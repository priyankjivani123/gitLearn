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
use Mirasvit\Assistant\Model\Rule;
use Mirasvit\CatalogLabel\Api\Data\LabelInterface;
use Mirasvit\Core\Service\SerializeService;

class Save extends RuleAbstract
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($origData = $this->getRequest()->getParams()) {
            $id = (int)$this->getRequest()->getParam(RuleInterface::ID);

            /** @var Rule $model */
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage((string)__('This rule no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }
            $data  = $this->prepareData($origData, $model);

            $model->addData($data);

            try {
                $this->ruleRepository->save($model);

                $this->messageManager->addSuccessMessage((string)__('You have saved the rule.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [RuleInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [RuleInterface::ID => $model->getId()]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage((string)__('No data to save.'));

            return $resultRedirect;
        }
    }

    private function prepareData(array $data, RuleInterface $model): array
    {
        if (isset($data['rule'])) {
            if (isset($data['rule']['conditions'])) {
                $rule = $model->getRule();

                $rule->loadPost(['conditions' => $data['rule']['conditions']]);

                $conditions = $rule->getConditions()->asArray();

                $data[RuleInterface::CONDITIONS_SERIALIZED] = SerializeService::encode($conditions);
            } else {
                $data[RuleInterface::CONDITIONS_SERIALIZED] = SerializeService::encode([]);
            }
            unset($data['rule']);
        }

        if (isset($data['store_ids'])) {
            $data['store_ids'] = implode(',', $data['store_ids']);
        } else {
            $data['store_ids'] = 0;
        }
        return $data;
    }
}
