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
use Mirasvit\Assistant\Api\Data\LogInterface;
use Mirasvit\Assistant\Api\Data\LogInterfaceFactory;
use Mirasvit\Assistant\Model\ResourceModel\Log\Collection;
use Mirasvit\Assistant\Model\ResourceModel\Log\CollectionFactory;

class LogRepository
{
    private $entityManager;

    private $factory;

    private $collectionFactory;

    public function __construct(
        EntityManager       $entityManager,
        LogInterfaceFactory $factory,
        CollectionFactory   $collectionFactory
    ) {
        $this->entityManager     = $entityManager;
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    public function create(): LogInterface
    {
        return $this->factory->create();
    }

    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function get(int $id): ?LogInterface
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function save(LogInterface $model): LogInterface
    {
        $this->entityManager->save($model);

        return $model;
    }

    public function delete(LogInterface $model)
    {
        $this->entityManager->delete($model);

        return $this;
    }
}
