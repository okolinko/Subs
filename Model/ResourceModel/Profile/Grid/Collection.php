<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile\Grid;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult {

    protected $_fieldMap = [
        'profile_id' => 'main_table.profile_id',
        'status' => 'main_table.status',
	    'customer_id' => 'main_table.customer_id',
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

    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        $mainTable = 'subscriptions_profiles',
        $resourceModel = 'Toppik\Subscriptions\Model\ResourceModel\Profile'
    ) {
		$this->reportHelper = $reportHelper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    public function addFieldToFilter($field, $condition = null) {
        $lifetime = ($this->reportHelper->getMaxLifetimePoints() > 0) ? $this->reportHelper->getMaxLifetimePoints() : 0;

        if($field === 'customer_name') {
            parent::addFieldToFilter([
                'first_name' => 'customer_entity.firstname',
                'last_name' => 'customer_entity.lastname',
            ], [
                'first_name' => $condition,
                'last_name' => $condition,
            ]);
            return $this;
        } else if($field === 'lifetime_save_points_used') {
            parent::addFieldToFilter(new \Zend_Db_Expr('(IF(ISNULL(save_points.lifetime_save_points_used), 0, save_points.lifetime_save_points_used))'), $condition);
            return $this;
        } else if($field === 'total_save_points_available') {
            if($lifetime > 0) {
                parent::addFieldToFilter(
                    new \Zend_Db_Expr(
                        sprintf('(%s - (IF(ISNULL(save_points.lifetime_save_points_used), 0, save_points.lifetime_save_points_used)))', $lifetime)
                    ),
                    $condition
                );
            }

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

        $lifetime = ($this->reportHelper->getMaxLifetimePoints() > 0) ? $this->reportHelper->getMaxLifetimePoints() : 0;

        $this->getSelect()
            ->joinLeft(
                ['customer_entity' => $this->getTable('customer_entity')],
                'main_table.customer_id = customer_entity.entity_id',
                [
                    'IF(customer_entity.entity_id, CONCAT_WS(" ", customer_entity.firstname, customer_entity.lastname), "Guest") as customer_name',
                'customer_entity.email as customer_email'
                ]
            )
            ->joinLeft(
                ['sales_order' => $this->getTable('sales_order')],
                'main_table.last_order_id = sales_order.entity_id',
                ['sales_order.increment_id as order_increment_id', 'sales_order.grand_total as order_grand_total', 'sales_order.created_at as order_created_at']
            )
            ->joinLeft(
                [
                    'spo' =>  new \Zend_Db_Expr(
                    '(SELECT profile_id  as spo_profile_id ,count(order_id) as count_orders from subscriptions_profiles_orders
                    join sales_order as so on subscriptions_profiles_orders.order_id = so.entity_id
                    group by profile_id)'
                    )
                ],
                'spo.spo_profile_id = main_table.profile_id'
            )
            ->joinLeft(
                [
                    'save_points' =>  new \Zend_Db_Expr(sprintf('(SELECT profile_id AS save_points_profile_id, SUM(used_points) AS lifetime_save_points_used, (%s - SUM(used_points)) AS total_save_points_available FROM subscriptions_save GROUP BY profile_id)', $lifetime))
                ],
                'save_points.save_points_profile_id = main_table.profile_id',
                [
                    'lifetime_save_points_used' => new \Zend_Db_Expr('(IF(ISNULL(save_points.lifetime_save_points_used), 0, save_points.lifetime_save_points_used))'),
                    'total_save_points_available' => new \Zend_Db_Expr(sprintf('(%s - (IF(ISNULL(save_points.lifetime_save_points_used), 0, save_points.lifetime_save_points_used)))', $lifetime))
                ]
            )
            ->columns([
                'count_orders' => 'spo.count_orders',
                'lifetime_value' => new \Zend_Db_Expr('main_table.grand_total*spo.count_orders')
            ]);

            $this->addFilterToMap('store_id', 'main_table.store_id');

        return $this;
    }

}
