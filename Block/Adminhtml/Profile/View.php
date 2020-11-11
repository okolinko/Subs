<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Toppik\Subscriptions\Model\Settings\Period;
use Magento\Framework\Pricing\PriceCurrencyInterface as PriceHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Sales\Model\Order\Address as OrderAddress;

class View extends \Magento\Backend\Block\Widget\Container {
    
    /**
     * @var Registry
     */
    private $registry;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
    */
    protected $_timezoneInterface;
    
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceHelper;
    
    /**
     * @var AddressRenderer
     */
    private $addressRenderer;
    
    /**
     * @var OrderAddress
     */
    private $orderAddress;
    
    /**
     * Group service
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
    
    /**
     * View constructor.
     * @param OrderAddress $orderAddress
     * @param AddressRenderer $addressRenderer
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceHelper
     * @param DateTime $dateTime
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        OrderAddress $orderAddress,
        AddressRenderer $addressRenderer,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceHelper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        DateTime $dateTime,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->priceHelper = $priceHelper;
        $this->groupRepository = $groupRepository;
		$this->reportHelper = $reportHelper;
        $this->registry = $registry;
        $this->dateTime = $dateTime;
		$this->_timezoneInterface = $timezoneInterface;
        $this->addressRenderer = $addressRenderer;
        $this->orderAddress = $orderAddress;
        parent::__construct($context, $data);
    }
    
    protected function _construct() {
        parent::_construct();
        $profile = $this->getProfile();
        $availableActions = $profile->getAvailableSubscriptionOperations($profile->getStatus());
        
        if($profile->canCancel()) {
            if($this->reportHelper->getIsSTSEnabled()) {
                $this->buttonList->add(
                    'save_the_sale',
                    [
                        'label' => __('Save The Sale / Cancel'),
                        'class' => 'button primary js-save_the_sale'
                    ]
                );
            } else {
                $url = $this->getUrl(
                    'subscriptions/profiles/cancel',
                    [
                        'profile_id' => $profile->getId()
                    ]
                );
                
                $this->buttonList->add(
                    'cancel',
                    [
                        'label' => __('Cancel'),
                        'class' => 'button',
                        'onclick' => "window.location.href = '{$url}'; return false;"
                    ]
                );
            }
        }
        
        if($profile->canUpdate() && $this->hasConfigurable($profile)) {
            $url = $this->getUrl(
                'subscriptions/profiles/product',
                [
                    'profile_id' => $profile->getId()
                ]
            );
            
            $this->buttonList->add(
                'product',
                [
                    'label' => __('Change Product'),
                    'onclick' => "window.open('{$url}', '_blank')"
                ]
            );
        }
        
        if($profile->canEditBillingAddress()) {
            $url = $this->getUrl(
                'toppikvault/customer/view',
                [
                    'profile_id' => $profile->getId(),
                    'h' => $profile->getPaymentTokenReference()->getPublicHash(),
                    'customer_id' => $profile->getCustomerId()
                ]
            );
            
            $this->buttonList->add(
                'change_billing_address',
                [
                    'label' => __('Change Billing Address'),
                    'class' => 'button',
                    'onclick' => "window.open('{$url}', '_blank')"
                ]
            );
        }
        
        if($profile->canEditShippingAddress()) {
            $url = $this->getUrl(
                'subscriptions/profiles/shipping',
                [
                    'profile_id' => $profile->getId()
                ]
            );
            
            $this->buttonList->add(
                'change_shipping_address',
                [
                    'label' => __('Change Shipping Address'),
                    'class' => 'button',
                    'onclick' => "window.open('{$url}', '_blank')"
                ]
            );
        }
        
        foreach($availableActions as $action) {
            if($action == \Toppik\Subscriptions\Model\Profile::ACTION_CANCEL || $action == \Toppik\Subscriptions\Model\Profile::ACTION_UPDATE) {
                continue;
            }
            
            $title = $profile->getActionTitle($action);
            $url = $this->getUrl('subscriptions/profiles/' . $action, ['profile_id' => $profile->getId()]);
            
            $this->buttonList->add(
                $action,
                [
                    'label' => $title,
                    'class' => 'button',
                    'onclick' => ($action == \Toppik\Subscriptions\Model\Profile::ACTION_UPDATE ? "window.open('{$url}', '_blank')" : "window.location.href = '{$url}'; return false;")
                ]
            );
        }
    }
    
    /**
     * @return Profile
     */
    public function getProfile() {
        if(!$this->hasData('profile')) {
            $this->setData('profile', $this->registry->registry('profile'));
        }
        
        return $this->getData('profile');
    }
    
    public function canShowPoints() {
        if(
            (
                $this->getProfile()->getStatus() == \Toppik\Subscriptions\Model\Profile::STATUS_ACTIVE
                || $this->getProfile()->getStatus() == \Toppik\Subscriptions\Model\Profile::STATUS_SUSPENDED_TEMPORARILY
            )
            &&
            (
                $this->getAvailableOnetimePointsNumber() === -1
                || $this->getAvailableOnetimePointsNumber() > 0
            )
        ) {
            return true;
        }
        
        return false;
    }
    
    public function getMaxOnetimePoints() {
        return ($this->reportHelper->getMaxOnetimePoints() > 0) ? $this->reportHelper->getMaxOnetimePoints() : __('Unlimited');
    }
    
    public function getMaxLifetimePoints() {
        $onetime = ($this->reportHelper->getMaxOnetimePoints() > 0) ? $this->reportHelper->getMaxOnetimePoints() : 0;
        $lifetime = ($this->reportHelper->getMaxLifetimePoints() > 0) ? $this->reportHelper->getMaxLifetimePoints() : 0;
        $calls = ($lifetime > 0 && $onetime > 0) ? __('%1 %2', ($lifetime / $onetime), (($lifetime / $onetime > 1) ? __('calls') : __('call'))) : '';
        
        return ($lifetime > 0) ? __('%1', $calls) : __('Unlimited');
    }
    
    public function getLifetimeUsedPoints() {
        $onetime = ($this->reportHelper->getMaxOnetimePoints() > 0) ? $this->reportHelper->getMaxOnetimePoints() : 0;
        $lifetime_used = $this->getProfile()->getLifetimeUsedPoints();
        $calls = ($lifetime_used > 0 && $onetime > 0) ? __('%1 %2', ($lifetime_used / $onetime), (($lifetime_used / $onetime > 1) ? __('calls') : __('call'))) : '';
        
        return ($lifetime_used > 0) ? __('%1', $calls) : 0;
    }
    
    public function getAvailableOnetimePoints() {
        $points = $this->getProfile()->getAvailableOnetimePoints();
        return $points === -1 ? __('Unlimited') : $points;
    }
    
    public function getAvailableOnetimePointsNumber() {
        return $this->getProfile()->getAvailableOnetimePoints();
    }
    
    /**
     * @return Phrase
     */
    public function getStatus() {
        $profile = $this->getProfile();
        $availableStatuses = $profile->getAvailableStatuses();
        $status = $profile->getStatus();
        
        if(isset($availableStatuses[$status])) {
            return $availableStatuses[$status];
        } else {
            return __('Unknown');
        }
    }
    
    /**
     * @return Phrase
     */
    public function getPaymentMethod() {
        $paymentMethodCode = $this->getProfile()->getEngineCode();
        return __($paymentMethodCode);
    }
    
    /**
     * @return string
     */
    public function hasConfigurable($profile) {
        foreach($profile->getAllVisibleItems() as $_item) {
            if($_item->getProductType() == 'configurable') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @return string
     */
    public function getProfileName($profile) {
        $value = array();
        
        foreach($profile->getAllVisibleItems() as $_item) {
            if((int) $_item->getIsOnetimeGift() !== 1) {
                $value[] = $_item->getName();
            }
        }
        
        return implode(', ', $value);
    }
    
    /**
     * @return string
     */
    public function getStartDate() {
        return $this->dateTime->date('F j, Y', $this->getProfile()->getStartDate());
    }
    
    /**
     * @return string
     */
    public function getBillingPeriod() {
        $text = $this->getProfile()->getFrequencyTitle() . ' cycle. ';
        
        if($this->getProfile()->getIsInfinite() == \Toppik\Subscriptions\Model\Settings\Period::INFINITE) {
            $text .= 'Repeat until suspended or cancelled.';
        } else {
            $text .= 'Repeat ' . $this->getProfile()->getNumberOfOccurrences() . ' time(s).';
        }
        
        return $text;
    }
    
    /**
     * @return string|Phrase
     */
    public function getNextOrderDate() {
        if(
            (
                $this->getProfile()->getStatus() == \Toppik\Subscriptions\Model\Profile::STATUS_ACTIVE
                || $this->getProfile()->getStatus() == \Toppik\Subscriptions\Model\Profile::STATUS_SUSPENDED_TEMPORARILY
            )
            && $this->getProfile()->getNextOrderAt()
        ) {
            return $this->_timezoneInterface->date(new \DateTime($this->getProfile()->getNextOrderAt()))->format('M d, Y');
        }
        
        return '';
    }
    
    /**
     * @return string|Phrase
     */
    public function getNextOrderDateType() {
        $profile = $this->getProfile();
        
        if($profile->getNextOrderAtType() == \Toppik\Subscriptions\Model\Profile::TYPE_NEXT_DATE_AUTOMATIC) {
            return __('Automatic');
        } else if($profile->getNextOrderAtType() == \Toppik\Subscriptions\Model\Profile::TYPE_NEXT_DATE_MANUAL) {
            return __('Manual');
        } else {
            return __('Unknown');
        }
    }
    
    /**
     * Return name of the customer group.
     *
     * @return string
     */
    public function getCustomerGroupName() {
        $customerGroupId = $this->getProfile()->getCustomer()->getGroupId();
        
        try {
            if($customerGroupId !== null) {
                return $this->groupRepository->getById($customerGroupId)->getCode();
            }
        } catch(NoSuchEntityException $e) {
            return '';
        }
        
        return '';
    }
    
    /**
     * @return string
     */
    public function getBillingAddress() {
        $this->orderAddress->setData($this->getProfile()->getBillingAddress()->getData());
        return $this->addressRenderer->format($this->orderAddress, 'html');
    }
    
    /**
     * @return string
     */
    public function getShippingAddress() {
        $this->orderAddress->setData($this->getProfile()->getShippingAddress()->getData());
        return $this->addressRenderer->format($this->orderAddress, 'html');
    }
    
    /**
     * Retrieve url for loading blocks
     *
     * @return string
     */
    public function getLoadBlockUrl() {
        return $this->getUrl('*/*/loadBlock');
    }
    
}
