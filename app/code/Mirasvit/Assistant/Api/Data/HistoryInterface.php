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

interface HistoryInterface
{
    const TABLE_NAME = 'mst_assistant_history';

    const ID = 'history_id';

    const RULE_ID = 'rule_id';
    const ENTITY_ID = 'entity_id';
    const CREATED_AT = 'created_at';
    const OLD_VALUE = 'old_value';
    const NEW_VALUE = 'new_value';
    const IS_REMOVED = 'is_removed';


    public function getId(): ?int;

    public function setId(int $value);

    public function getRuleId(): int;

    public function setRuleId(int $value): HistoryInterface;

    public function getOldValue(): string;

    public function setOldValue(string $value): HistoryInterface;

    public function getNewValue(): string;

    public function setNewValue(string $value): HistoryInterface;

    public function getCreatedAt(): string;

    public function setCreatedAt(string $value): HistoryInterface;

    public function isRemoved(): bool;

    public function setIsRemoved(bool $value): HistoryInterface;
}
