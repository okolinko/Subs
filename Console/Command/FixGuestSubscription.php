<?php
namespace Toppik\Subscriptions\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\App\ResourceConnection;
use Toppik\Subscriptions\Model\Profile;
use Toppik\Subscriptions\Model\ProfileFactory;

class FixGuestSubscription extends Command
{
	
    /**
     * @var State
     */
    private $state;
	
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
	
    /**
     * @var ProfileFactory
     */
    private $profileFactory;
	
    /**
     * ProcessProfiles constructor.
     * @param ProfileFactory $profileFactory
     * @param ResourceConnection $resourceConnection
     * @param State $state
     */
    public function __construct(
        ProfileFactory $profileFactory,
        ResourceConnection $resourceConnection,
        State $state
    )
    {
        $this->state = $state;
        parent::__construct();
        $this->resourceConnection = $resourceConnection;
        $this->profileFactory = $profileFactory;
    }
	
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('subscriptions:fix:guest');
        $this->setDescription('Fix guest subscriptions');
        $this->setDefinition([
            new InputArgument(
                'simulate',
                InputArgument::OPTIONAL,
                'Specify this option if you do not want to change profiles'
            )
        ]);
        parent::configure();
    }
	
    protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('FixGuestSubscription start');
		
        $this->state->setAreaCode('frontend');
		
		$simulate = $input->getArgument('simulate');
		
        $connection = $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
		
		$data = $connection->fetchAll(
			'SELECT
				main_table.profile_id AS profile_id,
				main_table.customer_id AS profile_customer_id,
				c.entity_id AS customer_entity_id,
				o.entity_id AS order_entity_id,
				o.customer_email AS order_customer_email
			FROM `subscriptions_profiles` AS main_table
			LEFT JOIN customer_entity AS c ON main_table.customer_id = c.entity_id
			LEFT JOIN sales_order AS o ON main_table.last_order_id = o.entity_id
			WHERE c.entity_id IS NULL || c.entity_id < 1'
		);
		
		$output->writeln(sprintf('Found %s profiles without customers', count($data)));
		
		$index 	= array();
		$emails = array();
		
		foreach($data as $_item) {
			if(isset($_item['profile_id']) && isset($_item['order_customer_email']) && !empty($_item['order_customer_email'])) {
				$email = trim(strtolower($_item['order_customer_email']));
				$emails[] = sprintf('"%s"', $email);
				$index[$_item['profile_id']] = $email;
			}
		}
		
		$found = $connection->fetchAll(
			sprintf('
					SELECT entity_id, email FROM `customer_entity`
					WHERE email IN(%s)
				',
				implode(',', $emails)
			)
		);
		
		$output->writeln(sprintf('Found %s emails with customers', count($found)));
		
		foreach($found as $_item) {
			$entity_id 	= isset($_item['entity_id']) ? $_item['entity_id'] : null;
			$email 		= isset($_item['email']) ? trim(strtolower($_item['email'])) : null;
			
			if($entity_id && $email) {
				$profile_id = null;
				
				foreach($index as $_profile_id => $_email) {
					if($email == $_email) {
						$profile_id = $_profile_id;
						break;
					}
				}
				
				if(!$profile_id) {
					$output->writeln("Index for customer ID {$entity_id} and email {$email} does not exist!");
					continue;
				}
				
				$output->writeln("Processing profile ID {$profile_id} : customer ID {$entity_id} with email {$email}");
				
				$profile = $this->profileFactory->create();
				$profile->load($profile_id);
				
				if(!$profile->getId()) {
					$output->writeln("Profile with ID {$profile_id} does not exist!");
					continue;
				}
				
				if(!$simulate) {
					$profile->setCustomerId($entity_id);
					$profile->save();
				}
			}
		}
    }
	
}
