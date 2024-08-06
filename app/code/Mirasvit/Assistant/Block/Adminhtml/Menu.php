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


namespace Mirasvit\Assistant\Block\Adminhtml;


use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObject;
use Mirasvit\Core\Block\Adminhtml\AbstractMenu;


class Menu extends AbstractMenu
{
    public function __construct(Context $context)
    {
        $this->visibleAt(['assistant']);

        parent::__construct($context);
    }

    protected function buildMenu()
    {
        $this->addItem([
            'resource' => 'Mirasvit_Assistant::assistant_prompt',
            'title'    => __('Prompts'),
            'url'      => $this->urlBuilder->getUrl('assistant/prompt')
        ])->addItem([
            'resource' => 'Mirasvit_Assistant::assistant_rule',
            'title'    => __('Automation Rules'),
            'url'      => $this->urlBuilder->getUrl('assistant/rule')
        ]);
    }
}
