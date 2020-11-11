<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 6:22 PM
 */

namespace Toppik\Subscriptions\Model\ResourceModel\Settings;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Period extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('subscriptions_periods', 'period_id');
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function _prepareTableValueForSave($value, $type)
    {
        if(is_array($value)) {
            return implode(',', $value);
        } else {
            return parent::_prepareTableValueForSave($value, $type);
        }
    }

}