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


namespace Mirasvit\Assistant\Model\Rule\ConditionRule\Condition\Product;


use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;

class PriceDiff extends AbstractProductCondition
{
    public function getCode(): string
    {
        return 'price_diff';
    }

    public function getLabel(): string
    {
        return (string)__('Price - Final Price');
    }

    public function getValueOptions(): ?array
    {
        return null;
    }

    public function getInputType(): string
    {
        return self::TYPE_STRING;
    }

    public function getValueElementType(): string
    {
        return self::TYPE_TEXT;
    }

    public function validate(AbstractModel $object, AbstractCondition $validator): bool
    {
        $diff = $this->productDataHelper->setProduct($object)->getDiscountAmount();

        return $diff ? $validator->validateAttribute(abs($diff)) : false;
    }

    public function getExtraAttributesToSelect(): array
    {
        return [
            'price_view',
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'type_id'
        ];
    }
}
