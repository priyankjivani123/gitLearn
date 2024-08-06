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


use Magento\Framework\Registry;

class KbArticleContext extends AbstractContext
{
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function context(): ?array
    {
        if (!class_exists('Mirasvit\Kb\Model\Article')) {
            return null;
        }

        /** @var \Mirasvit\Kb\Model\Article|null $article */
        $article = $this->registry->registry('current_article');

        if (!$article || !($article instanceof \Mirasvit\Kb\Model\Article)) {
            return null;
        }

        $context = [
            [
                'id'    => 'kb_article.name',
                'label' => 'Title',
                'value' => $this->stripTags((string)$article->getName()),
            ],
            [
                'id'    => 'kb_article.content',
                'label' => 'Content',
                'value' => $this->stripTags((string)$article->getTextHtml()),
            ],
            [
                'id'    => 'kb_article.meta_title',
                'label' => 'Meta Title',
                'value' => $this->stripTags((string)$article->getMetaTitle()),
            ],
            [
                'id'    => 'kb_article.meta_description',
                'label' => 'Meta Description',
                'value' => $this->stripTags((string)$article->getMetaDescription()),
            ],
            [
                'id'    => 'kb_article.meta_keywords',
                'label' => 'Meta Keywords',
                'value' => $this->stripTags((string)$article->getMetaKeywords()),
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
            'id'    => 'kb_article.data',
            'label' => 'Knowledge Base Article Data',
            'value' => $fullData
        ];

        return $context;
    }
}
