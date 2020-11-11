<?php
namespace Toppik\Subscriptions\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Toppik\Subscriptions\Api\Data\ProfileInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Toppik\Subscriptions\Model\Settings\Period;
use Zend\Json\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenManagement;

class Profile extends AbstractModel implements ProfileInterface, IdentityInterface {
    
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'subscriptions_profile';

    /**
     * @var string
     */
    protected $_cacheTag = 'subscriptions_profile';

    protected $_eventPrefix = 'subscriptions_profile';
    
	protected $_payment_processors = array(
        'braintree_cc_vault' => 'braintree'
    );
    
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var ResourceModel\Profile
     */
    private $profileResourceModel;
	
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
	
    /**
     * @var Data
     */
    protected $_subscriptionHelper;
	
    /**
     * @var PaymentToken
     */
    private $paymentToken;
	
    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;
	
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    
    /**
     * Quote addresses collection
     *
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected $_addresses;
    
    /**
     * Quote items collection
     *
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected $_items;
    
    /**
     * @var \Toppik\Subscriptions\Model\Profile\ItemFactory
     */
    protected $_profileItemFactory;
    
    /**
     * @var \Toppik\Subscriptions\Model\Profile\AddressFactory
     */
    protected $_profileAddressFactory;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
    
    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeAdapterFactory
     */
    private $_braintreeAdapterFactory;
    
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;
    
    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 101.1.0
     */
    protected $serializer;
    
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        ResourceModel\Profile $profileResourceModel,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        \Toppik\Subscriptions\Model\Profile\AddressFactory $profileAddressFactory,
        \Toppik\Subscriptions\Model\Profile\ItemFactory $profileItemFactory,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Braintree\Model\Adapter\BraintreeAdapterFactory $braintreeAdapterFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        PaymentToken $paymentToken,
        PaymentTokenManagement $paymentTokenManagement,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        DateTime $dateTime,
        Context $context,
        Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->dateTime = $dateTime;
        $this->profileResourceModel = $profileResourceModel;
        $this->_customerSession = $customerSession;
        $this->eventManager = $eventManager;
		$this->_subscriptionHelper = $subscriptionHelper;
        $this->_profileItemFactory = $profileItemFactory;
        $this->_profileAddressFactory = $profileAddressFactory;
		$this->reportHelper = $reportHelper;
        $this->_braintreeAdapterFactory = $braintreeAdapterFactory;
        $this->paymentToken = $paymentToken;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->_objectManager = $objectManager;
	    $this->_storeManager = $storeManager;
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Profile');
    }
    
    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    
    /**
     * @return integer|null
     */
    public function getCustomerId() {
        return $this->getData(self::CUSTOMER_ID);
    }
    
    /**
     * @return integer|null
     */
    public function getPaymentTokenId() {
        return $this->getData(self::PAYMENT_TOKEN_ID);
    }
    
    /**
     * @return float|null
     */
    public function getGrandTotal() {
        return $this->getData(self::GRAND_TOTAL);
    }
    
    /**
     * @return float|null
     */
    public function getBaseGrandTotal() {
        return $this->getData(self::BASE_GRAND_TOTAL);
    }
    
    /**
     * @return string|null
     */
    public function getCreatedAt() {
        return $this->getData(self::CREATED_AT);
    }
    
    /**
     * @return string|null
     */
    public function getUpdatedAt() {
        return $this->getData(self::UPDATED_AT);
    }
    
    /**
     * @return string|null
     */
    public function getResumeAt() {
        return $this->getData(self::RESUME_AT);
    }
    
    /**
     * @return string|null
     */
    public function getStartDate() {
        return $this->getData(self::START_DATE);
    }
    
    /**
     * @return integer|null
     */
    public function getLastOrderId() {
        return $this->getData(self::LAST_ORDER_ID);
    }
    
    /**
     * @return string|null
     */
    public function getLastOrderAt() {
        return $this->getData(self::LAST_ORDER_AT);
    }
    
    /**
     * @return string|null
     */
    public function getNextOrderAtType() {
        return $this->getData(self::NEXT_ORDER_AT_TYPE);
    }
    
    /**
     * @return string|null
     */
    public function getNextOrderAt() {
        return $this->getData(self::NEXT_ORDER_AT);
    }
    
    /**
     * @return string|null
     */
    public function getNextOrderAtOriginal() {
        return $this->getData(self::NEXT_ORDER_AT_ORIGINAL);
    }
    
    /**
     * @return string|null
     */
    public function getStatus() {
        return $this->getData(self::STATUS);
    }
    
    /**
     * @return string|null
     */
    public function getLastSuspendError() {
        return $this->getData(self::LAST_SUSPEND_ERROR);
    }
    
    /**
     * @return string|null
     */
    public function getCurrencyCode() {
        return $this->getData(self::CURRENCY_CODE);
    }
    
    /**
     * @return string|null
     */
    public function getSku() {
        return $this->getData(self::SKU);
    }
    
    /**
     * @return int|null
     */
    public function getFrequencyLength() {
        return $this->getData(self::FREQUENCY_LENGTH);
    }
    
    /**
     * @return string|null
     */
    public function getFrequencyTitle() {
        return $this->getData(self::FREQUENCY_TITLE);
    }
    
    /**
     * @return int|null
     */
    public function getItemsCount() {
        return $this->getData(self::ITEMS_COUNT);
    }
    
    /**
     * @return int|null
     */
    public function getItemsQty() {
        return $this->getData(self::ITEMS_QTY);
    }
    
    /**
     * @return int|null
     */
    public function getIsInfinite() {
        return $this->getData(self::IS_INFINITE);
    }
    
    /**
     * @return int|null
     */
    public function getNumberOfOccurrences() {
        return $this->getData(self::NUMBER_OF_OCCURRENCES);
    }
    
    /**
     * @return string|null
     */
    public function getEngineCode() {
        return $this->getData(self::ENGINE_CODE);
    }
    
    /**
     * @return string|null
     */
    public function getSuspendCounter() {
        return (int) $this->getData(self::SUSPEND_COUNTER);
    }
    
    /**
     * @return string
     */
    public function getBillingAmount() {
        $value = 0;
        
        foreach($this->getAllVisibleItems() as $_item) {
            $value = $value + $_item->getRowTotal();
        }
        
        return $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency($value, true, false);
    }
    
    /**
     * @return string
     */
    public function getShippingAmount() {
        $value = $this->getShippingAddress()->getShippingAmount();
        return $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency($value, true, false);
    }
    
    /**
     * @return string
     */
    public function getTaxAmount() {
        $value = 0;
        
        foreach($this->getAllVisibleItems() as $_item) {
            $value = $value + $_item->getTaxAmount();
        }
        
        return $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency($value, true, false);
    }
    
    /**
     * @return object
     */
    public function getCustomer() {
        if(!$this->hasData('customer')) {
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($this->getCustomerId());
            $this->setData('customer', $customer);
        }
		
        return $this->getData('customer');
    }
    
    public function getSubscriptionProduct() {
        if(!$this->getData('subscription_product')) {
            foreach($this->getAllVisibleItems() as $_item) {
                if((int) $_item->getData('is_onetime_gift') !== 1) {
                    $this->setData(
                        'subscription_product',
                        $this->_objectManager->create('Magento\Catalog\Model\Product')->setStoreId($this->getStoreId())->load($_item->getProductId())
                    );
                    
                    break;
                }
            }
        }
        
        return $this->getData('subscription_product');
    }
    
    /**
     * @return integer|null
     */
    public function getPaymentTokenReference() {
        if(!$this->hasData('payment_token_reference')) {
			$token = $this->paymentToken->load($this->getPaymentTokenId());
            $this->setData('payment_token_reference', new DataObject(($token ? $token->getData() : array())));
        }
		
        return $this->getData('payment_token_reference');
    }
    
    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId) {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }
    
    /**
     * @param int $paymentTokenId
     * @return $this
     */
    public function setPaymentTokenId($paymentTokenId) {
        return $this->setData(self::PAYMENT_TOKEN_ID, $paymentTokenId);
    }
    
    /**
     * @param float $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal) {
        return $this->setData(self::GRAND_TOTAL, $grandTotal);
    }
    
    /**
     * @param float $baseGrandTotal
     * @return $this
     */
    public function setBaseGrandTotal($baseGrandTotal) {
        return $this->setData(self::BASE_GRAND_TOTAL, $baseGrandTotal);
    }
    
    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt) {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
    
    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt) {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
    
    /**
     * @param string|null $resumeAt
     * @return $this
     */
    public function setResumeAt($resumeAt) {
        return $this->setData(self::RESUME_AT, $resumeAt);
    }
    
    /**
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate) {
        return $this->setData(self::START_DATE, $startDate);
    }
    
    /**
     * @param int $lastOrderId
     * @return $this
     */
    public function setLastOrderId($lastOrderId) {
        return $this->setData(self::LAST_ORDER_ID, $lastOrderId);
    }
    
    /**
     * @param string $lastOrderAt
     * @return $this
     */
    public function setLastOrderAt($lastOrderAt) {
        return $this->setData(self::LAST_ORDER_AT, $lastOrderAt);
    }
    
    /**
     * @param string $value
     * @return $this
     */
    public function setNextOrderAtType($value) {
        return $this->setData(self::NEXT_ORDER_AT_TYPE, $value);
    }
    
    /**
     * @param string $value
     * @return $this
     */
    public function setNextOrderAt($value) {
        return $this->setData(self::NEXT_ORDER_AT, $value);
    }
    
    /**
     * @param string $value
     * @return $this
     */
    public function setNextOrderAtOriginal($value) {
        return $this->setData(self::NEXT_ORDER_AT_ORIGINAL, $value);
    }
    
    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status) {
        return $this->setData(self::STATUS, $status);
    }
    
    /**
     * @param string $lastSuspendError
     * @return $this
     */
    public function setLastSuspendError($lastSuspendError) {
        return $this->setData(self::LAST_SUSPEND_ERROR, $lastSuspendError);
    }
    
    /**
     * @param string $value
     * @return $this
     */
    public function setCurrencyCode($value) {
        return $this->setData(self::CURRENCY_CODE, $value);
    }
    
    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku) {
        return $this->setData(self::SKU, $sku);
    }
    
    /**
     * @param int $frequency_length
     * @return $this
     */
    public function setFrequencyLength($frequency_length) {
        return $this->setData(self::FREQUENCY_LENGTH, $frequency_length);
    }
    
    /**
     * @param string $frequency_title
     * @return $this
     */
    public function setFrequencyTitle($frequency_title) {
        return $this->setData(self::FREQUENCY_TITLE, $frequency_title);
    }
    
    /**
     * @param int $value
     * @return $this
     */
    public function setItemsCount($value) {
        return $this->setData(self::ITEMS_COUNT, $value);
    }
    
    /**
     * @param int $value
     * @return $this
     */
    public function setItemsQty($value) {
        return $this->setData(self::ITEMS_QTY, $value);
    }
    
    /**
     * @param int $value
     * @return $this
     */
    public function setIsInfinite($value) {
        return $this->setData(self::IS_INFINITE, $value);
    }
    
    /**
     * @param int $value
     * @return $this
     */
    public function setNumberOfOccurrences($value) {
        return $this->setData(self::NUMBER_OF_OCCURRENCES, $value);
    }
    
    /**
     * @param string $value
     * @return $this
     */
    public function setEngineCode($value) {
        return $this->setData(self::ENGINE_CODE, $value);
    }
    
    /**
     * @param string $value
     * @return $this
     */
    public function setSuspendCounter($value) {
        return $this->setData(self::SUSPEND_COUNTER, $value);
    }
    
    public function getAvailableStatuses() {
        return [
            self::STATUS_ACTIVE                 => __('Active'),
            self::STATUS_SUSPENDED              => __('Suspended'),
            self::STATUS_CANCELLED              => __('Cancelled'),
            self::STATUS_SUSPENDED_TEMPORARILY  => __('Suspended Temporarily'),
        ];
    }
    
    /**
     * @return $this
     */
    public function scheduleNextOrder($timestamp = null) {
        $limitReached = false;
        
        if($this->getIsInfinite() == \Toppik\Subscriptions\Model\Settings\Period::FINITE) {
            $maxOrders      = (int) $this->getNumberOfOccurrences();
            $ordersCreated  = $this->profileResourceModel->getNumberOfCreatedOrders($this);
            
            if($ordersCreated >= $maxOrders) {
                $limitReached = true;
            }
        }
        
        if($limitReached) {
            $this->setNextOrderAt(null);
            $this->changeStatusToCancel(__('Expired'));
        } else {
            $nextTimestamp = $this->dateTime->gmtTimestamp($timestamp) + $this->getFrequencyLength();
            $nextDatetime = date('Y-m-d H:i:s', $nextTimestamp);
            
            $this->setStatus(self::STATUS_ACTIVE);
            
            $this->setNextOrderAtType(self::TYPE_NEXT_DATE_AUTOMATIC);
            $this->setNextOrderAt($nextDatetime);
            $this->setNextOrderAtOriginal($nextDatetime);
            $this->setSuspendCounter(0);
        }
        
        return $this;
    }
    
    /**
     * @return $this
     */
    public function scheduleRetry($timestamp = null) {
        $limitReached = false;
        
        if($this->reportHelper->getTransactionDeclineTimeout() < 1) {
            $limitReached = true;
        }
        
        if($this->reportHelper->getMaxSuspendsAllowed() < 1) {
            $limitReached = true;
        }
        
        if($this->reportHelper->getMaxSuspendsAllowed() > 0) {
            if($this->getSuspendCounter() >= $this->reportHelper->getMaxSuspendsAllowed()) {
                $limitReached = true;
            }
        }
        
        if($limitReached === true) {
            $this->setNextOrderAt(null);
            $this->changeStatusToSuspend(__('%1: Expired By Retry', $this->getLastSuspendError()));
        } else {
            $nextTimestamp = $this->dateTime->gmtTimestamp() + ($this->reportHelper->getTransactionDeclineTimeout() * 60);
            $nextDatetime = date('Y-m-d H:i:s', $nextTimestamp);
            
            $this->setNextOrderAtType(self::TYPE_NEXT_DATE_AUTOMATIC);
            $this->setNextOrderAt($nextDatetime);
            $this->setStatus(self::STATUS_SUSPENDED_TEMPORARILY);
            $this->setSuspendCounter(($this->getSuspendCounter() + 1));
            
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * @param string $currentStatus
     * @return string[]
     */
    public function getAvailableSubscriptionOperations($currentStatus) {
        $actions = [];
        
        switch($currentStatus) {
            case self::STATUS_ACTIVE:
                $actions = [self::ACTION_CANCEL, self::ACTION_UPDATE];
                
                if($this->canEditQuantity()) {
                    $actions[] = self::ACTION_QTY;
                }
                
                if($this->canEditFrequency()) {
                    $actions[] = self::ACTION_FREQUENCY;
                }
                
                if($this->canEditCc()) {
                    $actions[] = self::ACTION_CC;
                }
                
                if($this->canEditNextDate()) {
                    $actions[] = self::ACTION_NEXTDATE;
                }
                
                break;
                
            case self::STATUS_SUSPENDED_TEMPORARILY:
                $actions = [self::ACTION_CANCEL, self::ACTION_UPDATE];
                
                if($this->canEditQuantity()) {
                    $actions[] = self::ACTION_QTY;
                }
                
                if($this->canEditFrequency()) {
                    $actions[] = self::ACTION_FREQUENCY;
                }
                
                if($this->canEditCc()) {
                    $actions[] = self::ACTION_CC;
                }
                
                if($this->canEditNextDate()) {
                    $actions[] = self::ACTION_NEXTDATE;
                }
                
                break;
                
            case self::STATUS_SUSPENDED:
                $actions = [self::ACTION_ACTIVATE, self::ACTION_CANCEL];
                break;
                
            case self::STATUS_CANCELLED:
                $actions = [];
                break;
        }
        
        return $actions;
    }
    
    /**
     * @param string $action
     * @return Phrase|string
     */
    public function getActionTitle($action) {
        switch($action) {
            case self::ACTION_UPDATE:
                return __('Update');
            case self::ACTION_ACTIVATE:
                return __('Activate');
            case self::ACTION_SUSPEND:
                return __('Suspend');
            case self::ACTION_CANCEL:
                return __('Cancel');
            case self::ACTION_FREQUENCY:
                return __('Change Frequency');
            case self::ACTION_QTY:
                return __('Change Quantity');
            case self::ACTION_CC:
                return __('Change Credit Card');
            case self::ACTION_NEXTDATE:
                return __('Change Next Order Date');
            default:
                return '';
        }
    }
    
    /**
     * @return bool
     */
    public function canActivate() {
        return in_array(self::ACTION_ACTIVATE, $this->getAvailableSubscriptionOperations($this->getStatus()));
    }
    
    /**
     * @return bool
     */
    public function canSuspend() {
        return in_array(self::ACTION_SUSPEND, $this->getAvailableSubscriptionOperations($this->getStatus()));
    }
    
    /**
     * @return bool
     */
    public function canSuspendAsCurrentUser() {
        return $this->canSuspend()
                && (
                    $this->_customerSession->getAdminId()
                    || ($this->authSession && $this->authSession->getUser() && $this->authSession->getUser()->getId())
                );
    }
	
    /**
     * @return bool
     */
    public function canCancel() {
        return in_array(self::ACTION_CANCEL, $this->getAvailableSubscriptionOperations($this->getStatus()));
    }
    
    /**
     * @return bool
     */
    public function canCancelAsCurrentUser() {
        return $this->canCancel()
                && (
                    $this->_subscriptionHelper->getIsCancelMode() === true
                    || $this->_customerSession->getAdminId()
                    || ($this->authSession->isLoggedIn())
                );
    }
	
    /**
     * @return bool
     */
    public function canUpdate() {
        return ($this->getStatus() == self::STATUS_ACTIVE) || ($this->getStatus() == self::STATUS_SUSPENDED_TEMPORARILY);
    }
    
    /**
     * @return bool
     */
    public function canEditBillingAddress() {
		return $this->canUpdate();
    }
    
    /**
     * @return bool
     */
    public function canEditShippingAddress() {
		return $this->canUpdate();
    }
    
    /**
     * @return bool
     */
    public function canEditNextDate() {
        if($this->reportHelper->getMaxSuspendsAllowed() > 0) {
            if($this->getSuspendCounter() >= $this->reportHelper->getMaxSuspendsAllowed()) {
                return false;
            }
        }
        
        return $this->canUpdate()
                && (
                    $this->_subscriptionHelper->getIsCustomerMode() === true
                    || $this->_customerSession->getAdminId()
                    || ($this->authSession->isLoggedIn())
                );
    }
	
    /**
     * @return bool
     */
    public function canEditQuantity() {
        return $this->canUpdate()
                && (
                    $this->_subscriptionHelper->getIsCustomerMode() === true
                    || $this->_customerSession->getAdminId()
                    || ($this->authSession->isLoggedIn())
                );
    }
    
    /**
     * @return bool
     */
    public function canEditQuantityOfItem($item) {
        return $this->canEditQuantity() && (int) $item->getIsOnetimeGift() !== 1;
    }
    
    /**
     * @return bool
     */
    public function canEditFrequency() {
        return $this->canUpdate()
                && (
                    $this->_subscriptionHelper->getIsCustomerMode() === true
                    || $this->_customerSession->getAdminId()
                    || ($this->authSession->isLoggedIn())
                );
    }
    
    /**
     * @return bool
     */
    public function canEditCc() {
        return $this->canUpdate()
                && (
                    $this->_subscriptionHelper->getIsCustomerMode() === true
                    || $this->_customerSession->getAdminId()
                    || ($this->authSession->isLoggedIn())
                );
    }
    
    /**
     * @return bool
     */
    public function canEditProduct() {
        return $this->canUpdate()
                && (
                    $this->_subscriptionHelper->getIsChangeMode() === true
                    || $this->_customerSession->getAdminId()
                    || ($this->authSession->isLoggedIn())
                );
    }
    
    /**
     * @return bool
     */
    public function canRemoveOneTimeProduct() {
        return $this->canUpdate()
                && (
                    $this->_subscriptionHelper->getIsRemoveMode() === true
                    || $this->_customerSession->getAdminId()
                    || ($this->authSession->isLoggedIn())
                );
    }
    
    public function changeProductPrice($item_id = null, float $price) {
        if($price > 0.001) {
            foreach($this->getAllVisibleItems() as $_item) {
                if($item_id === null) {
                    if((int) $_item->getData('is_onetime_gift') !== 1) {
                        $this->updateItemPrice($_item, $price);
                    }
                } else {
                    if((int) $item_id === $_item->getId()) {
                        $this->updateItemPrice($_item, $price);
                    }
                }
            }
            
            $this->updateProfile();
        }
    }
    
    /**
     * If sku changed - returns true
     * @param string $sourceSku
     * @param string $targetSku
     * @return bool
     */
    public function changeSku($sourceSku, $targetSku) {
        $productModel 	= $this->_objectManager->get('Magento\Catalog\Model\Product');
        $product 		= $productModel->loadByAttribute('sku', $targetSku);
        
        if(!$product || !$product->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Product # %1 does not exist', $targetSku));
        }
        
        $updated = false;
        
        $this->setSku($targetSku);
        
        foreach($this->getAllItems() as $_item) {
            if((int) $_item->getData('is_onetime_gift') !== 1) {
                $_item->setSku($targetSku);
                $updated = true;
            }
        }
        
        return $updated;
    }
    
    /**
     * Update profile
     * @param array $data
     * @return bool
     */
    public function changeQuantity($data) {
		$updated = false;
		
        if(is_array($data)) {
            foreach($data as $_item_id => $_qty) {
                $_qty = (int) $_qty;
                $item = $this->getItemsCollection()->getItemById($_item_id);
                
                if($item && $_qty > 0 && (int) $item->getQty() !== $_qty) {
                    $updated = true;
                    
                    $item->setData('tax_amount', ($item->getData('tax_amount') / $item->getQty() * $_qty));
                    $item->setData('base_tax_amount', ($item->getData('base_tax_amount') / $item->getQty() * $_qty));
                    $item->setData('row_total', (($item->getData('price') * $_qty) + $item->getData('discount_amount')));
                    $item->setData('base_row_total', (($item->getData('base_price') * $_qty) + $item->getData('base_discount_amount')));
                    $item->setData('row_total_incl_tax', (($item->getData('price') * $_qty) + $item->getData('discount_amount') + $item->getData('tax_amount')));
                    $item->setData('base_row_total_incl_tax', (($item->getData('base_price') * $_qty) + $item->getData('base_discount_amount') + $item->getData('base_tax_amount')));
                    
                    $item->setQty($_qty);
                    
                    if($item->getHasChildren()) {
                        foreach($item->getChildren() as $child) {
                            $child->setQty($_qty);
                        }
                    }
                }
            }
        }
        
        if($updated === true) {
            $this->updateProfile();
            $this->save();
        }
        
		return $updated;
    }
    
    /**
     * Update profile
     * @param int $id
     * @return bool
     */
    public function changeFrequency($id) {
		$updated = false;
		$id = (int) $id;
        
		if($id > 0) {
            $subscription = $this->_subscriptionHelper->getSubscriptionByProduct($this->getSubscriptionProduct());
            
            if($subscription && count($subscription->getItemsCollection())) {
                $subscriptionItem = false;
                
                foreach($subscription->getItemsCollection() as $_item) {
                    if((int) $_item->getId() === $id) {
                        $subscriptionItem = $_item;
                        break;
                    }
                }
                
                if($subscriptionItem && $subscriptionItem->getId()) {
                    $period         = $subscriptionItem->getPeriod();
                    $subscription 	= $this->_objectManager->get('Toppik\Subscriptions\Model\Settings\SubscriptionFactory')->create();
                    $unit 			= $this->_objectManager->get('Toppik\Subscriptions\Model\Settings\UnitFactory')->create();
                    
                    $subscription->load($subscriptionItem->getSubscriptionId());
                    $unit->load($period->getUnitId());
                    
                    if($subscription->getId() && $unit->getId()) {
                        $this->setFrequencyTitle($period->getLength() . ' ' . $unit->getTitle() . 's');
                        $this->setFrequencyLength($period->getLength() * $unit->getLength());
                        $this->setIsInfinite($period->getData(\Toppik\Subscriptions\Model\Settings\Period::IS_INFINITE));
                        $this->setNumberOfOccurrences($period->getData(\Toppik\Subscriptions\Model\Settings\Period::NUMBER_OF_OCCURRENCES));
                        $this->setEngineCode($period->getEngineCode());
                        
                        $this->scheduleNextOrder($this->getLastOrderAt() ? $this->getLastOrderAt() : null);
                        $this->save();
                        $updated = true;
                    }
                }
            }
		}
		
		return $updated;
    }
    
    /**
     * Update profile
     * @param string $token
     * @return bool
     */
    public function changeCc($token) {
		$updated = false;
		
		if(isset($token)) {
			$token 			= trim($token);
			$paymentToken 	= $this->paymentTokenManagement->getByGatewayToken($token, $this->getEngineCode(), $this->getCustomerId());
			
			if($paymentToken && $paymentToken->getId()) {
				if($this->getPaymentTokenId() !== $paymentToken->getId()) {
                    $this->_braintreeAdapterFactory->create($this->getStoreId());
                    
                    $method = \Braintree\PaymentMethod::find($paymentToken->getGatewayToken());
                    
                    if(isset($method->billingAddress) && isset($method->billingAddress->countryCodeAlpha2)) {
                        $messages   = array();
                        $billing    = $this->getBillingAddress();
                        
                        $region_id  = null;
                        
                        $regions    = $this->_objectManager->create('Magento\Directory\Model\RegionFactory')
                                                    ->create()
                                                    ->getResourceCollection()
                                                    ->addCountryFilter($method->billingAddress->countryCodeAlpha2)
                                                    ->load()
                                                    ->toOptionArray();
                        
                        foreach($regions as $_region) {
                            if($_region['label'] == $method->billingAddress->region) {
                                $region_id = $_region['value'];
                                break;
                            }
                        }
                        
                        $address    = array(
                            'firstname' => $method->billingAddress->firstName,
                            'lastname' => $method->billingAddress->lastName,
                            'street' => $method->billingAddress->streetAddress,
                            'city' => $method->billingAddress->locality,
                            'region' => $method->billingAddress->region,
                            'region_id' => $region_id,
                            'postcode' => $method->billingAddress->postalCode,
                            'country_id' => $method->billingAddress->countryCodeAlpha2
                        );
                        
                        foreach($address as $_key => $_value) {
                            if(is_array($_value)) {
                                $_value = trim(implode("\n", $_value));
                            }
                            
                            if(is_scalar($_value)) {
                                if($billing->hasData($_key) && $billing->getData($_key) != $_value) {
                                    $messages[] = __('Billing %1 changed from "%2" to "%3"', $_key, $billing->getData($_key), $_value);
                                    $billing->setData($_key, $_value);
                                    $updated = true;
                                }
                            }
                        }
                        
                        if($updated === true) {
                            if(count($messages)) {
                                $this->setStatusHistoryCode('address_change');
                                $this->setStatusHistoryNote(__('Credit Card Change'));
                                $this->setStatusHistoryMessage(implode(', ', $messages));
                            }
                        }
                    }
                    
					$this->setPaymentTokenId($paymentToken->getId());
					$this->save();
					$updated = true;
				}
			}
		}
		
		return $updated;
    }
    
    /**
     * Update profile
     * @param string 
     * @return bool
     */
    public function changeNextOrderDate($period, $storeId, $note) {
		$updated = false;
		
		if(isset($period)) {
            $period = strtotime($period);
            
			if($period === false) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid date'));
			}
			
            $dateTime = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime');
            
            if($dateTime->gmtTimestamp() >= $period) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid date'));
            }
            
            $timezone = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $date = new \DateTime(date("Y-m-d H:i:s", $period), new \DateTimeZone($timezone->getConfigTimezone()));
            $date = $timezone->date($date, null, false)->format('Y-m-d H:i:s');
            
			if(!$date) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid date'));
			}
            
            if($period > strtotime($this->getNextOrderAt())) {
                $this->setStatus(self::STATUS_SUSPENDED_TEMPORARILY);
                $this->setSuspendCounter(($this->getSuspendCounter() + 1));
            }
            
            $this->setNextOrderAtType(self::TYPE_NEXT_DATE_MANUAL);
            $this->setNextOrderAt($date);
            
            $this->setStatusHistoryCode('next_order_date_change');
            $this->setStatusHistoryNote($note);
            $this->setStatusHistoryMessage(
                __( 
                    'Next order date changed from "%1" to "%2"',
                    $this->getOrigData(self::NEXT_ORDER_AT),
                    $this->getNextOrderAt()
                )
            );
            
            $this->save();
            $updated = true;
            
            try {
                $title  = array();
                $sku    = array();
                
                foreach($this->getAllVisibleItems() as $_item) {
                    if((int) $_item->getData('is_onetime_gift') !== 1) {
                        $title[] = $_item->getName();
                        $sku[] = $_item->getSku();
                    }
                }
                
                $template = $this->reportHelper->getEmailTemplateChangeNextDate($storeId);
                
                $vars = array(
                    'profile'   => $this,
                    'customer'  => $this->getCustomer(),
                    'next_date' => date('m/d/Y', strtotime($this->getNextOrderAt())),
                    'sku'       => implode(', ', $sku),
                    'title'     => implode(', ', $title)
                );
                
                $this->reportHelper->sendEmail($template, $this->getCustomer()->getEmail(), $storeId, $vars);
            } catch(\Exception $e) {
                $message = sprintf('CANNOT send report on next_order_date_change for subscription ID %s: %s', $this->getId(), $e->getMessage());
                
                $this->eventManager->dispatch(
                    'toppikreport_system_add_message',
                    [
                        'entity_type' 	=> 'next_order_date_change',
                        'entity_id' 	=> $this->getId(),
                        'message' 		=> $message
                    ]
                );
                
                $this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
            }
		}
		
		return $updated;
    }
    
    public function changeStatusToActive($note = null) {
        try {
            $this->setErrorCode('');
			$this->setLastSuspendError(null);
            $this->setCancelledAt(null);
            $this->setSuspendedAt(null);
            $this->setResumeAt(null);
			$this->setStatus(self::STATUS_ACTIVE);
            $this->save();
        } catch(\Exception $e) {
			throw new \Exception('Unable activate subscription');
        }
    }
	
    public function changeStatusToSuspend($note = null, $code = null) {
        if($code === null || empty($code)) {
            $_note = strtolower($note);
            
            if(strpos($_note, 'fatal') !== false) {
                $code = \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_FATAL;
            } else if(strpos($_note, '\interceptor') !== false) {
                $code = \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_FATAL;
            } else if(strpos($_note, 'notice:') !== false) {
                $code = \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_FATAL;
            } else if(strpos($_note, 'sqlstate') !== false) {
                $code = \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_FATAL;
            } else if(strpos($_note, 'something went wrong') !== false) {
                $code = \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_FATAL;
            } else if(strpos($_note, 'transaction') !== false) {
                $code = \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_PAYMENT_TRANSACTION;
            } else if(strpos($_note, 'declined') !== false) {
                $code = \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_PAYMENT_TRANSACTION;
            }
        }
        
        try {
            $this->setErrorCode($code);
            $this->setLastSuspendError($note);
            $this->setCancelledAt(null);
            $this->setSuspendedAt(date('Y-m-d H:i:s'));
			$this->setStatus(self::STATUS_SUSPENDED);
            $this->save();
        } catch(\Exception $e) {
			throw new \Exception('Unable suspend subscription');
        }
    }
	
    public function changeStatusToCancel($message = null, $note = null) {
        try {
            $this->setErrorCode('');
            $this->setLastSuspendError($message);
            $this->setStatusHistoryNote($note);
            $this->setCancelledAt(date('Y-m-d H:i:s'));
            $this->setSuspendedAt(null);
			$this->setStatus(self::STATUS_CANCELLED);
            $this->save();
        } catch(\Exception $e) {
			throw new \Exception('Unable cancel subscription');
        }
    }
    
    public function updateProfile() {
        $grand_total        = 0;
        $base_ground_total  = 0;
        $items_count        = 0;
        $items_qty          = 0;
        
        foreach($this->getAllVisibleItems() as $_item) {
            if((int) $_item->getData('is_onetime_gift') !== 1) {
                $this->setSku($_item->getSku());
                
                $grand_total        = $grand_total + $_item->getData('row_total');
                $base_ground_total  = $base_ground_total + $_item->getData('base_row_total');
                $items_count        = $items_count + 1;
                $items_qty          = $items_qty + $_item->getQty();
            }
        }
        
        $this->setGrandTotal($grand_total);
        $this->setBaseGrandTotal($base_ground_total);
        $this->setItemsCount($items_count);
        $this->setItemsQty($items_qty);
        
        return $this;
    }
    
    public function updateItemPrice($item, $price) {
        $item->setPrice($price);
        $item->setBasePrice($price);
        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);
        
        $item->setTaxAmount(0);
        $item->setBaseTaxAmount(0);
        
        $item->setRowTotal($price * $item->getQty());
        $item->setBaseRowTotal($price * $item->getQty());
        
        $item->setPriceInclTax($price);
        $item->setBasePriceInclTax($price);
        $item->setRowTotalInclTax($price * $item->getQty());
        $item->setBaseRowTotalInclTax($price * $item->getQty());
    }
    
    public function addProduct($product, $price, $qty, $options, $is_gift) {
        $item       = null;
        $attributes = isset($options['super_attributes']) ? $options['super_attributes'] : null;
        
        if(!$product || !$product->getId()) {
            return null;
        }
        
        if($product->getTypeId() == 'configurable') {
            $configurable = $this->_objectManager->create('\Magento\ConfigurableProduct\Model\Product\Type\Configurable');
            $child = $configurable->getProductByAttributes($attributes, $product);
            
            if($child === null) {
                return null;
            }
            
            $item = $this->setItem(
                new \Magento\Framework\DataObject(
                    array(
                        'profile_id' => $this->getId(),
                        'product_id' => $product->getId(),
                        'store_id' => $this->getStoreId(),
                        'is_onetime_gift' => ($is_gift === true ? 1 : 0),
                        'product_type' => $product->getTypeId(),
                        'sku' => $child->getSku(),
                        'name' => ($is_gift === true ? sprintf('%s (%s)', $product->getName(), __('One-Time')) : $product->getName()),
                        'qty' => $qty,
                        'price' => $price,
                        'base_price' => $price,
                        'row_total' => ($qty * $price),
                        'base_row_total' => ($qty * $price),
                        'item_options' => serialize(
                            array(
                                array(
                                    'product_id'    => $product->getId(),
                                    'code'          => 'attributes',
                                    'value'         => $this->serializer->serialize($attributes)
                                )
                            )
                        )
                    )
                )
            );
            
            $child_item = $this->setItem(
                new \Magento\Framework\DataObject(
                    array(
                        'profile_id' => $this->getId(),
                        'product_id' => $child->getId(),
                        'store_id' => $this->getStoreId(),
                        'product_type' => $child->getTypeId(),
                        'sku' => $child->getSku(),
                        'name' => ($is_gift === true ? sprintf('%s (%s)', $child->getName(), __('One-Time')) : $child->getName()),
                        'qty' => $qty,
                        'price' => 0,
                        'base_price' => 0,
                        'row_total' => 0,
                        'base_row_total' => 0,
                        'is_onetime_gift' => ($is_gift === true ? 1 : 0)
                    )
                )
            );
            
            $child_item->setParentItem($item);
        } else {
            $item = $this->setItem(
                new \Magento\Framework\DataObject(
                    array(
                        'profile_id' => $this->getId(),
                        'product_id' => $product->getId(),
                        'store_id' => $this->getStoreId(),
                        'product_type' => $product->getTypeId(),
                        'sku' => $product->getSku(),
                        'name' => ($is_gift === true ? sprintf('%s (%s)', $product->getName(), __('One-Time')) : $product->getName()),
                        'qty' => $qty,
                        'price' => $price,
                        'base_price' => $price,
                        'row_total' => ($qty * $price),
                        'base_row_total' => ($qty * $price),
                        'is_onetime_gift' => ($is_gift === true ? 1 : 0)
                    )
                )
            );
        }
        
        return $item;
    }
    
    /**
     * Retrieve profile address collection
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getAddressesCollection() {
        if(null === $this->_addresses) {
            $this->_addresses = $this->_profileAddressFactory->create()->getCollection()->setProfileFilter($this->getId());
            
            if($this->getId()) {
                foreach($this->_addresses as $address) {
                    $address->setProfile($this);
                }
            }
        }
        
        return $this->_addresses;
    }
    
    /**
     * Get all profile addresses
     *
     * @return \Toppik\Subscriptions\Model\Profile\Address[]
     */
    public function getAllAddresses() {
        $addresses = [];
        
        foreach($this->getAddressesCollection() as $address) {
            if(!$address->isDeleted()) {
                $addresses[] = $address;
            }
        }
        
        return $addresses;
    }
    
    /**
     * Retrieve profile address by type
     *
     * @param   string $type
     * @return  Address
     */
    protected function _getAddressByType($type) {
        foreach($this->getAddressesCollection() as $address) {
            if($address->getAddressType() == $type && !$address->isDeleted()) {
                return $address;
            }
        }
        
        $address = $this->_profileAddressFactory->create()->setAddressType($type);
        $this->addAddress($address);
        return $address;
    }
    
    /**
     * Retrieve profile billing address
     *
     * @return Address
     */
    public function getBillingAddress() {
        return $this->_getAddressByType(\Toppik\Subscriptions\Model\Profile\Address::TYPE_BILLING);
    }
    
    /**
     * Retrieve profile shipping address
     *
     * @return Address
     */
    public function getShippingAddress() {
        return $this->_getAddressByType(\Toppik\Subscriptions\Model\Profile\Address::TYPE_SHIPPING);
    }
    
    /**
     *
     * @param int $addressId
     * @return Address|false
     */
    public function getAddressById($addressId) {
        foreach($this->getAddressesCollection() as $address) {
            if($address->getId() == $addressId) {
                return $address;
            }
        }
        
        return false;
    }
    
    public function addAddress($address) {
        $address->setProfile($this);
        $address->setProfileId($this->getId());
        
        if(!$address->getId()) {
            $this->getAddressesCollection()->addItem($address);
        }
        
        return $this;
    }
    
    public function setBillingAddress($address) {
        $old = $this->getBillingAddress();
        
        if(!empty($old)) {
            $old->addData($address->getData());
        } else {
            $this->addAddress($address->setAddressType(\Toppik\Subscriptions\Model\Profile\Address::TYPE_BILLING));
        }
        
        return $this;
    }
    
    public function setShippingAddress($address) {
        $old = $this->getShippingAddress();
        
        if(!empty($old)) {
            $old->addData($address->getData());
        } else {
            $this->addAddress($address->setAddressType(\Toppik\Subscriptions\Model\Profile\Address::TYPE_SHIPPING));
        }
        
        return $this;
    }
    
    /**
     * @param int|string $addressId
     * @return $this
     */
    public function removeAddress($addressId) {
        foreach($this->getAddressesCollection() as $address) {
            if($address->getId() == $addressId) {
                $address->isDeleted(true);
                break;
            }
        }
        
        return $this;
    }
    
    /**
     * Retrieve quote items collection
     *
     * @param bool $useCache
     * @return  \Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getItemsCollection($useCache = true) {
        if(null === $this->_items) {
            $this->_items = $this->_profileItemFactory->create()->getCollection()->setProfileFilter($this->getId());
            
            if($this->getId()) {
                foreach($this->_items as $item) {
                    $item->setProfile($this);
                }
            }
        }
        
        return $this->_items;
    }
    
    /**
     * Retrieve quote items array
     *
     * @return array
     */
    public function getAllItems() {
        $items = [];
        
        foreach($this->getItemsCollection() as $item) {
            if(!$item->isDeleted()) {
                $items[] = $item;
            }
        }
        
        return $items;
    }
    
    /**
     * Get array of all items what can be display directly
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getAllVisibleItems() {
        $items = [];
        
        foreach($this->getItemsCollection() as $item) {
            if(!$item->isDeleted() && !$item->getParentItemId()) {
                $items[] = $item;
            }
        }
        
        return $items;
    }
    
    /**
     * Get array of all items what can not be display directly
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getAllNonVisibleItems() {
        $items = [];
        
        foreach($this->getItemsCollection() as $item) {
            if(!$item->isDeleted() && $item->getParentItemId()) {
                $items[] = $item;
            }
        }
        
        return $items;
    }
    
    /**
     * Get array of all items what can be display directly
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getAllGiftItems() {
        $items = [];
        
        foreach($this->getItemsCollection() as $item) {
            if((int) $item->getData('is_onetime_gift') === 1) {
                $items[] = $item;
            }
        }
        
        return $items;
    }
    
    /**
     * Get array of all items what can be display directly
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getAllVisibleGiftItems() {
        $items = [];
        
        foreach($this->getItemsCollection() as $item) {
            if(!$item->isDeleted() && !$item->getParentItemId() && (int) $item->getData('is_onetime_gift') === 1) {
                $items[] = $item;
            }
        }
        
        return $items;
    }
    
    /**
     * Checking items availability
     *
     * @return bool
     */
    public function hasItems() {
        return sizeof($this->getAllItems()) > 0;
    }
    
    /**
     * Adding new item to quote
     *
     * @param  \Toppik\Subscriptions\Model\Profile\Item $item
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addItem(\Toppik\Subscriptions\Model\Profile\Item $item) {
        $item->setProfile($this);
        $item->setProfileId($this->getId());
        
        if(!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        
        return $this;
    }
    
    public function setItem($quoteItem) {
        $item = $this->_profileItemFactory->create();
        $item->addData($quoteItem->setId(null)->getData());
        $this->addItem($item);
        return $item;
    }
    
    /**
     * Mark all profile items as deleted
     *
     * @return $this
     */
    public function removeAllItems() {
        foreach($this->getItemsCollection() as $itemId => $item) {
            if($item->getId() === null) {
                $this->getItemsCollection()->removeItemByKey($itemId);
            } else {
                $item->isDeleted(true);
            }
        }
        
        return $this;
    }
    
    /**
     * Remove profile item by item identifier
     *
     * @param   int $itemId
     * @return $this
     */
    public function removeItem($itemId) {
        $item = $this->getItemsCollection()->getItemById($itemId);
        
        if($item) {
            $item->isDeleted(true);
            
            if($item->getHasChildren()) {
                foreach($item->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }
            
            $parent = $item->getParentItem();
            
            if($parent) {
                $parent->isDeleted(true);
            }
        }
        
        return $this;
    }
    
    /**
     * Checks if it was set
     *
     * @return bool
     */
    public function addressCollectionWasSet() {
        return null !== $this->_addresses;
    }
    
    /**
     * Checks if it was set
     *
     * @return bool
     */
    public function itemsCollectionWasSet() {
        return null !== $this->_items;
    }
    
    /**
     * Add status history
     *
     * @return $this
     */
    public function beforeSave() {
        $action_codes = array();
        
        if($this->getId()) {
            if($this->getStatusHistoryCode() && $this->getStatusHistoryMessage()) {
                $action_codes[] = array(
                    'code'      => $this->getStatusHistoryCode(),
                    'message'   => $this->getStatusHistoryMessage(),
                    'note'      => $this->getStatusHistoryNote()
                );
            }
            
            if($this->getId() != $this->getOrigData(self::PROFILE_ID)) {
                $message = __('Created new profile');
                $action_codes[] = array('code' => 'create', 'message' => $message);
            }
            
            if($this->getStatus() != $this->getOrigData(self::STATUS)) {
                $message = __('Status changed from "%1" to "%2"', $this->getOrigData(self::STATUS), $this->getStatus());
                $action_codes[] = array('code' => 'status_change', 'message' => $message, 'note' => $this->getStatusHistoryNote());
            }
            
            if($this->getPaymentTokenId() != $this->getOrigData(self::PAYMENT_TOKEN_ID)) {
                $message = __('Payment token ID changed from "%1" to "%2"', $this->getOrigData(self::PAYMENT_TOKEN_ID), $this->getPaymentTokenId());
                $action_codes[] = array('code' => 'cc_change', 'message' => $message, 'note' => $this->getStatusHistoryNote());
            }
            
            if($this->getFrequencyTitle() != $this->getOrigData(self::FREQUENCY_TITLE)) {
                $message = __('Billing period changed from "%1" to "%2"', $this->getOrigData(self::FREQUENCY_TITLE), $this->getFrequencyTitle());
                $action_codes[] = array('code' => 'frequency_change', 'message' => $message, 'note' => $this->getStatusHistoryNote());
            }
            
            if($this->getItemsQty() != $this->getOrigData(self::ITEMS_QTY)) {
                $message = __('Items QTY changed from "%1" to "%2"', $this->getOrigData(self::ITEMS_QTY), $this->getItemsQty());
                $action_codes[] = array('code' => 'qty_change', 'message' => $message, 'note' => $this->getStatusHistoryNote());
            }
            
            $this->unsStatusHistoryCode();
            $this->unsStatusHistoryMessage();
            $this->unsStatusHistoryNote();
        }
        
        if(count($action_codes) > 0) {
            foreach($action_codes as $_key => $_value) {
                if(isset($_value['code']) && isset($_value['message'])) {
                    try {
                        $this->eventManager->dispatch(
                            'subscription_history_add',
                            [
                                'entity'        => $this,
                                'action_code'   => $_value['code'],
                                'message'       => strip_tags(trim($_value['message'])),
                                'note'          => (isset($_value['note']) ? strip_tags(trim($_value['note'])) : null)
                            ]
                        );
                    } catch(\Exception $e) {
                        $message = sprintf('Cannot add status history on %s: %s', $_value['code'], $e->getMessage());
                        
                        $this->eventManager->dispatch(
                            'toppikreport_system_add_message',
                            [
                                'entity_type' 	=> 'subscription_history',
                                'entity_id' 	=> $this->getId(),
                                'message' 		=> $message
                            ]
                        );
                    }
                }
            }
        }
        
        return parent::beforeSave();
    }
    
    /**
     * @return string
     */
    public function getLifetimeUsedPoints() {
        if(!$this->hasData('lifetime_used_points')) {
            $model = $this->_objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Save');
            $this->setData('lifetime_used_points', $model->getLifetimeUsedPoints($this->getId()));
        }
		
        return $this->getData('lifetime_used_points');
    }
	
    /**
     * @return string
     */
    public function getOnetimeCouponCode() {
        if(!$this->hasData('onetime_coupon_code')) {
            $model = $this->_objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Save');
            $this->setData('onetime_coupon_code', $model->getOnetimeCouponCode($this->getId()));
        }
		
        return $this->getData('onetime_coupon_code');
    }
	
    /**
     * @return string
     */
    public function getAvailableOnetimePoints() {
        if($this->reportHelper->getMaxOnetimePoints() > 0 && $this->reportHelper->getMaxLifetimePoints() > 0) {
            return max(0, min($this->reportHelper->getMaxOnetimePoints(), ($this->reportHelper->getMaxLifetimePoints() - $this->getLifetimeUsedPoints())));
        } else if($this->reportHelper->getMaxOnetimePoints() > 0) {
            return $this->reportHelper->getMaxOnetimePoints();
        } else if($this->reportHelper->getMaxLifetimePoints() > 0) {
            return max(0, ($this->reportHelper->getMaxLifetimePoints() - $this->getLifetimeUsedPoints()));
        }
        
        return -1;
    }
	
    /**
     * @return string|null
     */
    public function getFirstOrderCookiesJson()
    {
        return $this->getData(self::FIRST_ORDER_COOKIES_JSON);
    }

    /**
     * @param string $first_order_cookies_json
     * @return $this
     */
    public function setFirstOrderCookiesJson($first_order_cookies_json)
    {
        $this->unsetData('first_order_cookies');
        return $this->setData(self::FIRST_ORDER_COOKIES_JSON, $first_order_cookies_json);
    }

    /**
     * @return DataObject
     */
    public function getFirstOrderCookies() {
        if(! $this->hasData('first_order_cookies')) {
            $data = Json::decode($this->getFirstOrderCookiesJson(), Json::TYPE_ARRAY);
            if(! is_array($data)) {
                $data = [];
            }
            $this->setData('first_order_cookies', new DataObject($data));
        }
        return $this->getData('first_order_cookies');
    }
    
}
