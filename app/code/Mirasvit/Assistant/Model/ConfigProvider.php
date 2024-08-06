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

namespace Mirasvit\Assistant\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Encryption\EncryptorInterface;

class ConfigProvider
{
    private $scopeConfig;
    private $encryptor;

    //https://platform.openai.com/docs/models/gpt-4
    const OPENAI_MODEL_GTP4        = 'gpt-4';
    const OPENAI_MODEL_GTP4_TURBO  = 'gpt-4-turbo-preview';
    //https://platform.openai.com/docs/models/gpt-3-5
    const OPENAI_MODEL_GTP35 = 'gpt-3.5-turbo';
    const OPENAI_MODEL_GTP3  = 'text-davinci-003';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    public function OpenAIKey(): string
    {
        $value = (string)$this->scopeConfig->getValue('assistant/general/openai_key');
        if (strpos($value, "sk-") === 0) { //for tests
            return $value;
        }
        $value = $this->encryptor->decrypt($value);
        return $value;
    }

    public function OpenAIModel(): string
    {
        return $this->scopeConfig->getValue('assistant/general/openai_model');
    }

    public function isLoggingEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('assistant/general/logging_enabled');
    }
}
