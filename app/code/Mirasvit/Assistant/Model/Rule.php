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

use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Assistant\Api\Data\RuleInterface;
use Mirasvit\CatalogLabel\Repository\DisplayRepository;
use Mirasvit\CatalogLabel\Repository\PlaceholderRepository;

class Rule  extends AbstractModel implements IdentityInterface, RuleInterface
{
    const CACHE_TAG = "assistant_rule";

    private $ruleFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Assistant\Model\Rule\ConditionRuleFactory $ruleFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ruleFactory            = $ruleFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Rule::class);
    }

    private $rule;

    /**
     * @var string
     */
    protected $_cacheTag = 'assistant_rule';
    /**
     * @var string
     */
    protected $_eventPrefix = 'assistant_rule';

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function setId($value): Rule
    {
        return $this->setData(self::ID, $value);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(self::TITLE);
    }

    public function setTitle(string $value): RuleInterface
    {
        return $this->setData(self::TITLE, $value);
    }

    public function getEntity(): string
    {
        return (string)$this->getData(self::ENTITY);
    }

    public function setEntity(string $value): RuleInterface
    {
        return $this->setData(self::ENTITY, $value);
    }

    public function getPromptId(): int
    {
        return (int)$this->getData(self::PROMPT_ID);
    }

    public function setPromptId(int $value): RuleInterface
    {
        return $this->setData(self::PROMPT_ID, $value);
    }

    public function getField(): string
    {
        return (string)$this->getData(self::FIELD);
    }

    public function setField(string $value): RuleInterface
    {
        return $this->setData(self::FIELD, $value);
    }

    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): RuleInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function isOnce(): bool
    {
        return (bool)$this->getData(self::IS_ONCE);
    }

    public function setIsOnce(bool $value): RuleInterface
    {
        return $this->setData(self::IS_ONCE, $value);
    }

    public function isOverwrite(): bool
    {
        return (bool)$this->getData(self::IS_OVERWRITE);
    }

    public function setIsOverwrite(bool $value): RuleInterface
    {
        return $this->setData(self::IS_OVERWRITE, $value);
    }

    public function getStoreIds(): array
    {
        $value = $this->getData(self::STORE_IDS);
        return $value ? explode(",", $value) : [0];
    }

    public function setStoreIds(array $value): RuleInterface
    {
        $value = implode(",", $value);
        return $this->setData(self::STORE_IDS, $value);
    }

    public function getRule(): \Mirasvit\Assistant\Model\Rule\ConditionRule
    {
        if (!$this->rule) {
            $this->rule = $this->ruleFactory->create()->setRuleId($this->getId())
                ->setData(self::CONDITIONS_SERIALIZED, $this->getData(self::CONDITIONS_SERIALIZED));
        }

        return $this->rule;
    }

    public function setConditionsSerialized(string $value): RuleInterface
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $value);
    }
}
