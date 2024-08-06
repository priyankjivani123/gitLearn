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
use Mirasvit\Assistant\Api\Data\HistoryInterface;

class History extends AbstractModel implements HistoryInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\History::class);
    }

    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getRuleId(): int
    {
        return (int)$this->getData(self::RULE_ID);
    }

    public function setRuleId(int $value): HistoryInterface
    {
        return $this->setData(self::RULE_ID, $value);
    }

    public function getOldValue(): string
    {
        return (string)$this->getData(self::OLD_VALUE);
    }

    public function setOldValue(string $value): HistoryInterface
    {
        return $this->setData(self::OLD_VALUE, $value);
    }

    public function getNewValue(): string
    {
        return (string)$this->getData(self::NEW_VALUE);
    }

    public function setNewValue(string $value): HistoryInterface
    {
        return $this->setData(self::NEW_VALUE, $value);
    }

    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $value): HistoryInterface
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    public function isRemoved(): bool
    {
        return (bool)$this->getData(self::IS_REMOVED);
    }

    public function setIsRemoved(bool $value): HistoryInterface
    {
        return $this->setData(self::IS_REMOVED, $value);
    }
}
