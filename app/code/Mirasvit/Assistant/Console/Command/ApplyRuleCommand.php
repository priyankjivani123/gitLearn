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



namespace Mirasvit\Assistant\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class RefreshReportStatistic
 *
 * Console command - refreshes report statistic in admin
 *
 * @package Mirasvit\Rewards\Console\Command
 */
class ApplyRuleCommand extends Command
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
        $this->setName('mirasvit:assistant:apply-rule')
            ->setDescription('Applies AI Assistant Rule');
        $this->addOption('id', 'id', InputOption::VALUE_REQUIRED, 'Apply particular rule');
        $this->addOption('entity-id', 'entity-id', InputOption::VALUE_OPTIONAL, 'Run on specific entity ID only');
        $this->addOption('dry-run', 'dry-run', InputOption::VALUE_NONE, 'Test run. Without any changes in DB');
        $this->addOption('reset', 'reset', InputOption::VALUE_NONE, 'Reset rule (applicable only for rules with "Apply Only Once" = "Yes")');
        $this->addOption('force', 'force', InputOption::VALUE_NONE, 'Forcibly apply rule (will terminate previous process for current rule if running)');
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

        $reset  = (bool)$input->getOption('reset');
        $dryRun = (bool)$input->getOption('dry-run');

        if ($reset) {
            $output->writeln('<info>' . 'Resetting...' . '</info>');

            if ($dryRun) {
                $output->writeln('<info>' . 'Resetting rule is not applicable in test run.' . '</info>');
                return 0;
            }
        } else {
            $output->writeln('<info>' . 'Applying...' . '</info>');
        }

        $id = (int)$input->getOption('id');
        if (!$id) {
            $output->writeln('<error>' . 'ID is not set.' . '</error>');
            return 1;
        }

        $entityId = (int)$input->getOption('entity-id');

        /** @var \Mirasvit\Assistant\Service\ApplyRuleService $service */
        $service = $objectManager->create('Mirasvit\Assistant\Service\ApplyRuleService');

        if ($reset) {
            $service->resetInHistory($id, $entityId);

            $output->writeln('<info>' . 'Done. Rule can be re-applied.' . '</info>');

            return 0;
        }

        $force = (bool)$input->getOption('force');

        if ($force && !$dryRun) {
            $question = "<info>Using --force flag might result in terminating already running process for current rule.</info> Proceed? [y/n]: ";

            $q = $objectManager->create(
                ConfirmationQuestion::class,
                [
                    'question' => $question,
                    'default'  => false
                ]
            );

            $helper = $this->getHelper('question');
            $answer = $helper->ask($input, $output, $q);

            if (!$answer) { // answer is everything but 'y' or 'yes'
                $output->writeln('<info>' . 'Aborted.' . '</info>');
                return 0;
            }
        }

        $service->apply($id, $entityId, $dryRun, $force);

        $output->writeln('<info>' . 'Rule is applied.' . '</info>');

        return 0;
    }
}
