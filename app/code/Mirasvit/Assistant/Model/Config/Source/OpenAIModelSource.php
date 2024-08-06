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

use Mirasvit\Assistant\Model\ConfigProvider;
use Magento\Framework\Data\OptionSourceInterface;

class OpenAIModelSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __("GPT-4 Turbo (".ConfigProvider::OPENAI_MODEL_GTP4_TURBO.")"),
                'value' => ConfigProvider::OPENAI_MODEL_GTP4_TURBO
            ],
            [
                'label' => __("GPT-4 (".ConfigProvider::OPENAI_MODEL_GTP4.")"),
                'value' => ConfigProvider::OPENAI_MODEL_GTP4
            ],
            [
                'label' => __("GPT-3.5 (".ConfigProvider::OPENAI_MODEL_GTP35.")"),
                'value' => ConfigProvider::OPENAI_MODEL_GTP35
            ],
            [
                'label' => __("GPT-3 (".ConfigProvider::OPENAI_MODEL_GTP3.")"),
                'value' => ConfigProvider::OPENAI_MODEL_GTP3
            ],
        ];
    }
}
