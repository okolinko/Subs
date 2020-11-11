<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile\Save;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_fieldMap = [
        'admin_email' => 'admin.email',
        'option_title' => 'options.title',
        'option_points' => 'options.points'
    ];
    
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\Profile\Save', 'Toppik\Subscriptions\Model\ResourceModel\Profile\Save');
    }
    
    public function addFieldToFilter($field, $condition = null) {
        if($field === 'ip_converted') {
            $field = new \Zend_Db_Expr('INET_NTOA(main_table.ip)');
        } else if(isset($this->_fieldMap[$field])) {
            $field = $this->_fieldMap[$field];
        }
        
        return parent::addFieldToFilter($field, $condition);
    }
    
    /**
     * @return $this
     */
    protected function _initSelect() {
        parent::_initSelect();
        
        $this->getSelect()
            ->joinLeft(
                ['options' => \Toppik\Subscriptions\Model\ResourceModel\Profile\Points::MAIN_TABLE],
                "(main_table.option_id = options.id)",
                [
                    'options.title AS option_title',
                    'options.points AS option_points'
                ]
            )
            ->joinLeft(
                ['admin' => 'admin_user'],
                "(main_table.admin_id = admin.user_id)",
                [
                    'admin.email AS admin_email'
                ]
            )
            ->columns([
                'ip_converted' => 'INET_NTOA(main_table.ip)'
            ]);
        return $this;
    }
    
    public function setProfileFilter($profile_id) {
        $this->addFieldToFilter('profile_id', $profile_id);
        return $this;
    }
	
}
