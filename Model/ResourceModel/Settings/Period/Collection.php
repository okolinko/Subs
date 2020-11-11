<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 6:28 PM
 */

namespace Toppik\Subscriptions\Model\ResourceModel\Settings\Period;


use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;

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

    public function __construct(
        Registry $registry,
        ObjectManagerInterface $objectManager,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->registry = $registry;
        $this->objectManager = $objectManager;
    }

    /**
     * @var string
     */
    protected $_idFieldName = 'period_id';

    protected function _construct()
    {
        $this->_init('Toppik\Subscriptions\Model\Settings\Period', 'Toppik\Subscriptions\Model\ResourceModel\Settings\Period');
    }

    public function getOptionArray() {
        if(! $this->registry->registry('period_option_array')) {
            $units = [];
            $periodCollection = $this->objectManager->create(__CLASS__);
            foreach($periodCollection as $period) {
                /** @var \Toppik\Subscriptions\Model\Settings\Period $period */
                $units[$period->getId()] = $period->getTitle();
            }
            $this->registry->register('period_option_array', $units);
        }
        return $this->registry->registry('period_option_array');
    }

    public function getPeriodByLength($length){
        $periodCollection = $this->objectManager->create(__CLASS__);
        $periodCollection->addFieldToFilter('length', (int)$length);

        return $periodCollection->getFirstItem();
    }

}