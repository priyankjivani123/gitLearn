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

use Magento\Store\Model\StoreManagerInterface;

class StoreContext extends AbstractContext
{
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function context(): ?array
    {
        $store = $this->storeManager->getStore();

        return [
            [
                'id'    => 'store.name',
                'label' => 'Store Name',
                'value' => $store->getName(),
            ],
            [
                'id'    => 'store.code',
                'label' => 'Store Code',
                'value' => $store->getCode(),
            ],
        ];
    }
}