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

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;

class ProductContext extends AbstractContext
{
    private $registry;

    private $priceCurrency;

    public function __construct(
        Registry               $registry,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->registry      = $registry;
        $this->priceCurrency = $priceCurrency;
    }

    public function context(): ?array
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('current_product');
        if (!$product) {
            return null;
        }
        return $this->contextByEntity($product);
    }

    public function contextByEntity(AbstractModel $product): ?array
    {
        $data = [];

        $attributes = $product->getAttributes();

        if (!($product instanceof ProductInterface) || is_null($attributes)) {
            return $data;
        }

        $skip = [
            'status',
            'sku',
            'price',
            'visibility',
            'new',
            'sale',
            'shipment_type',
            'image',
            'small_image',
            'thumbnail',
            'url_key',
            'msrp_display_actual_price_type',
            'price_view',
            'page_layout',
            'custom_design',
            'custom_layout',
            'gift_message_available',
            'image_label',
            'small_image_label',
            'thumbnail_label',
            'tax_class_id',
            'options_container',
            'quantity_and_stock_status',
            'swatch_image',
            'special_price',
            'special_to_date',
            'special_from_date',
        ];
        
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            try {
                $value = $attribute->setStoreId($product->getStoreId())->getFrontend()->getValue($product);
            } catch (\Exception $e) {
                // some attributes can throw exceptions so we ignore them
                // example: attribute added by 3rd-party module, module removed but the attribute still present
                continue;
            }

            if ($value instanceof Phrase) {
                $value = (string)$value;
            } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                $value = $this->priceCurrency->convertAndFormat((float)$value, false);
            }

            if (is_string($value) && strlen(trim($value)) && $attribute->getStoreLabel()) {
                if (in_array($attribute->getAttributeCode(), $skip)) {
                    continue;
                }
                if ($value == "No") { //fix me. Multi lang
                    continue;
                }
                $value = $this->clear($value);
                if (trim($value) == "") {
                    continue;
                }
                $data[] = [
                    'id'    => 'product.' . $attribute->getAttributeCode(),
                    'code'    => $attribute->getAttributeCode(),
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
            'id'    => 'product.attributes',
            'label' => 'Attributes',
            'value' => $attributes,
        ];
        return $data;
    }
}
