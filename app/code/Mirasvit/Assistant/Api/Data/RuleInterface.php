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

interface RuleInterface
{
    const TABLE_NAME = 'mst_assistant_rule';

    const ID                    = 'rule_id';
    const TITLE                 = 'title';
    const ENTITY                = 'entity';
    const PROMPT_ID             = 'prompt_id';
    const FIELD                 = 'field';
    const IS_ACTIVE             = 'is_active';
    const IS_ONCE               = 'is_once';
    const IS_OVERWRITE          = 'is_overwrite';
    const STORE_IDS             = 'store_ids';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';

    const ENTITY_PRODUCT  = 'product';
    const ENTITY_CATEGORY = 'category';

    public function getId(): ?int;

    public function setId(int $value);

    public function getTitle(): string;

    public function setTitle(string $value): RuleInterface;

    public function getEntity(): string;

    public function setEntity(string $value): RuleInterface;

    public function getPromptId(): int;

    public function setPromptId(int $value): RuleInterface;

    public function getField(): string;

    public function setField(string $value): RuleInterface;

    public function isActive(): bool;

    public function setIsActive(bool $value): RuleInterface;

    public function isOnce(): bool;

    public function setIsOnce(bool $value): RuleInterface;

    public function isOverwrite(): bool;

    public function setIsOverwrite(bool $value): RuleInterface;

    public function getStoreIds(): array;

    public function setStoreIds(array $value): RuleInterface;

    public function setConditionsSerialized(string $value): RuleInterface;
}
