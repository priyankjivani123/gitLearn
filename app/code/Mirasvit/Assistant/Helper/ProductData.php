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


namespace Mirasvit\Assistant\Helper;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResource;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager;
use Magento\Framework\Pricing\Helper\Data as PricingDataHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Assistant\Model\ConfigProvider;
use Mirasvit\Core\Service\CompatibilityService;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductData
{
    const DISCOUNT_PERCENT  = 'percent';
    const DISCOUNT_ABSOLUTE = 'absolute';

    /**
     * @var bool
     */
    private static $multiSourceInventorySupported = false;

    private $product;

    private $variableMapper = [
        'br'               => 'getNewLine',
        'price'            => 'getPrice',
        'final_price'      => 'getFinalPrice',
        'special_price'    => 'getSpecialPrice',
        'sku'              => 'getSku',
        'stock_qty'        => 'getStockQty',
        'discount_amount'  => 'getDiscountAmount',
        'discount_percent' => 'getDiscountPercent',
        'special_price_dl' => 'getDaysLeftForSpecialPrice',
        'new_days'         => 'getProductNewDays'
    ];

    private $pricingHelper;

    private $moduleManager;

    private $storeManager;

    private $stockResolver;

    private $sourceRepository;

    private $getStockSourceLinks;

    private $sourceDataBySku;

    private $productTypeConfigurableFactory;

    private $stockItemFactory;

    private $searchCriteriaBuilder;

    private $ruleFactoryModel;

    private $localDate;

    private $stockState;

    private $ruleResource;

    private $configProvider;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RuleResource $ruleResource,
        PricingDataHelper $pricingHelper,
        RuleFactory $ruleFactoryModel,
        Manager $moduleManager,
        StoreManagerInterface $storeManager,
        ItemFactory $stockItemFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConfigurableFactory $productTypeConfigurableFactory,
        TimezoneInterface $localDate,
        StockStateInterface $stockState,
        ConfigProvider $configProvider
    ) {
        $this->pricingHelper                  = $pricingHelper;
        $this->ruleFactoryModel               = $ruleFactoryModel;
        $this->moduleManager                  = $moduleManager;
        $this->storeManager                   = $storeManager;
        $this->stockItemFactory               = $stockItemFactory;
        $this->searchCriteriaBuilder          = $searchCriteriaBuilder;
        $this->productTypeConfigurableFactory = $productTypeConfigurableFactory;
        $this->localDate                      = $localDate;
        $this->stockState                     = $stockState;
        $this->ruleResource                   = $ruleResource;
        $this->configProvider                 = $configProvider;

        if (!self::$multiSourceInventorySupported) {
            self::$multiSourceInventorySupported =
                $this->moduleManager->isOutputEnabled('Magento_InventorySales') &&
                $this->moduleManager->isOutputEnabled('Magento_Inventory');
        }

        if (self::$multiSourceInventorySupported) {
            $this->stockResolver = CompatibilityService::getObjectManager()
                ->create(\Magento\InventorySales\Model\StockResolver::class);

            $this->sourceRepository = CompatibilityService::getObjectManager()
                ->create(\Magento\InventoryApi\Api\SourceRepositoryInterface::class);

            $this->getStockSourceLinks = CompatibilityService::getObjectManager()
                ->create(\Magento\InventoryApi\Api\GetStockSourceLinksInterface::class);

            $this->sourceDataBySku = CompatibilityService::getObjectManager()
                ->create(\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku::class);
        }
    }

    public function setProduct(ProductInterface $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function getVariableList(): array
    {
        $vars = [];

        foreach ($this->variableMapper as $code => $method) {
            $label = implode(' ',
                preg_split('/(?=[A-Z])/',
                    str_replace('get', '', $method)
                )
            );

            $vars[$code] = trim($label);
        }

        $vars['attr|attributeCode'] = 'Value of the product attribute with the code "attributeCode"';

        return $vars;
    }

    public function processVariables(string $text): string
    {
        foreach ($this->variableMapper as $code => $method) {
            $text = str_replace(
                '[' . $code . ']',
                $this->formatOutput($code, (string)$this->{$method}()),
                $text
            );
        }

        $text = preg_replace_callback(
            '/\[attr\|(.*)\]/s',
            [$this, 'replaceAttributeVariable'],
            $text
        );

        preg_match_all('/\[(\w*)\]/', $text, $matches);

        foreach ($matches[1] as $dataKey) {
            $value = $this->getProduct()->getData($dataKey);

            if (is_object($value)) {
                $value = '';
            }

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $text = str_replace(
                '[' . $dataKey . ']',
                $this->formatOutput($dataKey, (string)$value),
                $text
            );
        }

        return $text;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function replaceAttributeVariable(array $match): string
    {
        if (count($match) !== 2) {
            return '';
        }

        $attrCode = $match[1];
        $value    = $this->getAttributeValue($attrCode);

        if (!$value || is_object($value)) {
            return '';
        }

        return is_array($value) ? implode(', ', $value) : (string)$value;
    }

    public function getNewLine(): string
    {
        return '<br>';
    }

    public function getPrice(): float
    {
        $product = $this->getProduct();
        $price   = $product->getData('price');

        if ($price == 0 && $product->getTypeId() == 'configurable') {
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price');

            $price = $regularPrice->getMinRegularAmount()->getValue();
        }

        return (float)$price;
    }

    public function getFinalPrice(): float
    {
        $finalPriceInfo = $this->getProduct()->getPriceInfo()->getPrice('final_price');
        $finalPrice     = $finalPriceInfo->getMinimalPrice()->getValue();

        return $finalPrice ? (float)$finalPrice : (float)$this->getSpecialPrice();
    }

    public function getSpecialPrice(): ?float
    {
        $price = $this->getProduct()->getSpecialPrice();

        $specialPriceFromDate = $this->getProduct()->getSpecialFromDate();
        $specialPriceToDate   = $this->getProduct()->getSpecialToDate();
        $today                = time();
        $inDateInterval       = false;

        $validFrom = !$specialPriceFromDate || $today >= strtotime($specialPriceFromDate);
        $validTo   = !$specialPriceToDate || $today <= strtotime($specialPriceToDate);

        if ($validFrom && $validTo) {
            $inDateInterval = true;
        }

        return $price && $inDateInterval ? (float)$price : null;
    }

    public function getSku(): string
    {
        return $this->getProduct()->getSku();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getStockQty(): float
    {
        $qty = 0;

        $product           = $this->getProduct();
        $stockItem         = $this->stockItemFactory->create();
        $stockItemResource = $stockItem->getResource();

        $websiteId = $this->storeManager->getStore($product->getStoreId())->getWebsite()->getCode();

        $stockItemResource->loadByProductId(
            $stockItem,
            $product->getId(),
            $websiteId
        );

        if ($stockItem->getTypeId() == 'configurable') {
            if ($stockItem->getIsInStock()) {
                $requiredChildrenIds = $this->productTypeConfigurableFactory->create()
                    ->getChildrenIds($product->getId(), true);

                $childrenIds = [];

                foreach ($requiredChildrenIds as $groupedChildrenIds) {
                    $childrenIds = array_merge($childrenIds, $groupedChildrenIds);
                }

                $sumQty = 0;

                foreach ($childrenIds as $childId) {
                    $childQty = $this->stockState->getStockQty($childId, $websiteId);
                    $sumQty += $childQty;
                }

                $qty = $sumQty;
            } else {
                return 0;
            }
        } else {
            if (self::$multiSourceInventorySupported) {
                $websiteId   = $this->storeManager->getStore($product->getStoreId())->getWebsiteId();
                $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();

                $stockId = $this->stockResolver->execute(
                    SalesChannelInterface::TYPE_WEBSITE,
                    $websiteCode
                )->getStockId();

                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
                    ->create();

                $searchResult = $this->getStockSourceLinks->execute($searchCriteria);
                $stockData    = $this->sourceDataBySku->execute($product->getSku());

                foreach ($searchResult->getItems() as $result) {
                    $source = $this->sourceRepository->get($result->getSourceCode());

                    if ($source->isEnabled()) {
                        foreach ($stockData as $stockItem) {
                            if ($stockItem['source_code'] == $source->getSourceCode()) {
                                $qty = (float)$stockItem['quantity'];

                                break 2;
                            }
                        }
                    }
                }
            } else {
                $qty = $this->stockState->getStockQty($product->getId(), $websiteId);
            }
        }

        return $qty;
    }

    public function getDiscountAmount(): float
    {
        return $this->getDiscount(self::DISCOUNT_ABSOLUTE);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDiscountPercent(): float
    {
        $final  = $this->getSpecialPrice();
        $object = $this->getProduct();

        $ruleDiscount = 0;

        if (!$final || $final <= 0) {
            $rules = $this->ruleResource->getRulesFromProduct(
                $this->localDate->scopeDate($object->getStoreId())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                $this->storeManager->getWebsite(true)->getId(),
                $this->storeManager->getWebsite(true)->getDefaultGroup()->getId(),
                $object->getId()
            );

            if ($object->getTypeId() == 'configurable') { // get rules for children products
                $children = $object->getTypeInstance()->getUsedProducts($object);

                foreach ($children as $child) {
                    $rules = array_merge(
                        $rules,
                        $this->ruleResource->getRulesFromProduct(
                            $this->localDate->scopeDate($object->getStoreId())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                            $this->storeManager->getWebsite(true)->getId(),
                            $this->storeManager->getWebsite(true)->getDefaultGroup()->getId(),
                            $child->getId()
                        )
                    );
                }
            }

            if (count($rules)) {
                foreach ($rules as $ruleData) {
                    if ($ruleData['action_operator'] != RuleInterface::DISCOUNT_ACTION_BY_PERCENT) {
                        continue;
                    }

                    if (abs((float)$ruleData['action_amount']) > $ruleDiscount) {
                        $ruleDiscount = abs((float)$ruleData['action_amount']);
                    }
                }
            }
        }

        return $ruleDiscount
            ? round($ruleDiscount, 2)
            : $this->getDiscount(self::DISCOUNT_PERCENT);
    }

    private function getDiscount(string $discountType): float
    {
        $result = 0;

        $product   = $this->getProduct();
        $prodPrice = $this->getPrice();

        $final = $this->getFinalPrice();

        //Advanced Pricing -> Special Price in percent
        if ($final > 0 && $prodPrice > 0 && $product->getTypeId() == 'bundle') {
            $final = ($final * $prodPrice)/100;
        }

        if ($prodPrice && $final) {
            $result = $prodPrice - $final;

            if ($discountType == self::DISCOUNT_PERCENT) {
                $result = $result / $prodPrice * 100;
            }
        }

        $result = round($result, 2);

        return $result;
    }

    public function getIsSetAsNew(): bool
    {
        $now    = $this->getTimezoneHelper()->date()->getTimestamp();
        $from   = $this->getProduct()->getData('news_from_date')
            ? strtotime($this->getProduct()->getData('news_from_date'))
            : null;
        $to     = $this->getProduct()->getData('news_to_date')
            ? strtotime($this->getProduct()->getData('news_to_date'))
            : null;
        $result = false;

        if ($from || $to) {
            $result = true;

            if ($from && $from > $now) {
                $result = false;
            }
            if ($to && $to < $now) {
                $result = false;
            }
        }

        return $result;
    }

    public function getIsInStock(): bool
    {
        $result = true;
        /** @var Product $object */
        $object = $this->getProduct();

        $stockItem = $this->stockItemFactory->create()
            ->load($object->getId(), 'product_id');

        if ($object->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $children = $object->getTypeInstance()
                ->getSelectionsCollection(
                    $object->getTypeInstance()->getOptionsIds($object),
                    $object
                );

            $bundlestock = true;

            if (empty($children)) {
                $bundlestock = false;
            } else {
                foreach ($children as $child) {
                    $childStockItem = $this->stockItemFactory->create()
                        ->load($child->getId(), 'product_id');

                    if ($childStockItem->getIsInStock()) {
                        $bundlestock = false;
                    }
                }
            }

            if (!$bundlestock || !$object->isAvailable()) {
                $result = false;
            }
        } elseif (!$object->isAvailable() || !$stockItem->getIsInStock()) {
            $result = false;
        }

        return $result;
    }

    private function formatOutput(string $code, string $value): string
    {
        if (
            $code !== 'special_price_dl'
            && (strpos($code, 'price') !== false || $code === 'discount_amount')
        ) {
            return $this->pricingHelper->currency((float)$value);
        }

        if ($code === 'discount_percent') {
            $value = $this->configProvider->getDiscountOutputPrecision()
                ? round((float)$value, $this->configProvider->getDiscountOutputPrecision())
                : floor((float)$value);

            return $value . '%';
        }

        return (string)$value;
    }

    public function getStoreManager(): StoreManagerInterface
    {
        return $this->storeManager;
    }

    public function getTimezoneHelper(): TimezoneInterface
    {
        return $this->localDate;
    }

    public function getDaysLeftForSpecialPrice(): ?int
    {
        $specialPriceToDate = $this->getProduct()->getSpecialToDate();

        if (!$specialPriceToDate) {
            return null;
        }

        return $this->getDaysDiff(time(), strtotime($specialPriceToDate));
    }

    public function getProductNewDays(): ?int
    {
        $baseDate  = $this->getProduct()->getData('news_from_date') ?: $this->getProduct()->getCreatedAt();
        $newToDate = $this->getProduct()->getData('news_to_date');
        $current   = time();

        if ($newToDate && strtotime($newToDate) - $current < 1) {
            return null;
        }

        return $this->getDaysDiff(strtotime($baseDate), $current);
    }

    private function getDaysDiff(int $from, int $to): ?int
    {
        $daysDiff = ($to - $from) / 3600 / 24;

        return $daysDiff > 1 ? (int)floor($daysDiff) : null;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @return mixed
     */
    public function getAttributeValue(string $code)
    {
        $product = $this->getProduct();

        if ($val = $product->getData($code)) {
            return $val;
        }

        if ($product->getTypeId() == 'configurable') {
            $value = [];

            $productAttributeOptions = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);

            foreach ($productAttributeOptions as $productAttribute) {
                if ($productAttribute['attribute_code'] != $code) {
                    continue;
                }

                foreach ($productAttribute['values'] as $attribute) {
                    $value[] = $attribute['label'];
                }
            }

            if (count($value)) {
                return implode(', ', $value);
            }
        }

        if ($attributes = $product->getAttributes()) {
            foreach ($attributes as $attr) {
                if (!is_object($attr)) {
                    continue;
                }

                if ($attr->getAttributeCode() === $code) {
                    $value = $attr->getFrontend()->getValue($product);

                    if (empty($value)) {
                        $value = $product->getResource()
                            ->getAttributeRawValue($product->getId(), $code, $product->getStoreId());

                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }

                        if ($value && in_array($attr->getFrontendInput(), ['select', 'multiselect'])) {
                            if ($attr->getFrontendInput() == 'multiselect') {
                                $value = explode(',', $value);

                                foreach ($value as $idx => $v) {
                                    foreach ($attr->getFrontend()->getSelectOptions() as $option) {
                                        if ($option['value'] == $v) {
                                            $value[$idx] = $option['label'];
                                        }
                                    }
                                }
                            } else {
                                foreach ($attr->getFrontend()->getSelectOptions() as $option) {
                                    if ($option['value'] == $value) {
                                        $value = $option['label'];
                                    }
                                }
                            }
                        }
                    }

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    return $value ? (string)$value : null;
                }
            }
        }

        return null;
    }
}
