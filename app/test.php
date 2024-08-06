<?php
/**
 * Copyright © CustomerRegisterForm All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Vdcstore\CustomerRegisterForm\Plugin\Magento\Customer\Model;

class Registration
{

    protected $_storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_storeManager = $storeManager;
    }

    public function afterIsAllowed(
        \Magento\Customer\Model\Registration $subject,
        $result
    )
    {
        try {
            $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();

            if ($currentWebsiteId == 24) {
                return false;
            } else {
                return $result;

            }
        } catch (Exception $e) {
            return $result;
        }

    }
}

