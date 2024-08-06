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

namespace Mirasvit\Assistant\Setup;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Assistant\Api\Data\PromptInterface;
use Mirasvit\Assistant\Repository\PromptRepository;
use Mirasvit\Core\Service\YamlService;

class Recurring implements InstallSchemaInterface
{
    private $promptRepository;
    private $yamlService;

    public function __construct(
        PromptRepository $promptRepository,
        YamlService $yamlService
    ) {
        $this->promptRepository = $promptRepository;
        $this->yamlService = $yamlService;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->renamePrompts();

        $prompts = $this->yamlService->loadFile(dirname(__FILE__)."/prompts.yaml");
        foreach ($prompts as $prompt) {
            if (!isset($prompt['is_modal'])) {
                $prompt['is_modal'] = false;
            }
            if (!isset($prompt['is_convert2html'])) {
                $prompt['is_convert2html'] = false;
            }
            if (!isset($prompt['placeholder'])) {
                $prompt['placeholder'] = '';
            }
            if (!isset($prompt['frequency_penalty'])) {
                $prompt['frequency_penalty'] = 0;
            }
            if (!isset($prompt['stop_sequences'])) {
                $prompt['stop_sequences'] = "";
            }
            $this->ensurePrompt(
                $this->promptRepository->create()
                    ->setTitle($prompt['title'])
                    ->setCode($prompt['code'])
                    ->setPrompt($prompt['prompt'])
                    ->setPlaceholder($prompt['placeholder'])
                    ->setScopes($prompt['scope'])
                    ->setFieldSelector($prompt['field_selector'])
                    ->setPosition($prompt['position'])
                    ->setIsActive($prompt['is_active'])
                    ->setIsModal($prompt['is_modal'])
                    ->setFrequencyPenalty($prompt['frequency_penalty'])
                    ->setStopSequences($prompt['stop_sequences'])
                    ->setIsConvert2Html($prompt['is_convert2html'])
            );
        }
    }

    private function renamePrompts() {
        $rename = [
            "mst_meta_title" => "mst_product_meta_title",
            "mst_meta_keywords" => "mst_product_meta_keywords",
            "mst_meta_description" => "mst_product_meta_description",
            "mst_short_description" => "mst_product_short_description",
            "mst_description" => "mst_product_description",
            "mst_friendly" => "mst_helpdesk_friendly",
            "mst_grammar" => "mst_helpdesk_grammar",
            "mst_ticket" => "mst_helpdesk_message",
        ];
        $prompts = $this->promptRepository->getCollection();
        foreach ($prompts as $prompt) {
            if (isset($rename[$prompt["code"]])) {
                $prompt["code"] = $rename[$prompt["code"]];
                $this->promptRepository->save($prompt);
            }
        }
    }

    private function ensurePrompt(PromptInterface $prompt)
    {
        $prompts = $this->promptRepository->getCollection()
                ->addFieldToFilter(PromptInterface::CODE, $prompt->getCode());
        if (count($prompts)) {
            $p = $prompts->getFirstItem();
            $prompt->setId($p->getId());
            $prompt->setIsActive((bool)$p->isActive());
            $prompt->setTitle($p->getTitle());
            $prompt->setPlaceholder($p->getPlaceholder());
            $prompt->setPosition($p->getPosition());
            $this->promptRepository->save($prompt);
        } else {
            $this->promptRepository->save($prompt);
        }
    }
}
