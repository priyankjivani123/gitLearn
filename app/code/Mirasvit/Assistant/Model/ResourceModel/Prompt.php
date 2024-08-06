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

namespace Mirasvit\Assistant\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mirasvit\Assistant\Api\Data\PromptInterface;

class Prompt extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(PromptInterface::TABLE_NAME, PromptInterface::ID);
    }
}