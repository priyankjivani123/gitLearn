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

namespace Mirasvit\Assistant\Ui\Rule\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Assistant\Api\Data\RuleInterface;
use Mirasvit\Assistant\Model\ConfigProvider;
use Mirasvit\Assistant\Repository\RuleRepository;

class DataProvider extends AbstractDataProvider
{
    private $repository;

    private $context;

    private $uiComponentFactory;

    private $configProvider;

    public function __construct(
        UiComponentFactory $uiComponentFactory,
        ConfigProvider $configProvider,
        RuleRepository $repository,
        ContextInterface $context,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->repository         = $repository;
        $this->uiComponentFactory = $uiComponentFactory;
        $this->configProvider     = $configProvider;
        $this->collection         = $this->repository->getCollection();
        $this->context            = $context;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $result = [];

        $model = $this->getModel();

        if ($model) {
            $data = $model->getData();
            $data['type_disabled'] = 1;
            $result[$model->getId()] = $data;
        }
        return $result;
    }

    private function getModel(): ?RuleInterface
    {
        $id = (int)$this->context->getRequestParam($this->getRequestFieldName(), null);
        return $id ? $this->repository->get($id) : null;
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        $model = $this->getModel();

        if (!$model) {
            return $meta;
        }

        $componentName = 'assistant_rule_conditions';

        $component = $this->uiComponentFactory->create($componentName);
        $meta      = $this->prepareComponent($component)['children'];

        return $meta;
    }

    protected function prepareComponent(UiComponentInterface $component)
    {
        $data = [];
        foreach ($component->getChildComponents() as $name => $child) {
            $data['children'][$name] = $this->prepareComponent($child);
        }

        $data['arguments']['data']  = $component->getData();
        $data['arguments']['block'] = $component->getBlock();

        return $data;
    }
}
