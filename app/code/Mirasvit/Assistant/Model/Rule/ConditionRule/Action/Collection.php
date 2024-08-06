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

namespace Mirasvit\Assistant\Model\Rule\ConditionRule\Action;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\LayoutInterface;
use Magento\Rule\Model\ActionFactory;

class Collection extends \Magento\Rule\Model\Action\Collection
{
    public function __construct(
        Repository $assetRepo,
        LayoutInterface $layout,
        ActionFactory $actionFactory,
        array $data = []
    ) {
        parent::__construct($assetRepo, $layout, $actionFactory, $data);
        $this->setType('Assistant/label_rule_action_collection');
        $this->setType('\\Mirasvit\\Assistant\\Model\\Rule\\ConditionRule\\Action\\Collection');
    }

    public function getNewChildSelectOptions(): array
    {
        $actions = parent::getNewChildSelectOptions();
        $actions = array_merge_recursive($actions, [
            [
                'value' => '\\Mirasvit\\Assistant\\Model\\Rule\\ConditionRule\\Action\\Product',
                'label' => (string)__('Update the Product'), ],
        ]);

        return $actions;
    }
}
