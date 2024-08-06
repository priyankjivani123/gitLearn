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

namespace Mirasvit\Assistant\Controller\Adminhtml\Prompt;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Assistant\Api\Data\CriterionInterface;
use Mirasvit\Assistant\Api\Data\PromptInterface;
use Mirasvit\Assistant\Controller\Adminhtml\PromptAbstract;

class Edit extends PromptAbstract
{
    public function execute()
    {
        $model = $this->initModel();
        $id    = (int)$this->getRequest()->getParam(PromptInterface::ID);

        if ($id && !$model) {
            $this->messageManager->addErrorMessage((string)__('This prompt no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(
                $model->getId()
                    ? (string)__('Prompt "%1"', $model->getTitle())
                    : (string)__('New Prompt')
            );

        return $resultPage;
    }
}
