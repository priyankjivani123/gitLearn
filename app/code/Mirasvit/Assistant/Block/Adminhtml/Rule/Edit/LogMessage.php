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


namespace Mirasvit\Assistant\Block\Adminhtml\Rule\Edit;


use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Mirasvit\Assistant\Model\ConfigProvider;

class LogMessage extends Template
{
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Template\Context $context
    ) {
        $this->configProvider = $configProvider;

        parent::__construct($context);
    }

    protected function _toHtml()
    {
        if ($this->configProvider->isLoggingEnabled()) {
            return '';
        }

        return '<div class="message message-notice notice"><div>Logging is disabled in the module\'s configurations</div></div>';
    }
}
