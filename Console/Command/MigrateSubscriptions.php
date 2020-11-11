<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/12/16
 * Time: 5:33 PM
 */

namespace Toppik\Subscriptions\Console\Command;

use Magento\Framework\Registry;
use Migration\App\Progress;
use Migration\Config;
use Migration\Console\AbstractMigrateCommand;
use Migration\Logger\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Toppik\Subscriptions\Migration\Mode\Subscriptions;

class MigrateSubscriptions extends AbstractMigrateCommand
{

    /**
     * @var Subscriptions
     */
    private $subscriptionMode;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * MigrateSubscriptions constructor.
     * @param Registry $registry
     * @param Subscriptions|Subscriptions\Proxy $subscriptionMode
     * @param Config $config
     * @param Manager $logManager
     * @param Progress $progress
     */
    public function __construct(
        Registry $registry,
        Subscriptions\Proxy $subscriptionMode,
        Config $config,
        Manager $logManager,
        Progress $progress)
    {
        $this->subscriptionMode = $subscriptionMode;
        $this->registry = $registry;
        parent::__construct($config, $logManager, $progress);
    }

    protected function configure()
    {
        $this->setName('migrate:subscriptions')
            ->setDescription('Subscription migration');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registry->register('config', $input->getArgument('config'));
        $this->subscriptionMode->run();
    }

}