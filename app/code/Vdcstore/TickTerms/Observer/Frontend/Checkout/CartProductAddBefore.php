<?php
declare(strict_types=1);

namespace Vdcstore\TickTerms\Observer\Frontend\Checkout;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;


class CartProductAddBefore implements ObserverInterface
{
    private $resultFactory;
    private $actionFlag;
    private $response;
    private $request;
    private $_messageManager;

    public function __construct(
        ResultFactory $resultFactory,
        ActionFlag $actionFlag,
        ResponseInterface $response,
        ManagerInterface $messageManager,
        Http $request
    ) {
        $this->resultFactory = $resultFactory;
        $this->actionFlag = $actionFlag;
        $this->response = $response;
        $this->_messageManager = $messageManager;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $sku = $product->getSku();
        $getTermsTick = $product->getTermsTick();

        if ($getTermsTick) {
            if (!$this->request->getParam('terms_tick')) {
                $message = 'This is a professional use only product and you certify you are qualified to use this product is required field';
                throw new LocalizedException(__($message));
            }
        }
    }
}
