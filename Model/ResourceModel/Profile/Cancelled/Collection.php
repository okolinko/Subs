<?php

namespace Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Toppik\Subscriptions\Model\Profile\Cancelled', 'Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled');
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
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
                ['customer_entity' => $this->getTable('customer_entity')],
                'main_table.profile_id = customer_entity.entity_id',
                [
                    'IF(customer_entity.entity_id, CONCAT_WS(" ", customer_entity.firstname, customer_entity.lastname), "Guest") as customer_name',
                    'customer_entity.email AS customer_email'
                ]
            )
            ->columns([
                'ip_converted' => 'INET_NTOA(main_table.ip)'
            ]);
        return $this;
    }

}
