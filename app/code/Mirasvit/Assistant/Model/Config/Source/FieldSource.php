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

namespace Mirasvit\Assistant\Model\Config\Source;

use Magento\Backend\Model\Url;
use Magento\Catalog\Model\ResourceModel\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Rule\Model\Condition\Context;
use Mirasvit\Assistant\Api\Data\RuleInterface;

class FieldSource implements OptionSourceInterface
{
    const PRODUCT_IMAGES_ALT = 'images_alt';

    protected $productFactory;

    protected $categoryFactory;

    protected $registry;

    public function __construct(
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->productFactory  = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->registry        = $registry;
    }

    public function loadAttributeOptions(): array
    {
        $model = $this->registry->registry('current_rule');

        if (!$model || !$model->getId()) {
            return [];
        }

        switch ($model->getEntity()) {
            case RuleInterface::ENTITY_PRODUCT:
                return $this->getProductAttributes();
            case RuleInterface::ENTITY_CATEGORY:
                return $this->getCategoryAttributes();
            default:
                return [];
        }
    }

    private function getProductAttributes(): array
    {
        $productAttributes = $this->productFactory->create()
            ->loadAllAttributes();

        if ($productAttributes) {
            $productAttributes = $productAttributes->getAttributesByCode();
        } else {
            $productAttributes = [];
        }

        $attributes = [];
        usort($productAttributes, function ($a, $b) {
            return ($a->getId() < $b->getId()) ? -1 : (($a->getId() > $b->getId()) ? 1 : 0);
        });

        foreach ($productAttributes as $attribute) {
            if (in_array($attribute->getBackendType(), ["static", "int", "decimal"])) {
                continue;
            }
            if (in_array($attribute->getAttributeCode(), ["custom_layout_update","url_path"])) {
                continue;
            }
            if (!in_array($attribute->getFrontendInput(), ["textarea", "text"])) {
                continue;
            }
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $attributes[self::PRODUCT_IMAGES_ALT] = (string)__('Product Images ALT Attribute');

        return $attributes;
    }

    private function getCategoryAttributes(): array
    {
        $categoryAttributes = $this->categoryFactory->create()->loadAllAttributes();


        if (!$categoryAttributes) {
            $categoryAttributes = [];
        } else {
            $categoryAttributes = $categoryAttributes->getAttributesByCode();
        }

        $attributes = [];
        usort($categoryAttributes, function ($a, $b) {
            return ($a->getId() < $b->getId()) ? -1 : (($a->getId() > $b->getId()) ? 1 : 0);
        });

        foreach ($categoryAttributes as $attribute) {
            if (in_array($attribute->getBackendType(), ["static", "int", "decimal"])) {
                continue;
            }
            if (in_array($attribute->getAttributeCode(), ["custom_layout_update","url_path"])) {
                continue;
            }
            if (!in_array($attribute->getFrontendInput(), ["textarea", "text"])) {
                continue;
            }
            if (!$attribute->getFrontendLabel()) {
                continue;
            }

            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        return $attributes;
    }

    public function toOptionArray(): array
    {
        $model = $this->registry->registry('current_rule');
        $entity = $model->getEntity();

        $attributes = $this->loadAttributeOptions();
        $options = [];
        foreach ($attributes as $k=>$label) {
            $options[] = [
                'label' => $label . ' / ' . $k,
                'value' => $entity.'.'.$k
            ];
        }

        return $options;
    }
}
