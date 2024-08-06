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
use Mirasvit\Assistant\Api\Data\HistoryInterface;
use Mirasvit\Assistant\Api\Data\HistoryInterfaceFactory;
use Mirasvit\Assistant\Model\ResourceModel\History\CollectionFactory;

class HistoryRepository
{
    private $entityManager;

    private $factory;

    private $collectionFactory;

    public function __construct(
        EntityManager          $entityManager,
        HistoryInterfaceFactory $factory,
        CollectionFactory      $collectionFactory
    ) {
        $this->entityManager     = $entityManager;
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Mirasvit\Assistant\Model\ResourceModel\History\Collection | HistoryInterface[]
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    public function create(): HistoryInterface
    {
        return $this->factory->create();
    }

    public function get(int $id): ?HistoryInterface
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }


    public function save(HistoryInterface $model): HistoryInterface
    {
        $this->entityManager->save($model);

        return $model;
    }

    public function delete(HistoryInterface $model)
    {
        $this->entityManager->delete($model);

        return $this;
    }
}
