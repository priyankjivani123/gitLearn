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

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Assistant\Api\Data\PromptInterface;

class Prompt extends AbstractModel implements PromptInterface
{
    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getTitle(): string
    {
        return (string)$this->getData(self::TITLE);
    }

    public function setTitle(string $value): PromptInterface
    {
        return $this->setData(self::TITLE, $value);
    }

    public function getCode(): string
    {
        return (string)$this->getData(self::CODE);
    }

    public function setCode(string $value): PromptInterface
    {
        return $this->setData(self::CODE, $value);
    }

    public function getPlaceholder(): string
    {
        return (string)$this->getData(self::PLACEHOLDER);
    }

    public function setPlaceholder(string $value): PromptInterface
    {
        return $this->setData(self::PLACEHOLDER, $value);
    }

    public function getPrompt(): string
    {
        return (string)$this->getData(self::PROMPT);
    }

    public function setPrompt(string $value): PromptInterface
    {
        return $this->setData(self::PROMPT, $value);
    }

    public function getScopes(): array
    {
        $scopes = $this->getData(self::SCOPES);
        $scopes = explode(',', $scopes);
        $scopes = array_map('trim', $scopes);
        return array_filter($scopes, function ($scope) {
            return $scope != '';
        });
    }

    public function setScopes(string $value): PromptInterface
    {
        return $this->setData(self::SCOPES, $value);
    }

    public function getFieldSelector(): string
    {
        return (string)$this->getData(self::FIELD_SELECTOR);
    }

    public function setFieldSelector(string $value): PromptInterface
    {
        return $this->setData(self::FIELD_SELECTOR, $value);
    }

    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): PromptInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function isModal(): bool
    {
        return (bool)$this->getData(self::IS_MODAL);
    }

    public function setIsModal(bool $value): PromptInterface
    {
        return $this->setData(self::IS_MODAL, $value);
    }

    public function isConvert2Html(): bool
    {
        return (bool)$this->getData(self::IS_CONVERT2HTML);
    }

    public function setIsConvert2Html(bool $value): PromptInterface
    {
        return $this->setData(self::IS_CONVERT2HTML, $value);
    }

    public function getPosition(): int
    {
        return (int)$this->getData(self::POSITION);
    }

    public function setPosition(int $value): PromptInterface
    {
        return $this->setData(self::POSITION, $value);
    }

    public function getFrequencyPenalty(): float
    {
        return (float)$this->getData(self::FREQUENCY_PENALTY);
    }

    public function setFrequencyPenalty(float $value): PromptInterface
    {
        return $this->setData(self::FREQUENCY_PENALTY, $value);
    }

    public function getStopSequences(): string
    {
       return (string)$this->getData(self::STOP_SEQUENCES);
    }

    public function setStopSequences(string $value): PromptInterface
    {
        return $this->setData(self::STOP_SEQUENCES, $value);
    }
    
    public function getOpenAIModel(): string
    {
        return (string)$this->getData(self::AI_MODEL) ?: self::CONFIG_MODEL;
    }
    
    public function setOpenAIModel(string $value): PromptInterface
    {
        return $this->setData(self::AI_MODEL, $value);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Prompt::class);
    }
}
