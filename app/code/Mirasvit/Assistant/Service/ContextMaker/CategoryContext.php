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

namespace Mirasvit\Assistant\Service\ContextMaker;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;

class CategoryContext extends AbstractContext
{
    private $registry;

    public function __construct(
        Registry               $registry
    ) {
        $this->registry      = $registry;
    }

    public function context(): ?array
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->registry->registry('current_category');
        if (!$category || !($category instanceof CategoryInterface)) {
            return null;
        }
        return $this->contextByEntity($category);
    }

    public function contextByEntity(CategoryInterface $category): ?array
    {
        $data = [];

        $attributes = $category->getAttributes(true);
        $skip = [
            "is_active",
            "url_key",
            "path",
            "include_in_menu",
            "position",
            "level",
            "children_count",
            "display_mode",
            "landing_page",
            "is_anchor",
            "custom_use_parent_settings",
            "custom_apply_to_products",
            "custom_design",
            "page_layout",
            "layout_update",
            "custom_layout_update",
            "custom_layout_update_file"
        ];

        foreach ($attributes as $attribute) {
            if (in_array($attribute->getAttributeCode(), $skip)) {
                continue;
            }

            $value = $attribute->setStoreId($category->getStoreId())->getFrontend()->getValue($category);
            if ($value instanceof Phrase) {
                $value = (string)$value;
            }

            if (is_string($value) && strlen(trim($value)) && $attribute->getStoreLabel()) {
                if ($value == "No") { //fix me. Multi lang
                    continue;
                }
                $value = $this->clear($value);
                if (trim($value) == "") {
                    continue;
                }
                $data[] = [
                    'id'    => 'category.' . $attribute->getAttributeCode(),
                    'code'  => $attribute->getAttributeCode(),
                    'label' => $attribute->getStoreLabel(),
                    'value' => $value,
                ];
            }
        }
        $attributes = '';
        foreach ($data as $attr) {
            $attributes .= $attr['label'] . ': "' . $this->clear($attr['value']) . '"' . PHP_EOL;
        }
        $data[] = [
            'id'    => 'category.attributes',
            'label' => 'Attributes',
            'value' => $attributes,
        ];
        return $data;
    }
}
