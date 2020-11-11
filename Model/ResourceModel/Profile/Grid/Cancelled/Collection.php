<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile\Grid\Cancelled;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Event\ManagerInterface as EventManager;

class Collection extends SearchResult {
    
    protected $_fieldMap = [
        'store_id' => 'p.store_id',
        'status' => 'main_table.status',
        'created_at' => 'main_table.created_at',
        'updated_at' => 'main_table.updated_at',
        'grand_total' => 'main_table.grand_total',
        'order_grand_total' => 'sales_order.grand_total',
        'order_created_at' => 'sales_order.created_at',
        'order_increment_id' => 'sales_order.increment_id',
        'frequency_title' => 'main_table.frequency_length',
        'merchant_source' => 'main_table.merchant_source',
        'admin_id' => 'main_table.admin_id'
    ];
    
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'subscriptions_profiles_cancelled',
        $resourceModel = 'Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    
    public function addFieldToFilter($field, $condition = null) {
        if($field === 'customer_name') {
            parent::addFieldToFilter([
                'first_name' => 'customer_entity.firstname',
                'last_name' => 'customer_entity.lastname',
            ], [
                'first_name' => $condition,
                'last_name' => $condition,
            ]);
            return $this;
        }
        
        if(isset($this->_fieldMap[$field])) {
            $field = $this->_fieldMap[$field];
        }
        
        return parent::addFieldToFilter($field, $condition);
    }
    
    /**
     * @return $this
     */
    protected function _initSelect() {
        parent::_initSelect();
        
        $this->getSelect()
            ->joinLeft(
                ['admin' => 'admin_user'],
                "(main_table.admin_id = admin.user_id)",
                [
                    'admin.email AS admin_email',
                    'IF(admin.user_id, CONCAT_WS(" ", admin.firstname, admin.lastname), "") AS admin_name'
                ]
            )
            ->joinLeft(
                ['p' => $this->getTable('subscriptions_profiles')],
                'main_table.profile_id = p.profile_id',
                [
                    'p.customer_id',
                    'p.sku',
                    'p.store_id'
                ]
            )
            ->joinLeft(
                ['customer_entity' => $this->getTable('customer_entity')],
                'p.customer_id = customer_entity.entity_id',
                [
                    'IF(customer_entity.entity_id, CONCAT_WS(" ", customer_entity.firstname, customer_entity.lastname), "Guest") as customer_name',
                    'customer_entity.email AS customer_email'
                ]
            )
            ->joinLeft(
                [
                    'orders' =>  new \Zend_Db_Expr(
                    '(SELECT profile_id, COUNT(order_id) AS order_count FROM subscriptions_profiles_orders GROUP BY profile_id)')
                ],
                'orders.profile_id = main_table.profile_id',
                [
                    'orders.order_count AS order_count'
                ]
            )
            ->joinLeft(
                ['options' => \Toppik\Subscriptions\Model\ResourceModel\Profile\Points::MAIN_TABLE],
                "(main_table.option_id = options.id)",
                [
                    'options.title AS option_title',
                    'options.points AS option_points'
                ]
            )
            ->columns([
                'ip_converted' => 'INET_NTOA(main_table.ip)'
            ]);
        return $this;
    }
    
}
