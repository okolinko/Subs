<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Sku;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
	
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
	
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\Sku', 'Toppik\Subscriptions\Model\ResourceModel\Sku');
    }
	
    public function setSkuFilter($id) {
        $this->addFieldToFilter('sku', $id);
        return $this;
    }
	
}
