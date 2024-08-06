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

namespace Mirasvit\Assistant\Api\Data;

interface PromptInterface
{
    const TABLE_NAME = 'mst_assistant_prompt';

    const ID = 'prompt_id';

    const TITLE             = 'title';
    const CODE              = 'code';
    const PLACEHOLDER       = 'placeholder';
    const PROMPT            = 'prompt';
    const SCOPES            = 'scopes';
    const FIELD_SELECTOR    = 'field_selector';
    const IS_ACTIVE         = 'is_active';
    const IS_MODAL          = 'is_modal';
    const IS_CONVERT2HTML   = 'is_convert2html';
    const POSITION          = 'position';
    const FREQUENCY_PENALTY = 'frequency_penalty';
    const STOP_SEQUENCES    = 'stop_sequences';
    const AI_MODEL          = 'ai_model';
    const CONFIG_MODEL      = 'config';

    public function getId(): ?int;

    public function setId(int $value);

    public function getTitle(): string;

    public function setTitle(string $value): PromptInterface;

    public function getCode(): string;

    public function setCode(string $value): PromptInterface;

    public function getPlaceholder(): string;

    public function setPlaceholder(string $value): PromptInterface;

    public function getPrompt(): string;

    public function setPrompt(string $value): PromptInterface;

    public function getScopes(): array;

    public function setScopes(string $value): PromptInterface;

    public function getFieldSelector(): string;

    public function setFieldSelector(string $value): PromptInterface;

    public function isActive(): bool;

    public function setIsActive(bool $value): PromptInterface;

    public function isModal(): bool;

    public function setIsModal(bool $value): PromptInterface;

    public function isConvert2Html(): bool;

    public function setIsConvert2Html(bool $value): PromptInterface;

    public function getPosition(): int;

    public function setPosition(int $value): PromptInterface;

    public function getFrequencyPenalty(): float;

    public function setFrequencyPenalty(float $value): PromptInterface;

    public function getStopSequences(): string;

    public function setStopSequences(string $value): PromptInterface;

    public function getOpenAIModel(): string;

    public function setOpenAIModel(string $value): PromptInterface;
}
