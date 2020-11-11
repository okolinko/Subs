<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 6:22 PM
 */

namespace Toppik\Subscriptions\Model\ResourceModel\Settings;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Unit extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('subscriptions_units', 'unit_id');
    }

}