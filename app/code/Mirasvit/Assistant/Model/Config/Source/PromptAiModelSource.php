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


use Magento\Framework\Data\OptionSourceInterface;

class PromptAiModelSource implements OptionSourceInterface
{
    private $openAiModelSource;
    
    public function __construct(OpenAIModelSource $openAiModelSource)
    {
        $this->openAiModelSource = $openAiModelSource;
    }
    
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('From config'),
                'value' => 'config'
            ]
        ];
        
        return array_merge($options, $this->openAiModelSource->toOptionArray());
    }
}