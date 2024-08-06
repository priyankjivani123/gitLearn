<?php

namespace Vdcstore\TickTerms\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;

class DefaultItem
{
    protected $_productloader;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $_productloader
    )
    {
        $this->_productloader = $_productloader;
    }

    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {

        $data = $proceed($item);

        /** @var Product $product */
        $product = $this->_productloader->create()->load($item->getProduct()->getId());

        $atts = [
            "terms_tick" => $product->getTermsTick(),
             "terms_tick_notice" => "This is a professional use only product and you certify you are qualified to use this product",
        ];

        return array_merge($data, $atts);
    }
}