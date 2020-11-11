<?php
namespace Toppik\Subscriptions\Plugin\Model\ResourceModel\Settings\Subscription\Grid;

class Collection {
	
    public function afterSetMainTable(\Toppik\Subscriptions\Model\ResourceModel\Settings\Subscription\Grid\Collection $collection) {
        $collection->addFilterToMap('product_sku', 'ce.sku');
    }
	
}
