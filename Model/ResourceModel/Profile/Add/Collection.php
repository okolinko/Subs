<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile\Add;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_init(
            'Toppik\Subscriptions\Model\Profile\Add',
            'Toppik\Subscriptions\Model\ResourceModel\Profile\Add'
        );
        
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        
        $this->storeManager = $storeManager;
    }
    
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\Profile\Add', 'Toppik\Subscriptions\Model\ResourceModel\Profile\Add');
    }
    
}
