<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 3:27 PM
 */

namespace Toppik\Subscriptions\Ui\Component\Listing\Column;


use Magento\Store\Ui\Component\Listing\Column\Store as StoreBase;

class Store extends StoreBase
{

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        if(is_string($item[$this->storeKey])) {
            $item[$this->storeKey] = explode(',', $item[$this->storeKey]);
        }
        return parent::prepareItem($item);
    }

}