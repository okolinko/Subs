<?php
namespace Toppik\Subscriptions\Converter;

class QuoteToProfile {
    
    /**
     * Not Represent options
     *
     * @var array
     */
    protected $_representOptions = ['info_buyRequest', 'attributes'];
    
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    
    /**
     * @var \Toppik\Subscriptions\Model\ProfileFactory
     */
    private $profileFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Quote
     */
    private $quoteHelper;
    
    /**
     * @var \Toppik\Subscriptions\Model\Settings\ItemFactory
     */
    private $itemFactory;
    
    /**
     * @var \Toppik\Subscriptions\Model\Settings\SubscriptionFactory
     */
    private $subscriptionFactory;
    
    /**
     * @var \Toppik\Subscriptions\Model\Settings\PeriodFactory
     */
    private $periodFactory;
    
    /**
     * @var \Toppik\Subscriptions\Model\Settings\UnitFactory
     */
    private $unitFactory;
    
    /**
     * @var \Magento\Vault\Model\PaymentTokenManagement
     */
    private $paymentTokenManagement;
    
    /**
     * @var \Toppik\Subscriptions\Model\ResourceModel\Profile
     */
    private $profileResourceModel;
    
    
    /**
     * @var \Toppik\Subscriptions\Model\Profile\BackupFactory
     */
    private $backupFactory;
    
    /**
     * @var \Toppik\Subscriptions\Model\Profile\AddressFactory
     */
    private $addressFactory;
    
    /**
     * @var \Toppik\Subscriptions\Model\Profile\ItemFactory
     */
    private $profileItemFactory;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    private $subscriptionHelper;
    
    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 101.1.0
     */
    protected $serializer;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    protected $reportHelper;
    
    /**
     * @param \Toppik\Subscriptions\Model\ResourceModel\Profile $profileResourceModel
     * @param \Magento\Vault\Model\PaymentTokenManagement $paymentTokenManagement
     * @param \Toppik\Subscriptions\Model\Settings\UnitFactory $unitFactory
     * @param \Toppik\Subscriptions\Model\Settings\PeriodFactory $periodFactory
     * @param \Toppik\Subscriptions\Model\Settings\SubscriptionFactory $subscriptionFactory
     * @param \Toppik\Subscriptions\Model\Settings\ItemFactory $itemFactory
     * @param \Toppik\Subscriptions\Helper\Quote $quoteHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Toppik\Subscriptions\Model\ProfileFactory $profileFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Toppik\Subscriptions\Model\ResourceModel\Profile $profileResourceModel,
        \Magento\Vault\Model\PaymentTokenManagement $paymentTokenManagement,
        \Toppik\Subscriptions\Model\Settings\UnitFactory $unitFactory,
        \Toppik\Subscriptions\Model\Settings\PeriodFactory $periodFactory,
        \Toppik\Subscriptions\Model\Settings\SubscriptionFactory $subscriptionFactory,
        \Toppik\Subscriptions\Model\Settings\ItemFactory $itemFactory,
        \Toppik\Subscriptions\Helper\Quote $quoteHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Toppik\Subscriptions\Model\ProfileFactory $profileFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Toppik\Subscriptions\Model\Profile\BackupFactory $backupFactory,
        \Toppik\Subscriptions\Model\Profile\AddressFactory $addressFactory,
        \Toppik\Subscriptions\Model\Profile\ItemFactory $profileItemFactory,
        \Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
		\Toppik\Subscriptions\Helper\Report $reportHelper
    ) {
        $this->eventManager = $eventManager;
        $this->profileFactory = $profileFactory;
        $this->dateTime = $dateTime;
        $this->quoteHelper = $quoteHelper;
        $this->itemFactory = $itemFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->periodFactory = $periodFactory;
        $this->unitFactory = $unitFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->profileResourceModel = $profileResourceModel;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->backupFactory = $backupFactory;
        $this->addressFactory = $addressFactory;
        $this->profileItemFactory = $profileItemFactory;
        $this->serializer = $serializer;
		$this->reportHelper = $reportHelper;
    }
    
    /**
     * Get all subscription items from quote and create profiles
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Sales\Model\Order $order
     * @param array $orderData
     */
    public function process(\Magento\Quote\Model\Quote $quote, \Magento\Sales\Model\Order $order, $orderData = []) {
		$profiles = array();
        
        $this->eventManager->dispatch('subscriptions_quote_to_profile_modify_quote', [
            'quote' => $quote,
            'orderData' => $orderData,
            'order' => $order,
        ]);
        
        foreach($quote->getAllItems() as $quoteItem) {
            /* @var \Magento\Quote\Model\Quote\Item $quoteItem */
            if(!$quoteItem->getParentItem() && $quoteItem->getLinkedChildQuoteItem()) {
                $profile = $this->createProfile($quote, $quoteItem, $order, $orderData);
				$profiles[$profile->getId()] = $profile;
            }
        }
        
        return $profiles;
    }
    
    /**
     * Create new profile from quote and quote item
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param \Magento\Sales\Model\Order $order
     * @param array $orderData
     */
    private function createProfile(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Sales\Model\Order $order,
        $orderData = []
    ) {
        $this->eventManager->dispatch('subscriptions_quote_to_profile_modify_quote_item', [
            'quote' => $quote,
            'item' => $quoteItem,
            'orderData' => $orderData,
            'order' => $order,
        ]);
        
        foreach($order->getItemsCollection() as $_item) {
            if($_item->getSku() == $quoteItem->getSku()) {
                $_item->setIsSubscription(1)->save();
            }
        }
        
        /* @var \Toppik\Subscriptions\Model\Profile $profile */
        $profile = $this->profileFactory->create();
        
        $this->eventManager->dispatch('subscriptions_quote_to_profile_before_profile_populate', [
            'profile' => $profile,
            'quote' => $quote,
            'item' => $quoteItem,
            'orderData' => $orderData,
            'order' => $order,
        ]);
        
        $this->fillProfileWithQuoteData($profile, $quote, $quoteItem, $order, $orderData);
        
        $this->eventManager->dispatch('subscriptions_quote_to_profile_before_profile_save', [
            'profile' => $profile,
            'quote' => $quote,
            'item' => $quoteItem,
            'orderData' => $orderData,
            'order' => $order,
        ]);
        
        $profile->setLastOrderId($order->getId());
        $profile->setLastOrderAt($order->getCreatedAt());
        $profile->scheduleNextOrder();
        $profile->save();
        
        $this->_createRelations($profile, $quote, $quoteItem, $order, $orderData);
        $this->_saveBackup($profile, $quote, $quoteItem, $order, $orderData);
        $this->profileResourceModel->addOrderRelation($profile, $order);
        
        try {
            $this->reportHelper->sendNewSubscriptionEmail($profile, $order);
        } catch(\Exception $e) {
            $message = sprintf(
                'CANNOT send new subscription email to customer ID %s for profile ID %s: %s',
                $profile->getCustomerId(),
                $profile->getId(),
                $e->getMessage()
            );
            
            $this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
        }
        
        $this->eventManager->dispatch('subscriptions_quote_to_profile_after_profile_save', [
            'profile' => $profile,
            'quote' => $quote,
            'item' => $quoteItem,
            'orderData' => $orderData,
            'order' => $order,
        ]);
        
		return $profile;
    }
    
    /**
     * @param \Toppik\Subscriptions\Model\Profile $profile
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param \Magento\Sales\Model\Order $order
     * @param array $orderData
     * @return \Toppik\Subscriptions\Model\Profile
     */
    private function fillProfileWithQuoteData(
        \Toppik\Subscriptions\Model\Profile $profile,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Sales\Model\Order $order,
        $orderData = []
    ) {
        $profile->setCustomerId($quote->getCustomerId());
		$profile->setMerchantSource($order->getMerchantSource());
		$profile->setAdminId($order->getAdminId());
        $profile->setGrandTotal($quoteItem->getRowTotal());
        $profile->setBaseGrandTotal($quoteItem->getBaseRowTotal());
        $profile->setStartDate($this->dateTime->gmtDate('Y-m-d'));
        $profile->setStatus(\Toppik\Subscriptions\Model\Profile::STATUS_ACTIVE);
        $profile->setCurrencyCode($quote->getStoreCurrencyCode());
        $profile->setStoreId($quote->getStoreId());
        
        $paymentToken = $this->paymentTokenManagement->getByPaymentId($order->getPayment()->getId());
        
        if($paymentToken) {
            $profile->setPaymentTokenId($paymentToken->getEntityId());
        }
        
        $subscriptionTypeOption = $this->quoteHelper->getSubscriptionTypeOptionFromQuoteItem($quoteItem);
        /* @var \Toppik\Subscriptions\Model\Settings\Item $subscriptionItem */
        $subscriptionItem = $this->itemFactory->create();
        $subscriptionItem->load($subscriptionTypeOption);
        
        $period = $subscriptionItem->getPeriod();
        $unit   = $subscriptionItem->getUnit();
        
        if(!$unit->getId() || !$unit->getLength() || (int) $unit->getLength() < 1) {
            
        }
        
        $profile->setFrequencyTitle($period->getLength() . ' ' . $unit->getTitle() . 's');
        $profile->setFrequencyLength($period->getLength() * $unit->getLength());
        $profile->setIsInfinite($period->getData(\Toppik\Subscriptions\Model\Settings\Period::IS_INFINITE));
        $profile->setNumberOfOccurrences($period->getData(\Toppik\Subscriptions\Model\Settings\Period::NUMBER_OF_OCCURRENCES));
        $profile->setEngineCode($period->getEngineCode());
        
        return $profile;
    }
    
    private function _createRelations(
        \Toppik\Subscriptions\Model\Profile $profile,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Sales\Model\Order $order,
        $orderData = []
    ) {
        $billing    = clone $quote->getBillingAddress();
        $shipping   = clone $quote->getShippingAddress();
        
        if($this->subscriptionHelper->useFreeShipping()) {
            $shipping
                ->setBaseShippingAmount(0)
                ->setShippingAmount(0)
                ->setBaseShippingTaxAmount(0)
                ->setShippingTaxAmount(0)
                ->setBaseShippingDiscountAmount(0)
                ->setShippingDiscountAmount(0)
                ->setShippingInclTax(0)
                ->setBaseShippingInclTax(0)
                ->setShippingTaxCalculationAmount(0)
                ->setBaseShippingTaxCalculationAmount(0);
        }
        
        $shipping
            ->setItemsAppliedTaxes(false)
            ->setAppliedTaxes(false)
            ->setTaxAmount(0)
            ->setBaseTaxAmount(0)
            ->setGrandTotal(0)
            ->setBaseGrandTotal(0)
            ->setBaseSubtotalInclTax(0)
            ->setBaseSubtotalTotalInclTax(0)
            ->setSubtotalInclTax(0);
        
        if(!$shipping->getPaymentMethod()) {
            $payment_method = null;
            
            if($order->getPayment() && $order->getPayment()->getMethod()) {
                $payment_method = $order->getPayment()->getMethod();
            } else if($quote->getPayment() && $quote->getPayment()->getMethod()) {
                $payment_method = $quote->getPayment()->getMethod();
            }
            
            $shipping->setPaymentMethod($payment_method);
        }
        
        $profile->setBillingAddress($billing->setId(null));
        $profile->setShippingAddress($shipping->setId(null));
        
        $item_options = array();
        
        if(is_array($quoteItem->getOptions())) {
            foreach($quoteItem->getOptions() as $option) {
                if(in_array($option->getCode(), $this->_representOptions)) {
                    $option_data = $option->getData();
                    $this->_cleanupArray($option_data);
                    $item_options[] = $option_data;
                }
            }
        }
        
        $newQuoteItem = clone $quoteItem;
        
        $newQuoteItem
            ->setId(null)
            ->setItemId(null)
            ->setParentItemId(null)
            ->setQuoteItemId($quoteItem->getId())
            ->setItemOptions((count($item_options) ? serialize($item_options) : null));
        
        $profileItem = $profile->setItem($newQuoteItem);
        
        if($quoteItem->getHasChildren()) {
            foreach($quoteItem->getChildren() as $_childQuoteItem) {
                $item_options = array();
                
                if(is_array($_childQuoteItem->getOptions())) {
                    foreach($_childQuoteItem->getOptions() as $option) {
                        if(in_array($option->getCode(), $this->_representOptions)) {
                            $option_data = $option->getData();
                            $this->_cleanupArray($option_data);
                            $item_options[] = $option_data;
                        }
                    }
                }
                
                $newChildQuoteItem = clone $_childQuoteItem;
                
                $newChildQuoteItem
                    ->setId(null)
                    ->setItemId(null)
                    ->setParentItemId(null)
                    ->setQuoteItemId($_childQuoteItem->getId())
                    ->setItemOptions((count($item_options) ? serialize($item_options) : null));
                
                $childProfileItem = $profile->setItem($newChildQuoteItem);
                $childProfileItem->setParentItem($profileItem);
            }
        }
        
        $profile->setItemsCount(((int) $profile->getItemsCount() + 1));
        $profile->setItemsQty(((int) $profile->getItemsQty() + (int) $newQuoteItem->getQty()));
        $profile->setSku($newQuoteItem->getSku());
        
        $profile->setOrigData();
        $profile->setOrigData(\Toppik\Subscriptions\Model\Profile::PROFILE_ID);
        
        $profile->save();
    }
    
    private function _saveBackup(
        \Toppik\Subscriptions\Model\Profile $profile,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Sales\Model\Order $order,
        $orderData = []
    ) {
        try {
            $paymentToken = $this->paymentTokenManagement->getByPaymentId($order->getPayment()->getId());
            
            $itemData = $quoteItem->getData();
            $itemData['options'] = [];
            
            foreach($quoteItem->getOptions() as $option) {
                $itemData['options'][] = $option->getData();
            }
            
            if($quoteItem->getHasChildren()) {
                $itemData['children'] = [];
                
                foreach($quoteItem->getChildren() as $child) {
                    $childData = $child->getData();
                    $itemData['children'][] = $childData;
                    $childData['options'] = [];
                    
                    foreach($child->getOptions() as $option) {
                        $childData['options'][] = $option->getData();
                    }
                }
            }
            
            $backup = $this->backupFactory->create();
            
            $backup->setProfileId($profile->getId());
            $backup->setCustomerId($profile->getCustomerId());
            $backup->setAdminId($order->getAdminId());
            $backup->setPaymentTokenId($profile->getPaymentTokenId());
            $backup->setGrandTotal($profile->getGrandTotal());
            $backup->setStartDate($profile->getStartDate());
            
            $backup->setLastOrderId($order->getId());
            $backup->setLastOrderAt($order->getCreatedAt());
            $backup->setSource($order->getSource());
            $backup->setMerchantSource($order->getMerchantSource());
            
            $backup->setBillingAddressJson(
                $this->_toJson(
                    $quote->getBillingAddress()->getData()
                )
            );
            
            $backup->setShippingAddressJson(
                $this->_toJson(
                    $quote->getShippingAddress()->getData()
                )
            );
            
            $backup->setItemsJson(
                $this->_toJson($itemData)
            );
            
            $backup->setQuoteJson(
                $this->_toJson(
                    $quote->getData()
                )
            );
            
            if($paymentToken) {
                $backup->setPaymentTokenJson(
                    $this->_toJson(
                        $paymentToken->getData()
                    )
                );
            }
            
            $backup->save();
        } catch(\Exception $e) {
            $message = sprintf('Error during processing saving backup for profile ID %s: %s', $profile->getId(), $e->getMessage());
            
            $this->eventManager->dispatch(
                'toppikreport_system_add_message',
                ['entity_type' => 'subscription_backup', 'entity_id' => $profile->getId(), 'message' => $message]
            );
        }
    }
    
    private function _toJson($array) {
        $this->_cleanupArray($array);
        return \Zend\Json\Json::prettyPrint(\Zend\Json\Json::encode($array, true));
    }
    
    /**
     * Recursively cleanup array from objects
     * @param array &$array
     * @return void
     */
    private function _cleanupArray(&$array) {
        if(!$array || !is_array($array)) {
            return;
        }
        
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $this->_cleanupArray($array[$key]);
            } elseif(!is_scalar($value)) {
                unset($array[$key]);
            }
        }
    }
    
}
