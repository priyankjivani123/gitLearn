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


namespace Mirasvit\Assistant\Service\ContextMaker\Magefan;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Assistant\Service\ContextMaker\AbstractContext;

class BlogPostContext extends AbstractContext
{
    private $request;

    private $moduleManager;
    
    private $objectManager;

    public function __construct(
        RequestInterface $request,
        Manager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->request       = $request;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    public function context(): ?array
    {
        if (!$this->moduleManager->isEnabled('Magefan_Blog')) {
            return null;
        }

        if (strpos($this->request->getFullActionName(), 'blog_post') === false) {
            return null;
        }

        $postId = $this->request->getParam('id');

        if (!$postId) {
            return null;
        }

        $post = null;
        /** @var \Magefan\Blog\Model\PostRepository $blogPostRepository */
        $blogPostRepository = $this->objectManager->get('Magefan\Blog\Model\PostRepository');

        try {
            $post = $blogPostRepository->getById($postId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        $context = [
            [
                'id'    => 'post.title',
                'label' => 'Title',
                'value' => $this->stripTags((string)$post->getTitle()),
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
