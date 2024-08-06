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

class Save extends PromptAbstract
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = (int)$this->getRequest()->getParam(PromptInterface::ID);

        $model = $this->initModel();

        $data = $this->getRequest()->getParams();

        $data = $this->filter($data);

        if ($data) {
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage((string)__('This prompt no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }
            $code = (string)$data[PromptInterface::CODE];
            if ($id === 0) {
                $code = str_replace("mst_", "custom_", $code);
            }
            
            $openAiModel = isset($data[PromptInterface::AI_MODEL]) && $data[PromptInterface::AI_MODEL]
                ? $data[PromptInterface::AI_MODEL]
                : PromptInterface::CONFIG_MODEL;

            $model->setTitle((string)$data[PromptInterface::TITLE])
                ->setPlaceholder((string)$data[PromptInterface::PLACEHOLDER])
                ->setCode($code)
                ->setPrompt((string)$data[PromptInterface::PROMPT])
                ->setFieldSelector((string)$data[PromptInterface::FIELD_SELECTOR])
                ->setIsActive((bool)$data[PromptInterface::IS_ACTIVE])
                ->setIsModal((bool)$data[PromptInterface::IS_MODAL])
                ->setIsConvert2Html((bool)$data[PromptInterface::IS_CONVERT2HTML])
                ->setFrequencyPenalty((float)$data[PromptInterface::FREQUENCY_PENALTY])
                ->setPosition((int)$data[PromptInterface::POSITION])
                ->setScopes((string)$data[PromptInterface::SCOPES])
                ->setStopSequences($data[PromptInterface::STOP_SEQUENCES])
                ->setOpenAIModel($openAiModel);

            try {
                $this->promptRepository->save($model);

                $this->messageManager->addSuccessMessage((string)__('You have saved the prompt.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [PromptInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [PromptInterface::ID => $model->getId()]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage((string)__('No data to save.'));

            return $resultRedirect;
        }
    }

    private function filter(array $data): array
    {
        if (!$data[PromptInterface::POSITION]) {
            $data[PromptInterface::POSITION] = 1;
        }

        if (!isset($data[PromptInterface::SCOPES])) {
            $data[PromptInterface::SCOPES] = '';
        }

        if (is_array($data[PromptInterface::SCOPES])) {
            $data[PromptInterface::SCOPES] = implode(",", array_filter($data[PromptInterface::SCOPES]));
        }

        return $data;
    }
}
