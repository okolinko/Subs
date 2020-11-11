<?php
namespace Toppik\Subscriptions\Model\Settings;

class Points extends \Magento\Framework\Model\AbstractModel {
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
    
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;
    
    /**
     * @var \Toppik\Subscriptions\Model\ResourceModel\Profile\Points\CollectionFactory
     */
    private $collectionFactory;
    
    public function __construct(
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Toppik\Subscriptions\Model\ResourceModel\Profile\Points\CollectionFactory $collectionFactory
    ) {
		$this->reportHelper = $reportHelper;
        $this->authSession = $authSession;
        $this->collectionFactory = $collectionFactory;
    }
    
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray() {
        $options[] = ['label' => '-- N/A --', 'value' => '0'];
        
        $collection = $this->collectionFactory->create();
        
        $isAllowed  = false;
        $role_id    = $this->reportHelper->getSaveRoleId();
        $role       = $this->authSession->getUser()->getRole();
        
        if((int) $role->getId() === 2) {
            $isAllowed = true;
        }
        
        if($role_id && $role_id === (int) $role->getId()) {
            $isAllowed = true;
        }
        
        if($isAllowed !== true) {
            $collection->addFieldToFilter('manager', 0);
        }
        
        $collection->setOrder('position', 'asc');
        
        foreach($collection->getItems() as $_item) {
            $options[] = [
                'label' => $_item->getTitle(),
                'value' => $_item->getId()
            ];
        }
        
        return $options;
    }
    
}
