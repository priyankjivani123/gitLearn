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


namespace Mirasvit\Assistant\Service;

use Liquid\Liquid;
use Mirasvit\Assistant\Model\ConfigProvider;
use Mirasvit\Core\Service\AbstractValidator;


class ValidationService extends AbstractValidator
{
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function testAssitantReady()
    {
        if (!class_exists(Liquid::class)) {
            $this->addError(
                'Required package <b>liquid/liquid</b> is missed. '
                . 'Install the package using the command <b><code>composer require liquid/liquid:~1.4</code></b>'
            );
        }

        if (!$this->configProvider->OpenAIKey()) {
            $this->addError(
                'OpenAI Secret Key is not set. '
                . 'Generate an <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI API key</a>. '
                . 'You\'ll need to create an account if you don\'t have one.'
            );
        }
    }
}
