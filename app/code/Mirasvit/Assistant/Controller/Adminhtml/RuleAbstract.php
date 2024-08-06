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

namespace Mirasvit\Assistant\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Mirasvit\Assistant\Api\Data\RuleInterface;
use Mirasvit\Assistant\Repository\RuleRepository;

abstract class RuleAbstract extends Action
{
    protected $ruleRepository;

    protected $resultForwardFactory;

    private   $context;

    private   $session;
    private   $registry;

    public function __construct(
        RuleRepository $ruleRepository,
        ForwardFactory   $resultForwardFactory,
        \Magento\Framework\Registry $registry,
        Context          $context
    ) {
        $this->ruleRepository     = $ruleRepository;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->registry = $registry;

        $this->context = $context;
        $this->session = $context->getSession();

        parent::__construct($context);
    }

    protected function initPage(ResultInterface $resultPage): ResultInterface
    {
        $resultPage->setActiveMenu('Mirasvit_Assistant::assistant');

        $resultPage->getConfig()->getTitle()->prepend((string)__('AI Assistant'));
        $resultPage->getConfig()->getTitle()->prepend((string)__('Automation Rules'));

        return $resultPage;
    }

    protected function initModel(): ?RuleInterface
    {
        $model = $this->ruleRepository->create();

        if ($this->getRequest()->getParam(RuleInterface::ID)) {
            $model = $this->ruleRepository->get((int)$this->getRequest()->getParam(RuleInterface::ID));
        }
        $this->registry->register('current_rule', $model);

        return $model;
    }

    protected function _isAllowed(): bool
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Assistant::assistant');
    }
}
