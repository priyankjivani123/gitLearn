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


namespace Mirasvit\Assistant\Console\Command;


use Magento\Framework\App\ObjectManagerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexCommand extends Command
{
    private $objectManagerFactory;

    public function __construct(
        ObjectManagerFactory $objectManagerFactory
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mirasvit:assistant:reindex')
            ->setDescription('Reindex AI Assistant Automation Rules (Rule-Product relations)');
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Cleanup Rule-Product relations');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $omParams = $_SERVER;
        $omParams[StoreManager::PARAM_RUN_CODE] = 'admin';
        $omParams[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $objectManager = $this->objectManagerFactory->create($omParams);

        /** @var \Magento\Framework\App\State $state */
        $state = $objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('global'); //2.1, 2.2.2 supports this

        $remove = false;

        if ($input->getOption('reset')) {
            $output->writeln('<info>' . 'Removing Rule-Product relations' . '</info>');
            $remove = true;
        } else {
            $output->writeln('<info>' . 'Updating Rule-Product relations' . '</info>');
        }

        /** @var \Mirasvit\Assistant\Service\ApplyRuleService $service */
        $service = $objectManager->create('Mirasvit\Assistant\Service\ApplyRuleService');
        $service->updateRuleProductRelation(null, $remove);

        $output->writeln('<info>' . 'Done!' . '</info>');

        return 0;
    }
}
