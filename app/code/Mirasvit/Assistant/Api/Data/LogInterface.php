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


interface LogInterface
{
    const TABLE_NAME = 'mst_assistant_rule_log';

    const ID              = 'log_id';
    const RULE_ID         = 'rule_id';
    const IDENTIFIER      = 'identifier';
    const TYPE            = 'type';
    const MESSAGE         = 'message';
    const ADDITIONAL_DATA = 'additional_data';
    const CREATED_AT      = 'created_at';

    const TYPE_INFO    = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR   = 'error';

    public function getRuleId(): int;

    public function setRuleId(int $ruleId): self;

    public function getIdentifier(): string;

    public function setIdentifier(string $identifier): self;

    public function getType(): string;

    public function setType(string $type): self;

    public function getMessage(): string;

    public function setMessage(string $message): self;

    public function getAdditionalData(): array;

    public function setAdditionalData(array $data): self;
}
