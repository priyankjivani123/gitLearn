<?php
namespace Priyank\BundleField\Observer;
class OrderSender implements \Magento\Framework\Event\ObserverInterface {

    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $transport = $observer->getTransport();
        $order = $transport->getOrder();
        $transport->setData('vichel',$order->getVichel());
    }
}