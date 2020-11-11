<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/12/16
 * Time: 7:32 PM
 */

namespace Toppik\Subscriptions\Migration\Step\Profiles;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenManagement;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;

class Helper
{

    private $sourceProfileTable = 'aw_sarp2_profile';

    private $sourceSubscriptionType = 'aw_sarp2_subscription_type';

    private $perPage = 5000;

    private $statusesToImport = ['active', 'suspended', ];

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
     * @param string $token
     * @param string $paymentMethodCode
     * @param int $customerId
     * @return PaymentTokenInterface|null
     */
    public function getPaymentTokenByGatewayToken($token, $paymentMethodCode, $customerId) {
        return $this->paymentTokenManagement->getByGatewayToken($token, $paymentMethodCode, $customerId);
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
                ->from($this->sourceProfileTable, 'COUNT(*) as cnt');
//                ->where('`status` in ("' . implode('", "', $this->statusesToImport) . '")');
            $result = $adapter->loadDataFromSelect($select);
            return $result[0]['cnt'];
        }
        return $this->profileCount;
    }

    /**
     * @param int $pageNumber
     * @return array
     */
    public function getProfiles($pageNumber)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect();
        $select
            ->from($this->sourceProfileTable . ' as profile', [
                'profile.*',
            ])
            ->joinInner(
                $this->sourceSubscriptionType . ' as type',
                'profile.subscription_type_id = type.entity_id',
                [
                    'type.period_length',
                    'type.period_unit',
                    'type.period_is_infinite',
                    'type.period_number_of_occurrences',
                ]
            )
            ->joinLeft(
                'sales_flat_order as order',
                'profile.last_order_id = order.entity_id',
                [
                    'order.increment_id',
                ]
            )
            ->order('profile.entity_id asc')
            ->limitPage($pageNumber, $this->perPage);
        return $adapter->loadDataFromSelect($select);
    }

    /**
     * @param int $increment_id
     * @return int
     */
    public function getOrderIdByIncrementId($increment_id)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $select = $adapter->getSelect();
        $select
            ->from('sales_order', 'entity_id')
            ->where('`increment_id` = ?', $increment_id);
        $result = $adapter->loadDataFromSelect($select);
        return isset($result[0]['entity_id']) ? $result[0]['entity_id'] : null;
    }

}