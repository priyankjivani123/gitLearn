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


namespace Mirasvit\Assistant\Service\ContextMaker;


use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;

class KbCategoryContext extends AbstractContext
{
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function context(): ?array
    {
        if (!class_exists('Mirasvit\Kb\Model\Category')) {
            return null;
        }

        /** @var \Mirasvit\Kb\Model\Category|null $category */
        $category = $this->registry->registry('current_category');

        if (!$category || !($category instanceof \Mirasvit\Kb\Model\Category)) {
            return null;
        }

        $context = [
            [
                'id'    => 'kb_category.name',
                'label' => 'Title',
                'value' => $this->stripTags((string)$category->getName()),
            ],
            [
                'id'    => 'kb_category.content',
                'label' => 'Content',
                'value' => $this->stripTags((string)$category->getDescriptionHtml()),
            ],
            [
                'id'    => 'kb_category.meta_title',
                'label' => 'Meta Title',
                'value' => $this->stripTags((string)$category->getMetaTitle()),
            ],
            [
                'id'    => 'kb_category.meta_description',
                'label' => 'Meta Description',
                'value' => $this->stripTags((string)$category->getMetaDescription()),
            ],
            [
                'id'    => 'kb_category.meta_keywords',
                'label' => 'Meta Keywords',
                'value' => $this->stripTags((string)$category->getMetaKeywords()),
            ],
        ];

        $fullData = '';

        foreach ($context as $data) {
            if (!trim((string)$data['value'])) {
                continue;
            }

            $fullData .= $data['label'] . ': ' . $data['value'] . PHP_EOL;
        }

        $context[] = [
            'id'    => 'kb_category.data',
            'label' => 'Knowledge Base Category Data',
            'value' => $fullData
        ];

        return $context;
    }
}
