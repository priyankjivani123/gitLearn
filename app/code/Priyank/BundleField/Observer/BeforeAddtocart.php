<?php

namespace Priyank\BundleField\Observer;

use Magento\Framework\Event\ObserverInterface;

class BeforeAddtocart implements ObserverInterface

{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */

    protected $_messageManager;
    protected $_productRepository;


    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository

    )
    {
        $this->_messageManager = $messageManager;
        $this->_productRepository = $productRepository;

    }

    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vichel=$observer->getRequest()->getParam('vichel');
        $productId=$observer->getRequest()->getParam('product');
        $product = $this->_productRepository->getById($productId);

        if ($product->getTypeId() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE || $product->getTypeId() === 'configurable' && $product->getAttributeCode()) {
         if (!$vichel) {
                $this->_messageManager->addError(__('Please Enter Required Fiels'));
                $observer->getRequest()->setParam('product', false);
                return $this;
            }
        }
        return $this;

    }

}