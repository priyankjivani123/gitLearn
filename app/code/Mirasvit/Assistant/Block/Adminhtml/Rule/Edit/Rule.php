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


use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Rule\Block\Conditions;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Model\Url;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;
use Mirasvit\Assistant\Api\Data\RuleInterface;


class Rule extends Form
{
    protected $widgetFormRendererFieldset;

    protected $conditions;

    protected $formFactory;

    protected $backendUrlManager;

    protected $registry;

    protected $context;

    public function __construct(
        Fieldset $widgetFormRendererFieldset,
        Conditions $conditions,
        FormFactory $formFactory,
        Url $backendUrlManager,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->widgetFormRendererFieldset = $widgetFormRendererFieldset;
        $this->conditions                 = $conditions;
        $this->formFactory                = $formFactory;
        $this->backendUrlManager          = $backendUrlManager;
        $this->registry                   = $registry;
        $this->context                    = $context;

        parent::__construct($context, $data);
    }

    public function getTabLabel(): string
    {
        return (string)__('Conditions');
    }

    public function getTabTitle(): string
    {
        return (string)__('Conditions');
    }

    public function canShowTab(): bool
    {
        return true;
    }

    public function isHidden(): bool
    {
        return false;
    }

    protected function _prepareForm(): Form
    {
        $formName = 'assistant_rule_form';
        /** @var RuleInterface $model */
        $model    = $this->registry->registry('current_rule');
        $form     = $this->formFactory->create();

        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->widgetFormRendererFieldset
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNameInLayout('Mirasvit_Assistant::rule_conditions')
            ->setNewChildUrl($this->backendUrlManager
                ->getUrl('*/rule/newConditionHtml/form/rule_conditions_fieldset'))
            ->setData('form_name', $formName);

        $legend = $model->getEntity() == RuleInterface::ENTITY_CATEGORY
            ? 'Conditions (leave blank for all categories)'
            : 'Conditions (leave blank for all products)';

        $fieldset = $form->addFieldset('conditions_fieldset', [
                'legend' => (string)__($legend), ]
        )->setRenderer($renderer);

        $rule = $model->getRule();

        $rule->getConditions()->setFormName($formName);

        $fieldset->addField('conditions', 'text', [
            'name'           => 'conditions',
            'label'          => (string)__('Conditions'),
            'title'          => (string)__('Conditions'),
            'required'       => true,
            'data-form-part' => $formName,
        ])->setRule($rule)->setRenderer($this->conditions)->setFormName($formName);

        $form->setValues($rule->getData());
        $this->setConditionFormName($rule->getConditions(), $formName);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, string $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
