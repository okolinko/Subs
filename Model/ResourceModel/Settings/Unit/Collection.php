<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 6:28 PM
 */

namespace Toppik\Subscriptions\Model\ResourceModel\Settings\Unit;


use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{

    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Collection constructor.
     * @param Registry $registry
     * @param ObjectManagerInterface $objectManager
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        Registry $registry,
        ObjectManagerInterface $objectManager,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->registry = $registry;
        $this->objectManager = $objectManager;
    }

    /**
     * @var string
     */
    protected $_idFieldName = 'unit_id';

    protected function _construct()
    {
        $this->_init('Toppik\Subscriptions\Model\Settings\Unit', 'Toppik\Subscriptions\Model\ResourceModel\Settings\Unit');
    }

    public function getOptionArray() {
        if(! $this->registry->registry('unit_option_array')) {
            $units = [];
            $unitCollection = $this->objectManager->create(__CLASS__);
            foreach($unitCollection as $unit) {
                /** @var \Toppik\Subscriptions\Model\Settings\Unit $unit */
                $units[$unit->getId()] = $unit->getTitle();
            }
            $this->registry->register('unit_option_array', $units);
        }
        return $this->registry->registry('unit_option_array');
    }

}