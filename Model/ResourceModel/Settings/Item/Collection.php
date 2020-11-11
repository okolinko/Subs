<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/1/16
 * Time: 12:54 PM
 */

namespace Toppik\Subscriptions\Model\ResourceModel\Settings\Item;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    protected function _construct()
    {
        $this->_init('Toppik\Subscriptions\Model\Settings\Item', 'Toppik\Subscriptions\Model\ResourceModel\Settings\Item');
    }

}