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

use Mirasvit\Assistant\Api\Data\RuleInterface;
use Mirasvit\Assistant\Controller\Adminhtml\RuleAbstract;

class Delete extends RuleAbstract
{
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam(RuleInterface::ID);

        if ($id) {
            try {
                $model = $this->ruleRepository->get($id);
                $this->ruleRepository->delete($model);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->messageManager->addSuccessMessage(
                (string)__('Rule have been removed')
            );
        } else {
            $this->messageManager->addErrorMessage((string)__('Please select rule'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
