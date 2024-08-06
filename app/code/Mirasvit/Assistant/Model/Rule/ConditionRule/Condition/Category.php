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

use Magento\Backend\Helper\Data;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Rule\Model\Condition\ConditionInterface;

class Category extends AbstractCondition
{
    const OPTION_CATEGORY_ID = 'category_id';

    protected $backendData;

    public function __construct(
        Data $backendData,
        Context $context,
        array $data = []
    ) {
        $this->backendData = $backendData;

        parent::__construct($context, $data);
    }

    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case self::OPTION_CATEGORY_ID:
                $url = 'catalog_rule/promo_widget/chooser/attribute/category_ids';
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                } else {
                    $url .= '/form/rule_conditions_fieldset'; //@dva fixed js error in sku grid. not sure about.
                }
                break;
            default:
                break;
        }

        return $url !== false ? $this->backendData->getUrl($url) : '';
    }

    public function getExplicitApply(): bool
    {
        switch ($this->getAttribute()) {
            case self::OPTION_CATEGORY_ID:
                return true;
        }

        return false;
    }

    public function getValueAfterElementHtml(): string
    {
        $html = '';

        switch ($this->getAttribute()) {
            case self::OPTION_CATEGORY_ID:
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = ''.
                '<a href="javascript:void(0)" class="rule-chooser-trigger">'.
                '<img src="'.$image.'" alt="" class="v-middle rule-chooser-trigger" title="'.(string)__('Open Chooser').'" />'.
                '</a>';
        }

        return $html;
    }

    public function loadAttributeOptions(): ConditionInterface
    {
        $attributes = [
            self::OPTION_CATEGORY_ID => (string)__('Category ID'),
        ];

        asort($attributes);
        $this->setData('attribute_option', $attributes);

        return $this;
    }

    public function validate(AbstractModel $object): bool
    {
        if (!($object instanceof \Magento\Catalog\Api\Data\CategoryInterface)) {
            return true;
        }

        /** @var \Magento\Catalog\Model\Category $object */
        switch ($this->getAttribute()) {
            case self::OPTION_CATEGORY_ID:
                return $this->validateAttribute($object->getId());
            default:
                return false;
        }
    }

    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = [
                '=='  => __('is'),
                '!='  => __('is not'),
                '()'  => __('is one of'),
                '!()' => __('is not one of'),
            ];
        }
        return $this->_defaultOperatorOptions;
    }

    public function getOperatorSelectOptions()
    {
        $opt = [];

        foreach ($this->getDefaultOperatorOptions() as $value => $label) {
            $opt[] = ['value' => $value, 'label' => $label];
        }

        return $opt;
    }
}
