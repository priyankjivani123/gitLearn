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

namespace Mirasvit\Assistant\Ui\Prompt\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Assistant\Api\Data\PromptInterface;
use Mirasvit\Assistant\Repository\PromptRepository;

class DataProvider extends AbstractDataProvider
{
    private $repository;

    private $context;


    public function __construct(
        PromptRepository $repository,
        ContextInterface $context,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->repository = $repository;
        $this->collection = $this->repository->getCollection();
        $this->context    = $context;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $result = [];

        $model = $this->getModel();

        if ($model) {
            $data = $model->getData();

            $data[PromptInterface::SCOPES] = $model->getScopes();
            if (strpos($model->getCode(), "mst_") === 0){
                $data['disabled'] = true;
            }
            $result[$model->getId()] = $data;
        }
        return $result;
    }

    private function getModel(): ?PromptInterface
    {
        $id = (int)$this->context->getRequestParam($this->getRequestFieldName(), null);
        return $id ? $this->repository->get($id) : null;
    }
}
