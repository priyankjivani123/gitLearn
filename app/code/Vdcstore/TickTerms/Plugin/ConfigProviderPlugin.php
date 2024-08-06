<?php

namespace Vdcstore\TickTerms\Plugin;

class ConfigProviderPlugin extends \Magento\Framework\Model\AbstractModel
{

    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {

        $items = $result['totalsData']['items'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        for($i=0;$i<count($items);$i++){
            $quoteId = $items[$i]['item_id'];
            $quote = $objectManager->create('\Magento\Quote\Model\Quote\Item')->load($quoteId);
            $productId = $quote->getProductId();
            $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($productId);
            $productFlavours = $product->getTermsTick();       
            $items[$i]['terms_tick'] = $productFlavours;
            $items[$i]['terms_tick_notice'] = "This is a professional use only product and you certify you are qualified to use this product.";
        }
        $result['totalsData']['items'] = $items;
        return $result;
    }

}