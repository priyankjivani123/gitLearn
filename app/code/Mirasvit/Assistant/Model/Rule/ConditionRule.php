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


declare(strict_types=1);
namespace Mirasvit\Assistant\Model\Rule;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatusSource;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Assistant\Api\Data\LabelInterface;
use Mirasvit\Assistant\Api\Data\RuleInterface;
use Mirasvit\Assistant\Model\Rule\ConditionRule\Action\CollectionFactory as ConditionRuleCollectionFactory;
use Mirasvit\Assistant\Model\Rule\ConditionRule\Condition\CombineFactory;
use Mirasvit\Assistant\Model\Rule\ConditionRule\Condition\CombineCategoryFactory;
use Mirasvit\Assistant\Repository\RuleRepository;
use phpseclib3\Math\PrimeField\Integer;

/**
 * @SuppressWarnings(PHPMD)
 */
class ConditionRule extends AbstractModel
{
    const CACHE_TAG      = 'assistant_rule';
    const RELATION_TABLE = 'mst_assistant_rule_product';

    /**
     * @var array
     */
    protected $_productIds;
    /**
     * @var array
     */
    protected $_categoryIds;
    /**
     * @var string
     */
    protected $_cacheTag = 'assistant_rule';
    /**
     * @var string
     */
    protected $_eventPrefix = 'assistant_rule';

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    protected $ruleRepository;

    protected $conditionRuleCollectionFactory;

    protected $conditionRuleCombineFactory;

    protected $productFactory;

    protected $productCollectionFactory;

    protected $productStatusSource;

    protected $productVisibility;

    protected $resourceIterator;

    protected $context;

    protected $registry;

    protected $resource;

    protected $resourceCollection;

    protected $storeManager;

    protected $conditionRuleCombineCategoryFactory;

    protected $categoryCollectionFactory;

    public function __construct(
        RuleRepository $ruleRepository,
        CombineFactory $conditionRuleCombineFactory,
        CombineCategoryFactory $conditionRuleCombineCategoryFactory,
        ConditionRuleCollectionFactory $conditionRuleCollectionFactory,
        ProductFactory $productFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductStatusSource $productStatusSource,
        ProductVisibility $productVisibility,
        Iterator $resourceIterator,
        CategoryCollectionFactory $categoryCollectionFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ruleRepository                      = $ruleRepository;
        $this->conditionRuleCombineFactory         = $conditionRuleCombineFactory;
        $this->conditionRuleCombineCategoryFactory = $conditionRuleCombineCategoryFactory;
        $this->conditionRuleCollectionFactory      = $conditionRuleCollectionFactory;
        $this->productFactory                      = $productFactory;
        $this->productCollectionFactory            = $productCollectionFactory;
        $this->productStatusSource                 = $productStatusSource;
        $this->productVisibility                   = $productVisibility;
        $this->resourceIterator                    = $resourceIterator;
        $this->categoryCollectionFactory           = $categoryCollectionFactory;
        $this->context                             = $context;
        $this->registry                            = $registry;
        $this->resource                            = $resource;
        $this->resourceCollection                  = $resourceCollection;
        $this->storeManager                        = $storeManager;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    public function getRule(): RuleInterface
    {
        $rule = $this->ruleRepository->create();
        if ($id = $this->getRuleId()) {
            $rule = $this->ruleRepository->get((int)$id);
        }

        return $rule;
    }

    public function getConditionsInstance(): \Magento\Rule\Model\Condition\Combine
    {
        if ($this->getRule()->getEntity() == RuleInterface::ENTITY_CATEGORY) {
            return $this->conditionRuleCombineCategoryFactory->create();
        } else {
            return $this->conditionRuleCombineFactory->create();
        }
    }

    public function getActionsInstance(): \Mirasvit\Assistant\Model\Rule\ConditionRule\Action\Collection
    {
        return $this->conditionRuleCollectionFactory->create();
    }

    public function getMatchingProductIds(array $productIds = null): array
    {
        $this->_productIds = [];

        foreach ($this->getRuleStoreIds() as $storeId) {
            $this->setCollectedAttributes([]);

            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addStoreFilter($storeId)
                ->addAttributeToFilter('status', ['in' => $this->productStatusSource->getVisibleStatusIds()])
                ->setVisibility($this->productVisibility->getVisibleInSiteIds());

            if ($productIds && count($productIds)) {
                $productCollection->addFieldToFilter('entity_id', ['in' => $productIds]);
            }

            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => $this->productFactory->create(),
                    'storeId'    => $storeId,
                ]
            );
        }

        return $this->_productIds;
    }

    public function callbackValidateProduct(array $args): void
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $product->setStoreId($args['storeId']);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = ['id' => $product->getId(), 'store_id' => $args['storeId']];
        }
    }

    public function getMatchingCategoryIds(array $categoryIds = null): array
    {
        $this->_categoryIds = [];

        foreach ($this->getRuleStoreIds() as $storeId) {
            $categoryCollection = $this->categoryCollectionFactory->create();
            $categoryCollection->setStoreId($storeId);

            if ($categoryIds && count($categoryIds)) {
                $categoryCollection->addFieldToFilter('entity_id', ['in' => $categoryIds]);
            }

            $categoryCollection->addAttributeToSelect('entity_id');

            foreach ($categoryCollection as $category) {
                if ($this->getConditions()->validate($category)) {
                    $this->_categoryIds[] = $category->getId();
                }
            }
        }

        $this->_categoryIds = array_unique($this->_categoryIds);

        return $this->_categoryIds;
    }

    private function getRuleStoreIds(): array
    {
        $stores = $this->getRule()->getStoreIds();

        if (!count($stores) || (count($stores) == 1 && $stores[0] == 0)) {
            $stores = [];

            foreach ($this->storeManager->getStores() as $store) {
                $stores[] = $store->getId();
            }
        }

        return $stores;
    }

    public function getConditions(): \Magento\Rule\Model\Condition\Combine
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();

            if (!empty($conditions)) {
                $decode = json_decode($conditions);

                if ($decode) { //M2.2 compatibility
                    $conditions = $this->serializer->unserialize($conditions);
                } else {
                    $conditions = unserialize($conditions);
                }

                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }

            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }

    public function getProductIds(array $productIds = null, int $limit = 0, int $pageNum = 0): array
    {
        if (!$this->resource) {
            $this->resource = $this->getRule()->getResource();
        }

        $read = $this->resource->getConnection();
        $select = $read->select()->from($this->resource->getTable(self::RELATION_TABLE), 'product_id')
            ->where('rule_id=?', $this->getRule()->getId());

        if ($productIds && count($productIds)) {
            $select->where('product_id IN(?)', implode(',', $productIds));
        }

        if ($limit && $pageNum) {
            $select->limitPage($pageNum, $limit);
        }

        return $read->fetchCol($select);
    }

    public function isProductId(int $productId, int $storeId = null): bool
    {
        if (!$this->resource) {
            $this->resource = $this->getRule()->getResource();
        }

        if (!$storeId) {
            $storeId = (int)$this->storeManager->getStore()->getId();
        }

        $read   = $this->resource->getConnection();
        $select = $read->select()->from($this->resource->getTable(self::RELATION_TABLE), 'product_id')
            ->where('rule_id=?', $this->getRule()->getId())
            ->where('product_id=?', $productId)
            ->where('store_id=?', $storeId);

        return (bool)count($read->fetchCol($select));
    }

    public function updateRuleProductData(array $productIds = null, bool $unset = false): self
    {
        if (!$this->resource) {
            $this->resource = $this->getRule()->getResource();
        }

        $ruleId = $this->getRule()->getId();
        $write  = $this->resource->getConnection();

        $write->beginTransaction();

        $deleteCondition = $write->quoteInto('rule_id = ?', $ruleId);

        if ($productIds && count($productIds)) {
            $deleteCondition .= ' ' . $write->quoteInto('AND product_id IN(?)', implode(',', $productIds));
        }

        $write->delete(
            $this->resource->getTable(self::RELATION_TABLE),
            $deleteCondition
        );

        if ($unset) {
            $write->commit();
            return $this;
        }

        $productIds = $this->getMatchingProductIds($productIds);
        $rows       = [];

        $queryStart = 'INSERT INTO '
            . $this->resource->getTable(self::RELATION_TABLE)
            . ' (rule_id, product_id, store_id) VALUES ';

        $queryEnd = ' ON DUPLICATE KEY UPDATE product_id=VALUES(product_id)';

        try {
            foreach ($productIds as $product) {
                $rows[] = "('".implode("','", [$ruleId, $product['id'], $product['store_id']])."')";

                if (sizeof($rows) == 1000) {
                    $sql = $queryStart.implode(',', $rows).$queryEnd;
                    $write->query($sql);
                    $rows = [];
                }
            }

            if (!empty($rows)) {
                $sql = $queryStart.implode(',', $rows).$queryEnd;
                $write->query($sql);
            }

            $write->commit();
        } catch (\Exception $e) {
            $write->rollback();
            throw $e;
        }

        return $this;
    }
}
