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



namespace Mirasvit\Assistant\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\Auth\Session;
use Mirasvit\Assistant\Api\Data\PromptInterface;
use Mirasvit\Assistant\Model\Config\Source\OpenAIModelSource;
use Mirasvit\Assistant\Model\ConfigProvider;
use Mirasvit\Assistant\Repository\PromptRepository;
use Mirasvit\Assistant\Service\ContextMakerService;

class Loader extends Template
{
    private $contextMakerService;

    private $promptRepository;

    private $session;

    private $modelSource;

    private $configProvider;

    public function __construct(
        PromptRepository    $promptRepository,
        ContextMakerService $contextMakerService,
        Session             $session,
        OpenAIModelSource   $modelSource,
        ConfigProvider      $configProvider,
        Template\Context    $context,
        array               $data = []
    ) {
        $this->promptRepository    = $promptRepository;
        $this->contextMakerService = $contextMakerService;
        $this->session             = $session;
        $this->modelSource         = $modelSource;
        $this->configProvider      = $configProvider;

        parent::__construct($context, $data);
    }

    public function isProduction(): bool
    {
        $headers = getallheaders();
        if (is_array($headers) && isset($headers['x-build-react']) && $headers['x-build-react'] === "1") {
            return false;
        }

        return true;
    }

    public function _toHtml()
    {
        if ($this->session->getUser() === null) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getJsConfig()
    {
        $collection = $this->promptRepository->getCollection()
            ->addFieldToFilter(PromptInterface::IS_ACTIVE, true)
            ->setOrder(PromptInterface::POSITION);

        $prompts = [];
        foreach ($collection as $prompt) {
            $prompts[] = [
                'id'            => (string)$prompt->getId(),
                'code'          => $prompt->getCode(),
                'title'         => $prompt->getTitle(),
                'placeholder'   => $prompt->getPlaceholder(),
                'prompt'        => $prompt->getPrompt(),
                'scopes'        => $prompt->getScopes(),
                'fieldSelector' => str_replace("\n", ",",$prompt->getFieldSelector()),
                'isModal'       => $prompt->isModal(),
                'aiModel'       => $prompt->getOpenAIModel() !== PromptInterface::CONFIG_MODEL
                    ? $prompt->getOpenAIModel()
                    : $this->configProvider->OpenAIModel(),
            ];
        }

        return [
            'apiURL'     => rtrim($this->getUrl('assistant', ['_nosecret' => true]), '/') . '/',
            'cssURL'     => $this->isProduction() ? $this->getViewFileUrl('Mirasvit_Assistant::ui/app.min.css') : 'http://localhost:3005/app.css',
            'context'    => $this->contextMakerService->context(),
            'promptList' => $prompts,
            'modelList'  => $this->modelSource->toOptionArray()
        ];
    }
}
