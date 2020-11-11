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
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\App\ResourceConnection;
use Toppik\Subscriptions\Model\Profile;
use Toppik\Subscriptions\Model\ProfileFactory;

class FixOrderSku extends Command
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
     * @var OrderFactory
     */
    private $orderFactory;
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
     * @param OrderFactory $orderFactory
     * @param ManagerInterface $eventManager
     * @param State $state
     */
    public function __construct(
        ProfileFactory $profileFactory,
        ResourceConnection $resourceConnection,
        OrderFactory $orderFactory,
        ManagerInterface $eventManager,
        State $state
    )
    {
        $this->eventManager = $eventManager;
        $this->state = $state;
        parent::__construct();
        $this->orderFactory = $orderFactory;
        $this->resourceConnection = $resourceConnection;
        $this->profileFactory = $profileFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('subscriptions:fix:ordersku');
        $this->setDescription('Fix Sku For Orders created from old subscriptions');
        $this->setDefinition([
            new InputArgument(
                'order_ids',
                InputArgument::REQUIRED,
                'List of order ids (comma-separated)'
            ),
            new InputArgument(
                'simulate',
                InputArgument::OPTIONAL,
                'Specify this option if you do not want to change orders'
            ),
        ]);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->state->setAreaCode('frontend');

        $orderIds = explode(',', $input->getArgument('order_ids'));
        $simulate = $input->getArgument('simulate');
        $connection = $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $subscriptionsProfilesOrders = $connection->getTableName('subscriptions_profiles_orders');

        foreach($orderIds as $incrementId) {
            $output->writeln("Processing order #{$incrementId}");
            /* @var \Magento\Sales\Model\Order $order */
            $order = $this->orderFactory->create();
            $order->loadByIncrementId($incrementId);
            if(! $order->getId()) {
                $output->writeln("Order #{$incrementId} not found");
                continue;
            }
            $output->writeln("Found order with entity_id {$order->getId()}");
            $profiles = [];
            $profileIds = $connection->fetchCol(
                'SELECT `po`.`profile_id`
                FROM `' . $subscriptionsProfilesOrders . '` as `po`
                WHERE `po`.`order_id` = :order_id GROUP BY `po`.`profile_id`',
                ['order_id' => $order->getId()]
            );
            if(empty($profileIds)) {
                $output->writeln("Profile for order #{$incrementId} not found");
                continue;
            }
            foreach($profileIds as $profileId) {
                /* @var Profile $profile */
                $profile = $this->profileFactory->create();
                $profile->load($profileId);
                if(! $profileId) {
                    $output->writeln("Profile for order #{$incrementId} not found");
                    continue 2;
                }
                $profiles[] = $profile;
            }
            $output->writeln("Found all order #{$incrementId} profiles");
            foreach($order->getAllVisibleItems() as $item) {
                /* @var Item $item */
                $sku = $item->getSku();
                $qty = (float) $item->getQtyOrdered();
                $name = $item->getName();
                $productId = $item->getProductId();
                $output->write("Found item#{$item->getId()} with product_id#{$productId} qty#{$qty}");
                $found = false;
                foreach($profiles as $profile) {
                    if($profile->getIsUsed()) {
                        continue;
                    }
                    $profileProductId = $profile->getItems()->getProductId();
                    $profileQty = $profile->getItems()->getQty();
                    $profileSku = $profile->getItems()->getSku();
                    $profileName = $profile->getItems()->getName();
                    if($productId == $profileProductId && $qty == $profileQty) {
                        $found = $profile;
                        $profile->setIsUsed(true);
                        break;
                    }
                }
                if(! $found) {
                    $output->writeln(" - Unable to find profile for order#{$incrementId} item#{$item->getId()}");
                    continue 2;
                } else {
                    $output->writeln(" - Found profile sku#{$profileSku} name#{$profileName}");
                }
                if($sku == $profileSku && $name == $profileName) {
                    $output->writeln("Item and profile have same sku#{$profileSku} and name #{$profileName}");
                    continue;
                }
                $output->writeln("Changing item#{$item->getId()} sku({$sku}=>{$profileSku}) and name({$name}=>{$profileName})");
                $item->setSku($profileSku);
                $item->setName($profileName);
                if(! $simulate) {
                    $item->save();
                }
            }
        }
    }


}