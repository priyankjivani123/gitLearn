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

namespace Mirasvit\Assistant\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ScopeSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Product'),
                'value' => 'product'
            ],
            [
                'label' => __('Category'),
                'value' => 'category'
            ],
            [
                'label' => __('Blog Post'),
                'value' => 'post'
            ],
            [
                'label' => __('CMS page'),
                'value' => 'page'
            ],
            [
                'label' => __('Helpdesk Ticket'),
                'value' => 'ticket'
            ],
            [
                'label' => __('Email'),
                'value' => 'email'
            ],
            [
                'label' => __('KB Category'),
                'value' => 'kb_category'
            ],
            [
                'label' => __('KB Article'),
                'value' => 'kb_article'
            ]
        ];
    }
}
