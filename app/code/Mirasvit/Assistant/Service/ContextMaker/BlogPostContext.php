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

class BlogPostContext extends AbstractContext
{
    private $registry;

    public function __construct()
    {
        $objectManager = ObjectManager::getInstance();

        if (class_exists('\Mirasvit\BlogMx\Registry')) {
            $this->registry = $objectManager->get('\Mirasvit\BlogMx\Registry');
        }
    }

    public function context(): ?array
    {
        if (!$this->registry) {
            return null;
        }

        /** @var \Mirasvit\BlogMx\Model\Post $post */
        $post = $this->registry->getPost();

        if (!$post) {
            return null;
        }

        $context = [
            [
                'id'    => 'post.title',
                'label' => 'Title',
                'value' => $this->stripTags((string)$post->getName()),
            ],
            [
                'id'    => 'post.content',
                'label' => 'Content',
                'value' => $this->stripTags((string)$post->getContent()),
            ],
            [
                'id'    => 'post.short_content',
                'label' => 'Short Content',
                'value' => $this->stripTags((string)$post->getShortContent()),
            ],
            [
                'id'    => 'post.meta_title',
                'label' => 'Meta Title',
                'value' => $this->stripTags((string)$post->getMetaTitle()),
            ],
            [
                'id'    => 'post.meta_description',
                'label' => 'Meta Description',
                'value' => $this->stripTags((string)$post->getMetaDescription()),
            ],
            [
                'id'    => 'post.meta_keywords',
                'label' => 'Meta Keywords',
                'value' => $this->stripTags((string)$post->getMetaKeywords()),
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
            'id'    => 'post.data',
            'label' => 'Post Data',
            'value' => $fullData
        ];

        return $context;
    }
}
