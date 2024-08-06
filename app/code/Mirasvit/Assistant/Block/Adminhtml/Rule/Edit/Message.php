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
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Model\Url;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions;

class Message extends Template
{
    protected $registry;

    protected $context;

    public function __construct(
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->registry                   = $registry;
        $this->context                    = $context;

        parent::__construct($context, $data);
    }

    public function getRuleId()
    {
        return $this->registry->registry('current_rule')->getId();
    }
}
