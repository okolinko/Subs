<?php
namespace Toppik\Subscriptions\Block\Customer\Account\View;

class Schedule extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
    */
    protected $_timezoneInterface;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        array $data = []
    ) {
		$this->registry = $registry;
		$this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context, $data);
    }
    
    public function getProfile() {
		return $this->registry->registry('current_profile');
    }
    
    public function getInfoBoxTitle() {
        return __('Profile Schedule');
    }
    
    public function getInfoBoxFields() {
        $profile = $this->getProfile();
        
		$fields = array(
            array(
                'title' => __('Start Date:'),
                'value' => $this->_timezoneInterface->date(new \DateTime($this->getProfile()->getStartDate()))->format('M d, Y')
            ),
            
			array(
				'title' => __('Shipping Frequency:'),
				'value' => $this->getProfile()->getFrequencyTitle() . ($profile->canEditFrequency() ? ' <a href="' . $this->getUrl('subscriptions/customer/frequency', array('id' => $profile->getId())).'"><u>(' . __('edit') . ')</u></a>' : '')
			)
		);
		
		if(
            (
                $this->getProfile()->getStatus() == \Toppik\Subscriptions\Model\Profile::STATUS_ACTIVE
                || $this->getProfile()->getStatus() == \Toppik\Subscriptions\Model\Profile::STATUS_SUSPENDED_TEMPORARILY
            )
            && $this->getProfile()->getNextOrderAt()
        ) {
            $fields[] = array(
                'title' => __('Next Order Date:'),
                'value' => $this->_timezoneInterface->date(new \DateTime($this->getProfile()->getNextOrderAt()))->format('M d, Y') . ($profile->canEditNextDate() ? ' <a href="javascript:void()" class="js-show-popup-next-date"><u>(' . __('edit') . ')</u></a>' : '')
            );
		}
		
		return $fields;
    }
    
}
