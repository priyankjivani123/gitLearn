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

namespace Mirasvit\Assistant\Ui\Rule\Form\Control;

use Mirasvit\Assistant\Api\Data\RuleInterface;

class DuplicateButton extends ButtonAbstract
{
    public function getButtonData(): array
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label'          => __('Duplicate'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'duplicate'],
                    ],
                ],
                'on_click'   => sprintf("location.href = '%s';", $this->getDuplicateUrl()),
                'sort_order'     => 70,
            ];
        }

        return $data;
    }

    public function getDuplicateUrl(): string
    {
        return $this->getUrl('*/*/duplicate', [RuleInterface::ID => $this->getId()]);
    }
}
