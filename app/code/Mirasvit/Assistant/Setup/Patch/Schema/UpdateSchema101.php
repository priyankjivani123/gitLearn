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

namespace Mirasvit\Assistant\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Mirasvit\Assistant\Api\Data\PromptInterface;
use Mirasvit\Assistant\Api\Data\RuleInterface;

class UpdateSchema101 implements DataPatchInterface, PatchVersionInterface
{
    private $setup;

    public function __construct(ModuleDataSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    public static function getDependencies()
    {
        return [];
    }

    public static function getVersion()
    {
        return '1.0.1';
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $setup = $this->setup;

        $setup->getConnection()->startSetup();

        $setup->getConnection()->addForeignKey(
            $setup->getConnection()->getForeignKeyName(
                $setup->getTable(RuleInterface::TABLE_NAME),
                RuleInterface::PROMPT_ID,
                $setup->getTable(PromptInterface::TABLE_NAME),
                PromptInterface::ID
            ),
            $setup->getTable(RuleInterface::TABLE_NAME),
            RuleInterface::PROMPT_ID,
            $setup->getTable(PromptInterface::TABLE_NAME),
            PromptInterface::ID
        );

        $setup->getConnection()->endSetup();
    }
}
