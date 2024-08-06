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

namespace Mirasvit\Assistant\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mirasvit\Assistant\Api\Data\LabelInterface;
use Mirasvit\Assistant\Controller\Adminhtml\Label;
use Mirasvit\Assistant\Model\ConfigProvider;
use Mirasvit\Assistant\Repository\RuleRepository;
use Mirasvit\Core\Helper\Cron;
use Mirasvit\Assistant\Model\Rule\ConditionRuleFactory;
use Magento\Backend\App\Action;

class NewConditionHtml extends Action
{
    protected $context;
    protected $conditionRuleFactory;

    public function __construct(
//        RuleRepository $labelRepository,
        ConditionRuleFactory $conditionRuleFactory,
//        LabelInterface $label,
//        ConfigProvider $config,
//        Cron $cronHelper,
//        DateTime $date,
//        Registry $registry,
//        Data $jsonEncoder,
        Context $context
    ) {
//        $this->labelRepository  = $labelRepository;
        $this->conditionRuleFactory = $conditionRuleFactory;
//        $this->label            = $label;
//        $this->config           = $config;
//        $this->cronHelper       = $cronHelper;
//        $this->date             = $date;
//        $this->registry         = $registry;
//        $this->jsonEncoder      = $jsonEncoder;
//        $this->context          = $context;
//        $this->backendSession   = $context->getSession();
//        $this->resultFactory    = $context->getResultFactory();
        $this->context = $context;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        if (!$type) {
            $this->getResponse()->setBody('');

            return;
        }

        $model = $this->context->getObjectManager()->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->conditionRuleFactory->create())
            ->setFormName('assistant_rule_form')
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);

        return;
    }
}
