<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/30/16
 * Time: 6:55 PM
 */

namespace Toppik\Subscriptions\Model\ResourceModel\Settings;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Subscription extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('subscriptions_subscriptions', 'subscription_id');
    }
}