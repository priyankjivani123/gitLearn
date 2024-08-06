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


namespace Mirasvit\Assistant\Model\Rule\ConditionRule\Condition;

use Magento\Rule\Model\Condition\Context;


class CombineCategory extends \Magento\Rule\Model\Condition\Combine
{
    private $categoryCondition;

    public function __construct(
        Category $categoryCondition,
        Context  $context,
        array    $data = []
    ) {
        $this->categoryCondition = $categoryCondition;
        parent::__construct($context, $data);
        $this->setType(\Magento\CatalogRule\Model\Rule\Condition\Combine::class);
    }

    public function getNewChildSelectOptions(): array
    {
        $conditions = parent::getNewChildSelectOptions();

        $categoryAttributes        = [];
        $categoryAttributesOptions = $this->categoryCondition->loadAttributeOptions()->getData('attribute_option');
        foreach ($categoryAttributesOptions as $code => $label) {
            $categoryAttributes[] = [
                'value' => Category::class . '|' . $code,
                'label' => $label,
            ];
        }

        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => (string)__('Category'),
                    'value' => $categoryAttributes,
                ],
            ]
        );

        return $conditions;
    }
}
