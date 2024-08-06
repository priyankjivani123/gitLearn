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
use Mirasvit\Assistant\Api\Data\LogInterface;
use Mirasvit\Core\Service\SerializeService;

class Log extends AbstractModel implements LogInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Log::class);
    }

    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getRuleId(): int
    {
        return (int)$this->getData(self::RULE_ID);
    }

    public function setRuleId(int $ruleId): LogInterface
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    public function getIdentifier(): string
    {
        return $this->getData(self::IDENTIFIER);
    }

    public function setIdentifier(string $identifier): LogInterface
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    public function getType(): string
    {
        return $this->getData(self::TYPE);
    }

    public function setType(string $type): LogInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    public function getMessage(): string
    {
        return (string)$this->getData(self::MESSAGE);
    }

    public function setMessage(string $message): LogInterface
    {
        return $this->setData(self::MESSAGE, $message);
    }

    public function getAdditionalData(): array
    {
        $additionalData = $this->getData(self::ADDITIONAL_DATA);

        return $additionalData ? SerializeService::decode($additionalData) : [];
    }

    public function setAdditionalData(array $data): LogInterface
    {
        return $this->setData(self::ADDITIONAL_DATA, SerializeService::encode($data));
    }
}
