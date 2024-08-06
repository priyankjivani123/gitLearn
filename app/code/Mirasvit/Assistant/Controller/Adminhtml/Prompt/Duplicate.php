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

use Mirasvit\Assistant\Api\Data\PromptInterface;
use Mirasvit\Assistant\Controller\Adminhtml\PromptAbstract;

class Duplicate extends PromptAbstract
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam(PromptInterface::ID);

        if ($id) {
            try {
                $model = $this->promptRepository->get($id);
                $model->setId(false);
                $model->setCode(str_replace("mst_", "custom_", $model->getCode()));
                $this->promptRepository->save($model);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->messageManager->addSuccessMessage(
                (string)__('Prompt have been duplicated')
            );
            return $resultRedirect->setPath('*/*/edit', [PromptInterface::ID => $model->getId()]);
        } else {
            $this->messageManager->addErrorMessage((string)__('Please select prompt'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
