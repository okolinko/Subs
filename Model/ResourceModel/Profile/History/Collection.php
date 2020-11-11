<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\Profile\History', 'Toppik\Subscriptions\Model\ResourceModel\Profile\History');
    }
    
    public function setProfileFilter($profile_id) {
        $this->addFieldToFilter('profile_id', $profile_id);
        return $this;
    }
	
    /**
     * @return $this
     */
    protected function _initSelect() {
        parent::_initSelect();
        
        $this->getSelect()
            ->columns([
                'ip_converted' => 'INET_NTOA(main_table.ip)'
            ]);
        return $this;
    }
    
}
