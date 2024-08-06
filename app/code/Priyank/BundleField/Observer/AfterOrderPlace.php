<?php

namespace Priyank\BundleField\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * AfterOrderPlace order save the vehichle field
 */
class AfterOrderPlace implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory      $quoteFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
    ) {
        $this->quoteItemFactory = $quoteItemFactory;
        $this->quoteFactory = $quoteFactory;
    }

    /*
     * Fetch Quote Factory and add field to this function
     */
    /**
     * Save to the order table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $this->quoteFactory->create()->load($order->getQuoteId());
        if ($quote) {
            $quoteItem = $this->quoteItemFactory->create()->getCollection()
                ->addFieldToFilter('quote_id', $order->getQuoteId())
                ->addFieldToFilter('vichel', ['notnull' => true])
                ->getColumnValues('vichel');

            if (!empty($quoteItem)) {
                $vichelText = implode(',', $quoteItem);
                $orders = $observer->getData('order');
                if ($vichelText) {
                    $orders->setData('vichel', $vichelText);
                    $orders->save();
                }
            }
        }
    }
}
