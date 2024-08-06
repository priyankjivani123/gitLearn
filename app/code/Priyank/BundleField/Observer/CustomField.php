<?php

namespace Priyank\BundleField\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * CustomField save field to quote_item table
 */
class CustomField implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_request = $request;
    }

    /**
     * Add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getQuoteItem();
        $product = $observer->getEvent()->getProduct();
        $postData = $this->_request->getPost();

        if ($product->getTypeId() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE || $product->getTypeId() === 'configurable' && $product->getAttributeCode()) {
            if ($postData['vichel']) {
                $quoteItem->setData("vichel", $postData['vichel']);
            }
        }
    }
}
