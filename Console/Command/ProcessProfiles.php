<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/29/16
 * Time: 12:55 PM
 */

namespace Toppik\Subscriptions\Console\Command;


use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessProfiles extends Command
{

    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var State
     */
    private $state;

    /**
     * ProcessProfiles constructor.
     * @param ManagerInterface $eventManager
     * @param State $state
     */
    public function __construct(
        ManagerInterface $eventManager,
        State $state
    )
    {
        $this->eventManager = $eventManager;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('subscriptions:profiles:process');
        $this->setDescription('Process Profiles');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->state->setAreaCode('frontend');
        $result = new DataObject;
        $this->eventManager->dispatch('subscriptions_profiles_process', ['result' => $result, ]);
        $output->writeln('Subscription profiles have been processed');
    }


}