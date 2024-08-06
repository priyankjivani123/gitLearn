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

namespace Mirasvit\Assistant\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\Assistant\Api\Data\RuleInterface;
use Mirasvit\Assistant\Api\Data\RuleInterfaceFactory;
use Mirasvit\Assistant\Model\ResourceModel\Rule\CollectionFactory;

class RuleRepository
{
    private $entityManager;

    private $factory;

    private $collectionFactory;

    public function __construct(
        EntityManager          $entityManager,
        RuleInterfaceFactory $factory,
        CollectionFactory      $collectionFactory
    ) {
        $this->entityManager     = $entityManager;
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Mirasvit\Assistant\Model\ResourceModel\Rule\Collection | RuleInterface[]
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    public function create(): RuleInterface
    {
        return $this->factory->create();
    }

    public function get(int $id): ?RuleInterface
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }


    public function save(RuleInterface $model): RuleInterface
    {
        $model = $this->entityManager->save($model);

        $conditionRule = $model->getRule();

        if (!$conditionRule->getRuleId()) { // need to set rude id manually for new rule
            $conditionRule->setRuleId($model->getId());
        }

        if ($model->getEntity() == RuleInterface::ENTITY_PRODUCT) {
            $conditionRule->updateRuleProductData();
        }

        return $model;
    }

    public function delete(RuleInterface $model)
    {
        $model->getRule()->updateRuleProductData(null, true);

        $this->entityManager->delete($model);

        return $this;
    }
}
