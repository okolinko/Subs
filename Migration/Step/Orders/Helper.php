<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/12/16
 * Time: 7:32 PM
 */

namespace Toppik\Subscriptions\Migration\Step\Orders;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenManagement;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;

class Helper
{

    private $sourceLinkTable = 'aw_sarp2_profile_order';

    private $perPage = 1000;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var int
     */
    private $profileCount;
    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;
    /**
     * @var Destination
     */
    private $destination;

    /**
     * Helper constructor.
     * @param Source $source
     * @param PaymentTokenManagement $paymentTokenManagement
     * @param Destination $destination
     */
    public function __construct(
        Source $source,
        PaymentTokenManagement $paymentTokenManagement,
        Destination $destination
    )
    {
        $this->source = $source;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->destination = $destination;
    }

    /**
     * @return int
     */
    public function getTotalPages()
    {
        return ceil($this->getCount() / $this->perPage);
    }

    /**
     * @return int
     */
    public function getCount() {
        if(is_null($this->profileCount)) {
            /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $select = $adapter->getSelect();
            $select
                ->from($this->sourceLinkTable, 'COUNT(*) as cnt');
            $result = $adapter->loadDataFromSelect($select);
            return $result[0]['cnt'];
        }
        return $this->profileCount;
    }

    public function getLinks($pageNumber)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect();
        $select
            ->from($this->sourceLinkTable . ' as link', 'link.profile_id')
            ->joinInner(
                'sales_flat_order as order',
                'order.entity_id = link.order_id',
                [
                    'order.increment_id',
                ]
            )
            ->order(['link.profile_id asc', 'link.order_id asc'])
            ->limitPage($pageNumber, $this->perPage);
        $result = $adapter->loadDataFromSelect($select);
        return $result;
    }

    public function getIncrementIds($incrementIds)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $select = $adapter->getSelect();
        $select
            ->from('sales_order', ['entity_id', 'increment_id', ])
            ->where('`increment_id` in (?)', $incrementIds);
        $result = $adapter->loadDataFromSelect($select);
        $ret = [];
        foreach($result as $item) {
            $ret[$item['increment_id']] = $item['entity_id'];
        }
        return $ret;
    }

    public function insertLinks($data)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $adapter->insertRecords('subscriptions_profiles_orders', $data);
    }

}