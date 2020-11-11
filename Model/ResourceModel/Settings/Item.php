<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/1/16
 * Time: 12:54 PM
 */

namespace Toppik\Subscriptions\Model\ResourceModel\Settings;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('subscriptions_items', 'item_id');
    }
}