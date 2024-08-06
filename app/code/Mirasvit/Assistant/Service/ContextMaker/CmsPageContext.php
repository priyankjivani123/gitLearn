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

use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;

class CmsPageContext extends AbstractContext
{
    private $registry;

    private $priceCurrency;

    public function __construct(
        Registry               $registry
    ) {
        $this->registry      = $registry;
    }

    public function context(): ?array
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->registry->registry('cms_page');

        if (!$page || !$page->getId()) {
            return null;
        }

        return [
            [
                'id'    => 'page.title',
                'label' => 'Title',
                'value' => $this->stripTags((string)$page->getTitle()),
            ],
            [
                'id'    => 'page.content_heading',
                'label' => 'Content Heading',
                'value' => $this->stripTags((string)$page->getContentHeading()),
            ],
            [
                'id'    => 'page.content',
                'label' => 'Content',
                'value' => $this->stripTags((string)$page->getContent()),
            ],
        ];
    }
}
