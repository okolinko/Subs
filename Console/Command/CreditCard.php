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
use Migration\Logger\MessageFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Migration\Config;
use Migration\Logger\Manager;
use Zend\Json\Json;

class CreditCard extends Command
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
     * @var \Migration\Logger\Logger
     */
    private $logger;
    /**
     * @var \Toppik\Subscriptions\Migration\Step\CreditCards\Data
     */
    private $ccData;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Manager
     */
    private $logManager;
    /**
     * @var \Migration\Logger\FileHandler
     */
    private $fileHandler;
    /**
     * @var MessageFormatter
     */
    private $messageFormatter;

    /**
     * ProcessProfiles constructor.
     * @param MessageFormatter $messageFormatter
     * @param \Migration\Logger\FileHandler\Proxy $fileHandler
     * @param Config $config
     * @param Manager $logManager
     * @param \Toppik\Subscriptions\Migration\Step\CreditCards\Data|\Toppik\Subscriptions\Migration\Step\CreditCards\Data\Proxy $ccData
     * @param ManagerInterface $eventManager
     * @param State $state
     */
    public function __construct(
        \Migration\Logger\MessageFormatter $messageFormatter,
        \Migration\Logger\FileHandler\Proxy $fileHandler,
        Config $config,
        Manager $logManager,
        \Toppik\Subscriptions\Migration\Step\CreditCards\Data\Proxy $ccData,
        ManagerInterface $eventManager,
        State $state
    )
    {
        $this->eventManager = $eventManager;
        $this->state = $state;
        $this->ccData = $ccData;
        $this->config = $config;
        $this->logManager = $logManager;
        parent::__construct();
        $this->fileHandler = $fileHandler;
        $this->messageFormatter = $messageFormatter;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {

        $config = $input->getArgument('config');

        $this->config->init($config);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('migrate:cc');
        $this->setDescription('Migrate CC');
        $this->setDefinition([
            new InputArgument(
                'references',
                InputArgument::REQUIRED,
                'Line of references'
            ),
            new InputArgument(
                'config',
                InputArgument::REQUIRED,
                'Config'
            ),
        ]);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $logger = $this->ccData->getLogger();
        $this->fileHandler->setFormatter($this->messageFormatter);
        $logger->pushHandler($this->fileHandler);
        $references = explode(',', $input->getArgument('references'));
        foreach($references as $ref) {
            if($temp = $this->ccData->getReferenceData($ref)) {
                $output->writeln(Json::encode($temp));
            }
        }
    }


}