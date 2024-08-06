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
 * @package   mirasvit/module-helpdesk
 * @version   1.2.21
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Controller\Satisfaction;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Helpdesk\Controller\Satisfaction;

class Rate extends Satisfaction
{
    /**
     *
     */
    public function execute()
    {
        if (!$this->isAgentAllowed() || !$this->isIpAllowed()) {
            return;
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        return $resultPage;
    }

    /**
     * @return bool
     */
    private function isAgentAllowed()
    {
        $agent = $this->getRequest()->getHeader('USER_AGENT');
        if (!$agent) {
            return false;
        }

        $agents = [
            'http://help.yahoo.com/help/us/ysearch/slurp',
            'Slurp',
            'Bot',
        ];

        foreach ($agents as $value) {
            if (stripos($agent, $value) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function isIpAllowed()
    {
        $remoteIp = $this->remoteAddress->getRemoteAddress();

        foreach($this->ipsToBlock() as $range) {
            if ($this->blockRangeIps($remoteIp, $range[0], $range[1])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @returns array
     */
    private function ipsToBlock() {
        return [
            ['51.140.0.0', '51.145.255.255'], //Microsoft Limited UK range ips // source https://ipinfo.io/AS8075/51.140.0.0/14
            ['40.74.0.0', '40.125.127.255'], // microsoft US range ips // source https://ipinfo.io/AS8075/40.74.0.0/15
            ['23.96.0.0', '23.103.255.255'], // microsoft US range ips // source https://ipinfo.io/AS8075/23.96.0.0/14
            ['20.33.0.0', '20.128.255.255'], // microsoft US range ips // source https://ipinfo.io/AS8075/20.64.0.0/10
            ['104.40.0.0', '104.47.255.255'], // microsoft US range ips // source https://ipinfo.io/AS8075/104.40.0.0/13
            ['20.192.0.0', '20.255.255.255'], // microsoft US range ips // source https://ipinfo.io/AS8075/20.192.0.0/10
            ['20.0.0.0', '20.31.255.255'], // microsoft US range ips // source https://ipinfo.io/AS8075/20.0.0.0/11
            ['51.10.0.0', '51.13.255.255'], // Microsoft Limited UK range ips // source https://ipinfo.io/AS8075/51.12.0.0/15
            ['34.248.0.0', '34.255.255.255'], // Amazon Data Services Ireland Limited range ips // source https://ipinfo.io/AS16509/34.248.0.0/13
        ];
    }

    /**
     * @returns bool
     */
    private function blockRangeIps($remoteAddr, $rangeStatrt, $rangeEnd) {
        $remoteAddr  = sprintf('%u', ip2long($remoteAddr));
        $rangeStatrt = sprintf('%u', ip2long($rangeStatrt));
        $rangeEnd    = sprintf('%u', ip2long($rangeEnd));

        if ($rangeStatrt <= $remoteAddr && $rangeEnd >= $remoteAddr) {
            return true;
        }

        return false;
    }


}
