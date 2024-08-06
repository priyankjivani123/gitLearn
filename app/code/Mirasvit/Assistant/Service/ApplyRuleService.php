<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-assistant
 * @version   1.3.11
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Assistant\Service;


use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Assistant\Api\Data\LogInterface;
use Mirasvit\Assistant\Api\Data\RuleInterface;
use Mirasvit\Assistant\Model\Config\Source\FieldSource;
use Mirasvit\Assistant\Model\History;
use Mirasvit\Assistant\Model\Rule;
use Mirasvit\Assistant\Model\Prompt;
use Mirasvit\Assistant\Repository\PromptRepository;
use Mirasvit\Assistant\Repository\HistoryRepository;
use Mirasvit\Assistant\Repository\RuleRepository;
use Mirasvit\Assistant\Service\ContextMaker\CategoryContext;
use Mirasvit\Assistant\Service\ContextMaker\ProductContext;

class ApplyRuleService
{
    const MAX_ERROR_COUNT = 3;
    const MAX_ATTEMPTS    = 3;

    private $errorCount = 0;
    private $attempts   = 0;

    private $total           = [];
    private $applied         = [];
    private $skippedDueError = [];

    private $ruleRepository;
    private $promptRepository;
    private $historyRepository;
    private $productRepository;
    private $categoryCollectionFactory;
    private $categoryRepository;
    private $searchCriteriaBuilder;
    private $sortOrderBuilder;
    private $productContext;
    private $categoryContext;
    private $filterBuilder;
    private $completionsService;
    private $filterGroupBuilder;
    private $productResource;
    private $categoryResource;
    private $productFactory;
    private $storeManager;
    private $lockService;
    private $logger;

    public function __construct(
        RuleRepository $ruleRepository,
        PromptRepository $promptRepository,
        HistoryRepository $historyRepository,
        CompletionsService $completionsService,
        ProductRepositoryInterface $productRepository,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        ProductResource $productResource,
        CategoryResource $categoryResource,
        ProductFactory $productFactory,
        ProductContext $productContext,
        CategoryContext $categoryContext,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrderBuilder $sortOrderBuilder,
        StoreManagerInterface $storeManager,
        RuleLockService $lockService,
        LoggerService $logger
    ) {
        $this->ruleRepository            = $ruleRepository;
        $this->promptRepository          = $promptRepository;
        $this->historyRepository         = $historyRepository;
        $this->completionsService        = $completionsService;
        $this->productRepository         = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository        = $categoryRepository;
        $this->productResource           = $productResource;
        $this->categoryResource          = $categoryResource;
        $this->productFactory            = $productFactory;
        $this->productContext            = $productContext;
        $this->categoryContext           = $categoryContext;
        $this->searchCriteriaBuilder     = $searchCriteriaBuilder;
        $this->filterBuilder             = $filterBuilder;
        $this->filterGroupBuilder        = $filterGroupBuilder;
        $this->sortOrderBuilder          = $sortOrderBuilder;
        $this->storeManager              = $storeManager;
        $this->lockService               = $lockService;
        $this->logger                    = $logger;
    }

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function apply(int $ruleId, int $entityId = 0, bool $dryRun = false, bool $force = false): void
    {
        $this->logger->info(
            $ruleId,
            'Applying rule',
            [
                'entity_id' => $entityId ?: '',
                'test_run'  => $dryRun ? 'Yes' : 'No',
                'forced'    => $force ? 'Yes' : 'No'
            ]
        );

        $rule = $this->ruleRepository->get($ruleId);
        if (!$rule || !$rule->isActive()) {
            $this->logger->error($ruleId, "Cant find active rule");
            throw new \Exception("Cant find active rule");
        }

        $prompt = $this->promptRepository->get($rule->getPromptId());
        if (!$prompt || !$prompt->isActive()) {
            $this->logger->error($ruleId, "Cant find active prompt");
            throw new \Exception("Cant find active prompt");
        }

        $field       = $rule->getField();
        $parts       = explode(".", $field);
        $entity      = $parts[0];
        $variable    = $parts[1];
        $lockPointer = null;

        switch ($entity) {
            case RuleInterface::ENTITY_PRODUCT:
                if (!$dryRun) {
                    if ($force) {
                        $this->lockService->unlock($ruleId);
                    }

                    try {
                        $lockPointer = $this->lockService->lock($ruleId);
                    } catch (\RuntimeException $e) {
                        $this->logger->error($ruleId, $e->getMessage());
                        throw new \RuntimeException($e->getMessage());
                    }
                }

                $this->logger->info($ruleId, 'Using prompt #' . $prompt->getId(), $prompt->getData());
                $this->applyForProducts($rule, $prompt, $variable, $entityId, $dryRun, $lockPointer);
                $this->lockService->unlock($ruleId);
                break;
            case RuleInterface::ENTITY_CATEGORY:
                if (!$dryRun) {
                    if ($force) {
                        $this->lockService->unlock($ruleId);
                    }

                    try {
                        $lockPointer = $this->lockService->lock($ruleId);
                    } catch (\RuntimeException $e) {
                        $this->logger->error($ruleId, $e->getMessage());
                        throw new \RuntimeException($e->getMessage());
                    }
                }

                $this->logger->info($ruleId, 'Using prompt #' . $prompt->getId(), $prompt->getData());
                $this->applyForCategories($rule, $prompt, $variable, $entityId, $dryRun, $lockPointer);
                $this->lockService->unlock($ruleId);
                break;
            default:
                $this->logger->error($ruleId, "Can't find entity $entity");
                throw new \Exception("Can't find entity $entity");
        }

        $this->logger->info($ruleId, 'Rule is applied', $this->summarize());
    }

    public function resetInHistory(int $ruleId, int $entitId): void
    {
        /** @var Rule $rule */
        $rule = $this->ruleRepository->get($ruleId);

        if (!$rule) {
            throw new \Exception("Can't find rule with ID: " . $ruleId);
        }

        if (!$rule->isOnce()) {
            echo 'Resetting this rule is not necessary.';

            return;
        }

        $resource = $rule->getResource();
        $where    = 'rule_id = ' . $ruleId;

        if ($entitId) {
            $where .= ' AND entity_id = ' . $entitId;
        }

        $select = $resource->getConnection()->update(
            $resource->getTable(History::TABLE_NAME),
            ['is_removed' => true],
            $where
        );
    }

    public function reindexRuleProductRelation(?Schedule $schedule): void
    {
        $this->updateRuleProductRelation();
    }

    public function updateRuleProductRelation(array $productIds = null, bool $remove = false): void
    {
        if (!$productIds || !count($productIds)) {
            $resource   = $this->ruleRepository->create()->getResource();
            $connection = $resource->getConnection();

            $connection->truncateTable($resource->getTable(Rule\ConditionRule::RELATION_TABLE));
        }

        foreach ($this->ruleRepository->getCollection() as $rule) {
            if ($rule->getEntity() !== RuleInterface::ENTITY_PRODUCT) {
                continue;
            }

            $rule->getRule()->updateRuleProductData($productIds, $remove);
        }
    }

    protected function applyForProducts(Rule $rule, Prompt $prompt, string $variable, int $entityId, bool $dryRun, string $lockPointer = null)
    {
        $historyIds = [];

        if ($rule->isOnce()) {
            $resource = $rule->getResource();

            $select = $resource->getConnection()
                ->select()
                ->from($resource->getTable(History::TABLE_NAME), 'entity_id')
                ->where("rule_id = ?", $rule->getId())
                ->where("is_removed = ?", 0);

            $items = $resource->getConnection()->fetchCol($select);

            foreach ($items as $item) {
                $historyIds[$item] = 1;
            }

            $historyIds = array_keys($historyIds);
        }

        if ($entityId) {
            if (count($historyIds) && in_array($entityId, $historyIds)) {
                echo "Product #{$entityId}: skipped. Rule already applied to this product. To re-apply rule you need to reset it first\n";
                return;
            }

            $this->applyForProduct($rule, $prompt, $variable, $entityId, $dryRun, $lockPointer);
            return;
        }

        $pageSize = 5;
        $pageNum  = 1;

        while (true) {
            $productIds = $rule->getRule()->getProductIds(null, $pageSize, $pageNum);

            if (!count($productIds)) {
                break;
            }

            $pageNum++;

            $productIds = array_diff($productIds, $historyIds);

            if (!count($productIds)) {
                continue;
            }

            $idFilter = $this->filterBuilder->setField("entity_id")
                ->setConditionType("IN")
                ->setValue($productIds)
                ->create();
            $searchCriteria = $this->searchCriteriaBuilder
                ->setFilterGroups([$this->filterGroupBuilder->setFilters([$idFilter])->create()])
                ->create();
            $products = $this->productRepository->getList($searchCriteria)->getItems();

            foreach ($products as $product) {
                $this->applyForProduct($rule, $prompt, $variable, $product->getId(), $dryRun, $lockPointer);
            }
        }
    }

    protected function getAnswer(Prompt $prompt, array $context): string
    {
        try {
            $answer = $this->completionsService->answerByContext($prompt, $context);
            $this->attempts = 0;
            return $answer;
        } catch (\RuntimeException $re) { // throw exception on API key not set
            throw new \RuntimeException($re->getMessage());
        } catch (\Exception $e) {
            if ($this->attempts >= self::MAX_ATTEMPTS) {
                throw new \Exception($e->getMessage());
            }

            echo "OpenAI API ERROR: {$e->getMessage()} \n";
            echo "Waiting 5 sec\n";

            $this->attempts++;
            sleep(5);

            echo "Retrying...\n";

            return $this->getAnswer($prompt, $context);
        }
    }

    protected function applyForProduct(Rule $rule, Prompt $prompt, string $variable, int $productId, bool $dryRun, string $lockPointer = null)
    {
        $hasChanges        = false;
        $newValue          = false;
        $isApplyForDefault = false;

        $stores = $rule->getStoreIds();

        if (!count($stores) || (count($stores) == 1 && $stores[0] == 0)) {
            $isApplyForDefault = true;
            $stores = [];

            foreach ($this->storeManager->getStores(true) as $store) {
                $stores[] = $store->getId();
            }
        }

        $this->total[] = $productId;

        foreach ($stores as $storeId) {
            if ($lockPointer && $this->lockService->isLocked($rule->getId(), $lockPointer)) {
                $this->logger->warning(
                    $rule->getId(),
                    "Execution was terminated by another process",
                    $this->summarize()
                );
                throw new \Exception('Command execution was terminated by another process');
            }

            $storeViewLabel = "";
            if ($storeId > 0) {
                $storeViewLabel = ", storeview #$storeId";
            }

            if ($storeId == 0) {
                if (!$isApplyForDefault) {
                    echo "Product #{$productId}, admin: skipped. Rule is configured for specific store(s)\n";
                    continue;
                }
            } elseif (!$rule->getRule()->isProductId($productId, (int)$storeId)) { // when entity-id option present in the command
                echo "Product #{$productId}$storeViewLabel: skipped. Does not match rule conditions\n";
                continue;
            }

            $product = $this->productFactory->create();
            $product->setStoreId($storeId);
            $this->productResource->load($product, $productId);

            $value = $product->getData($variable);

            if ($variable == FieldSource::PRODUCT_IMAGES_ALT) {
                $values = [];

                foreach ($product->getMediaGalleryEntries() as $image) {
                    $values[] =  $image->getLabel();
                }

                if (!count($values)) {
                    echo "Product #{$product->getId()}$storeViewLabel: skipped. No images found\n";
                    continue;
                }

                $value = implode('|', $values);
            }
            if (!$rule->isOverwrite() && trim((string)$value) != "") {
                echo "Product #{$product->getId()}$storeViewLabel: skipped. {$variable} not empty and rule not configured to overwrite not empty fields\n";
                continue;
            }
            $context = $this->productContext->contextByEntity($product);

            if ($newValue === false) {
                try {
                    $this->attempts   = 0;
                    $newValue         = $this->getAnswer($prompt, $context);
                    $this->errorCount = 0;
                } catch (\RuntimeException $re) {
                    $this->lockService->unlock($rule->getId());

                    $this->logger->error(
                        $rule->getId(),
                        "Execution terminated due to critical error.",
                        array_merge(['error' => $re->getMessage()], $this->summarize())
                    );

                    throw new \RuntimeException($re->getMessage()); // throw exception on API key not set
                } catch (\Exception $e) {
                    if ($this->errorCount >= self::MAX_ERROR_COUNT) { // allow errors on 3 products
                        $this->lockService->unlock($rule->getId());

                        $this->logger->error(
                            $rule->getId(),
                            "Execution terminated because errors limit reached.",
                            array_merge(['last_error' => $e->getMessage()], $this->summarize())
                        );

                        throw new \Exception('Reached maximum errors limit. Last error: ' . $e->getMessage());
                    }

                    $this->errorCount++;

                    $this->skippedDueError[] = $product->getId();
                    $this->logger->warning(
                        $rule->getId(),
                        "Product #{$product->getId()} skipped due to errors",
                        ['error' => $e->getMessage()]
                    );

                    echo "\nProduct #{$product->getId()}$storeViewLabel: skipped due to errors\n\n";
                    continue;
                }
            }

            $product->setData($variable, $newValue);

            if (!$dryRun) {
                try { // prevent execution termination on exception when trying to save product attribute
                    if ($variable !== FieldSource::PRODUCT_IMAGES_ALT) {
                        $this->productResource->saveAttribute($product, $variable);
                    } else {
                        $this->updateProductMediaAlt($product, $variable);
                    }
                    $hasChanges = true;
                } catch (\Exception $e) {
                    $this->logger->warning(
                        $rule->getId(),
                        "Product #{$product->getId()}$storeViewLabel: skipped due to errors",
                        ['error' => $e->getMessage()]
                    );

                    echo "\nProduct #{$product->getId()}$storeViewLabel: skipped due to error: {$e->getMessage()}\n\n";
                    continue;
                }
            }

            echo "Product #{$product->getId()}$storeViewLabel: $variable => \"$newValue\" \n";
        }

        if (!$dryRun && $hasChanges) {
            $history = $this->historyRepository->create();
            $history->setRuleId($rule->getId());
            $history->setEntityId($product->getId());
            $history->setOldValue((string)$value);
            $history->setNewValue($newValue);
            $this->historyRepository->save($history);

            $this->applied[] = $product->getId();
        }
    }

    private function updateProductMediaAlt(ProductInterface $product, string $variable):void
    {
        if ($product->getStoreId() == 0) {
            $this->productResource->getConnection()->update(
                $this->productResource->getTable(ProductResource\Gallery::GALLERY_VALUE_TABLE),
                ['label' => $product->getData($variable)],
                'entity_id = ' . $product->getId() . ' AND store_id = ' . $product->getStoreId()
            );

            return;
        }

        $defaultValues = $this->productResource->getConnection()
            ->select()
            ->from($this->productResource->getTable(ProductResource\Gallery::GALLERY_VALUE_TABLE))
            ->where('entity_id = ' . $product->getId() . ' AND store_id = 0')
            ->query()
            ->fetchAll();

        $storeValueIds = $this->productResource->getConnection()
            ->select()
            ->from($this->productResource->getTable(ProductResource\Gallery::GALLERY_VALUE_TABLE), 'value_id')
            ->where('entity_id = ' . $product->getId() . ' AND store_id = ' . $product->getStoreId())
            ->query()
            ->fetchAll(\Zend_Db::FETCH_COLUMN);

        $insertData = [];

        $columns = [];

        foreach ($defaultValues as $row) {
            if (!count($columns)) {
                $columns = array_keys($row);
                unset($columns[array_search('record_id', $columns)]);
            }

            if (!in_array($row['value_id'], $storeValueIds)) {
                $row['store_id'] = $product->getStoreId();
                unset($row['record_id']);

                $insertData[] = array_values($row);
            }
        }

        if (count($insertData)) { // insert data for store if not present yet
            $this->productResource->getConnection()->insertArray(
                $this->productResource->getTable(ProductResource\Gallery::GALLERY_VALUE_TABLE),
                $columns,
                $insertData
            );
        }

        // update labels for all media for the store
        $this->productResource->getConnection()->update(
            $this->productResource->getTable(ProductResource\Gallery::GALLERY_VALUE_TABLE),
            ['label' => $product->getData($variable)],
            'entity_id = ' . $product->getId() . ' AND store_id = ' . $product->getStoreId()
        );
    }

    private function summarize(): array
    {
        return [
            'total'              => count(array_unique($this->total)),
            'applied'            => count(array_unique($this->applied)),
            'skipped_due_errors' => count(array_unique($this->skippedDueError)),
        ];
    }

    protected function applyForCategories(Rule $rule, Prompt $prompt, string $variable, int $entityId, bool $dryRun, string $lockPointer = null)
    {
        $historyIds = [];

        if ($rule->isOnce()) {
            $resource = $rule->getResource();

            $select = $resource->getConnection()
                ->select()
                ->from($resource->getTable(History::TABLE_NAME), 'entity_id')
                ->where("rule_id = ?", $rule->getId())
                ->where("is_removed = ?", 0);

            $items = $resource->getConnection()->fetchCol($select);

            foreach ($items as $item) {
                $historyIds[$item] = 1;
            }

            $historyIds = array_keys($historyIds);
        }

        if ($entityId) {
            if (count($historyIds) && in_array($entityId, $historyIds)) {
                echo "Category #{$entityId}: skipped. Rule already applied to this product. To re-apply rule you need to reset it first\n";
                return;
            }

            $this->applyForCategory($rule, $prompt, $variable, $entityId, $dryRun, $lockPointer);
            return;
        }

        foreach ($rule->getRule()->getMatchingCategoryIds() as $categoryId) {
            $this->applyForCategory($rule, $prompt, $variable, $categoryId, $dryRun, $lockPointer);
        }
    }

    protected function applyForCategory(Rule $rule, Prompt $prompt, string $variable, int $categoryId, bool $dryRun, string $lockPointer = null)
    {
        $hasChanges        = false;
        $newValue          = false;
        $isApplyForDefault = false;

        $stores = $rule->getStoreIds();

        if (!count($stores) || (count($stores) == 1 && $stores[0] == 0)) {
            $isApplyForDefault = true;
            $stores = [];

            foreach ($this->storeManager->getStores(true) as $store) {
                $stores[] = $store->getId();
            }
        }

        $this->total[] = $categoryId;

        foreach ($stores as $storeId) {
            if ($lockPointer && $this->lockService->isLocked($rule->getId(), $lockPointer)) {
                $this->logger->warning(
                    $rule->getId(),
                    "Execution was terminated by another process",
                    $this->summarize()
                );
                throw new \Exception('Command execution was terminated by another process');
            }

            $storeViewLabel = "";
            if ($storeId > 0) {
                $storeViewLabel = ", storeview #$storeId";
            }

            if ($storeId == 0) {
                if (!$isApplyForDefault) {
                    echo "Category #{$categoryId}, admin: skipped. Rule is configured for specific store(s)\n";
                    continue;
                }
            }

            $category = $this->categoryRepository->get($categoryId, $storeId);

            $value = $category->getData($variable);
            if (!$rule->isOverwrite() && trim((string)$value) != "") {
                echo "Category #{$category->getId()}$storeViewLabel: skipped. {$variable} not empty and rule not configured to overwrite not empty fields\n";
                continue;
            }
            $context = $this->categoryContext->contextByEntity($category);

            if ($newValue === false) {
                try {
                    $this->attempts   = 0;
                    $newValue         = $this->getAnswer($prompt, $context);
                    $this->errorCount = 0;
                } catch (\RuntimeException $re) {
                    $this->lockService->unlock($rule->getId());

                    $this->logger->error(
                        $rule->getId(),
                        "Execution terminated due to critical error.",
                        array_merge(['error' => $re->getMessage()], $this->summarize())
                    );

                    throw new \RuntimeException($re->getMessage()); // throw exception on API key not set
                } catch (\Exception $e) {
                    if ($this->errorCount >= self::MAX_ERROR_COUNT) { // allow errors on 3 products
                        $this->lockService->unlock($rule->getId());

                        $this->logger->error(
                            $rule->getId(),
                            "Execution terminated because errors limit reached.",
                            array_merge(['last_error' => $e->getMessage()], $this->summarize())
                        );

                        throw new \Exception('Reached maximum errors limit. Last error: ' . $e->getMessage());
                    }

                    $this->errorCount++;

                    $this->skippedDueError[] = $category->getId();
                    $this->logger->warning(
                        $rule->getId(),
                        "Category #{$category->getId()} skipped due to errors",
                        ['error' => $e->getMessage()]
                    );

                    echo "\nCategory #{$category->getId()}$storeViewLabel: skipped due to errors\n\n";
                    continue;
                }
            }

            $category->setData($variable, $newValue);

            if (!$dryRun) {
                try { // prevent execution termination on exception when trying to save category attribute
                    $this->categoryResource->saveAttribute($category, $variable);
                    $hasChanges = true;
                } catch (\Exception $e) {
                    $this->logger->warning(
                        $rule->getId(),
                        "Category #{$category->getId()}$storeViewLabel: skipped due to errors",
                        ['error' => $e->getMessage()]
                    );

                    echo "\nCategory #{$category->getId()}$storeViewLabel: skipped due to error: {$e->getMessage()}\n\n";
                    continue;
                }
            }

            echo "Category #{$category->getId()}$storeViewLabel: $variable => \"$newValue\" \n";
        }
        if (!$dryRun && $hasChanges) {
            $history = $this->historyRepository->create();
            $history->setRuleId($rule->getId());
            $history->setEntityId($category->getId());
            $history->setOldValue((string)$value);
            $history->setNewValue($newValue);
            $this->historyRepository->save($history);

            $this->applied[] = $category->getId();
        }
    }
}
