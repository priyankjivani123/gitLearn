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

namespace Mirasvit\Assistant\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Mirasvit\Assistant\Repository\PromptRepository;

class PromptSource implements OptionSourceInterface
{
    protected $promptRepository;

    protected $registry;

    public function __construct(
        PromptRepository $promptRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->promptRepository = $promptRepository;
        $this->registry         = $registry;
    }

    public function toOptionArray(): array
    {
        $currentRule = $this->registry->registry('current_rule');

        if (!$currentRule || !$currentRule->getId()) {
            return [];
        }

        $prompts = $this->promptRepository->getCollection()->addFieldToFilter("is_active", true);
        $options = [];
        foreach ($prompts as $prompt) {
            if (!in_array($currentRule->getEntity(), $prompt->getScopes())) {
                continue;
            }

            $options[] = [
                'label' => $prompt->getTitle()." / ".$prompt->getCode(),
                'value' => $prompt->getId()
            ];
        }

        return $options;
    }
}
