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



namespace Mirasvit\Assistant\Controller\Adminhtml\Api;

use Magento\Backend\App\Action\Context;
use Mirasvit\Assistant\Controller\Adminhtml\AbstractApi;
use Mirasvit\Assistant\Repository\PromptRepository;
use Mirasvit\Assistant\Service\CompletionsService;
use Mirasvit\Core\Service\SerializeService;


class Text extends AbstractApi
{
    private $completionsService;

    private $promptRepository;

    public function __construct(
        CompletionsService $completionsService,
        PromptRepository   $promptRepository,
        Context            $context
    ) {
        $this->completionsService = $completionsService;
        $this->promptRepository   = $promptRepository;

        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $payload = SerializeService::decode(file_get_contents('php://input'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
        if (!$payload) {
            return $this->errorResponse("Payload is empty");
        }
        try {
            $prompt  = $this->promptRepository->get((int)$payload['id']);
            $aiModel = $payload['aiModel'];

            if ($aiModel) {
                $prompt->setOpenAIModel($aiModel);
            }

            $answer = $this->completionsService->answer($prompt, (string)$payload['prompt']);

            return $this->successResponse([
                'answer' => $answer,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
