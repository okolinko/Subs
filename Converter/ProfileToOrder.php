<?php
namespace Toppik\Subscriptions\Converter;

use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Vault\Model\Method\Vault;
use Magento\Vault\Model\PaymentTokenFactory;
use Symfony\Component\Config\Definition\Exception\Exception;
use Toppik\Subscriptions\Model\Profile;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Model\QuoteValidator;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;
use Magento\Sales\Api\OrderManagementInterface as OrderManagement;
use Magento\Quote\Model\CustomerManagement;
use \Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrder as ToOrderConverter;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ToOrderAddressConverter;
use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Toppik\Subscriptions\Helper\Data as SubscriptionHelper;
use Toppik\Subscriptions\Model\ResourceModel\Profile as ProfileResourceModel;
use Magento\Quote\Model\Quote as QuoteEntity;
use Zend\Json\Json;
use Toppik\Subscriptions\Helper\Quote as QuoteHelper;

class ProfileToOrder extends \Magento\Quote\Model\QuoteManagement {
    
    /**
     * Not Represent options
     *
     * @var array
     */
    protected $_notRepresentOptions = ['info_buyRequest'];
    
    /**
     * Not Represent options
     *
     * @var array
     */
    protected $_representOptions = ['attributes'];
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    /**
     * @var Quote\ItemFactory
     */
    private $itemFactory;
    
    /**
     * @var ItemsForReindex
     */
    private $itemsForReindex;
    
    /**
     * @var PaymentTokenFactory
     */
    private $paymentTokenFactory;
    
    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;
    
    /**
     * @var ProductRepository
     */
    private $productRepository;
    
    /**
     * @var StockStateInterface
     */
    private $stockState;
    
    /**
     * @var Quote\Item\OptionFactory
     */
    private $itemOptionFactory;
    
    /**
     * @var ProfileResourceModel
     */
    private $profileResourceModel;
    

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;
    
    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $_quoteItemFactory;
    
    /**
     * @var \Magento\Quote\Model\Quote\Item\Processor
     */
    protected $itemProcessor;
    
    /**
     * Address factory.
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;
    
    /**
     * @var \Magento\SalesRule\Model\RulesApplier
     */
    protected $rulesApplier;
    
    /**
     * @var CustomerAddressInterfaceFactory
     */
    protected $customerAddressFactory;
    
    /** @var RegionInterface */
    protected $regionData;
    
    /** @var CountryFactory */
    protected $countryFactory;
	
    /** @var RegionFactory */
    protected $regionFactory;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
    
    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 101.1.0
     */
    protected $serializer;
    
    /**
     * ProfileToOrder constructor.
     * @param ProfileResourceModel $profileResourceModel
     * @param Quote\Item\OptionFactory $itemOptionFactory
     * @param StockStateInterface $stockState
     * @param ProductRepository $productRepository
     * @param SubscriptionHelper $subscriptionHelper
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param ItemsForReindex $itemsForReindex
     * @param Quote\ItemFactory $itemFactory
     * @param EventManager $eventManager
     * @param QuoteValidator $quoteValidator
     * @param OrderFactory $orderFactory
     * @param OrderManagement $orderManagement
     * @param CustomerManagement $customerManagement
     * @param ToOrderConverter $quoteAddressToOrder
     * @param ToOrderAddressConverter $quoteAddressToOrderAddress
     * @param ToOrderItemConverter $quoteItemToOrderItem
     * @param ToOrderPaymentConverter $quotePaymentToOrderPayment
     * @param UserContextInterface $userContext
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerModelFactory
     * @param Quote\AddressFactory $quoteAddressFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        EventManager $eventManager,
        \Magento\Quote\Model\SubmitQuoteValidator $submitQuoteValidator,
        OrderFactory $orderFactory,
        OrderManagement $orderManagement,
        CustomerManagement $customerManagement,
        ToOrderConverter $quoteAddressToOrder,
        ToOrderAddressConverter $quoteAddressToOrderAddress,
        ToOrderItemConverter $quoteItemToOrderItem,
        ToOrderPaymentConverter $quotePaymentToOrderPayment,
        UserContextInterface $userContext,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory = null,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository = null,
        \Magento\Framework\App\RequestInterface $request = null,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress = null,
        
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ProfileResourceModel $profileResourceModel,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        StockStateInterface $stockState,
        ProductRepository $productRepository,
        SubscriptionHelper $subscriptionHelper,
        PaymentTokenFactory $paymentTokenFactory,
        ItemsForReindex $itemsForReindex,
        Quote\ItemFactory $itemFactory,
        QuoteValidator $quoteValidator,
        QuoteHelper $quoteHelper,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        \Magento\SalesRule\Model\RulesApplier $rulesApplier,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $customerAddressFactory,
        \Magento\Customer\Api\Data\RegionInterface $regionData,
		\Magento\Directory\Model\CountryFactory $countryFactory,
		\Magento\Directory\Model\RegionFactory $regionFactory,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        parent::__construct($eventManager, $submitQuoteValidator, $orderFactory, $orderManagement, $customerManagement, $quoteAddressToOrder, $quoteAddressToOrderAddress, $quoteItemToOrderItem, $quotePaymentToOrderPayment, $userContext, $quoteRepository, $customerRepository, $customerModelFactory, $quoteAddressFactory, $dataObjectHelper, $storeManager, $checkoutSession, $customerSession, $accountManagement, $quoteFactory, $quoteIdMaskFactory, $addressRepository, $request, $remoteAddress);
        $this->_objectManager = $objectManager;
        $this->itemFactory = $itemFactory;
        $this->itemsForReindex = $itemsForReindex;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->productRepository = $productRepository;
        $this->stockState = $stockState;
        $this->itemOptionFactory = $itemOptionFactory;
        $this->registry = $registry;
        $this->profileResourceModel = $profileResourceModel;
        $this->quoteHelper = $quoteHelper;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->itemProcessor = $itemProcessor;
        $this->addressRepository = $addressRepository;
        $this->rulesApplier = $rulesApplier;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->regionData = $regionData;
		$this->countryFactory = $countryFactory;
		$this->regionFactory = $regionFactory;
		$this->reportHelper = $reportHelper;
        $this->serializer = $serializer;
    }
    
    /**
     * @param Profile $parent
     * @param Profile[] $children
     * @return Order
     */
    public function process(Profile $parent, array $children = []) {
        $this->storeManager->setCurrentStore($parent->getStoreId());
        
        $quote = $this->createQuote($parent, $children);
		
        /* @var Order $order */
        $order = $this->submitQuote($quote);
		
        return $order;
    }
    
    /**
     * @param Profile $parent
     * @param array $children
     * @return mixed
     * @throws \Exception
     */
    public function createQuote(Profile $parent, array $children = []) {
		$this->reportHelper->log(sprintf('Creating quote for profile ID %s', $parent->getId()), []);
		
        $quote = $this->quoteFactory->create();
		
        $quote->setHasSubscription(true);
        $quote->setCreateFromSubscriptionProfile(true); #important for collect subtotal
        $quote->setSubscriptionProfileDataParent($parent);
        $quote->setSubscriptionProfileDataChildren($children);
		
        $quote->setStoreId($parent->getStoreId());
		
        $quote->setReservedOrderId(null);
        $quote->reserveOrderId();
		
		$quote->setSkipQuoteDiscountCollector(true);
		
		if(!$parent->getCustomerId()) {
			throw new \Exception(sprintf('createQuote -> Empty customer ID for profile ID %s', $parent->getId()));
		}
		
        $customer = $this->customerRepository->getById($parent->getCustomerId());
		
		if(!$customer->getId()) {
			throw new \Exception(sprintf('createQuote -> Customer with ID %s does not exist for profile ID %s', $parent->getCustomerId(), $parent->getId()));
		}
		
        $customer->getAddresses();
		
        $quote->setCurrency();
        $quote->setCustomer($customer);
        $quote->setInventoryProcessed(false);
        
		$this->quoteRepository->save($quote);
		
        $this->generateQuoteItems($parent, $children, $quote);
		
        $this->createQuoteAddress($quote->getShippingAddress(), $parent->getShippingAddress()->getData());
        $this->createQuoteAddress($quote->getBillingAddress(), $parent->getBillingAddress()->getData());
		
        $quote->getShippingAddress()
                ->setCollectShippingRates(true);
        
        $quote->getShippingAddress()
                ->setFreeShipping(true)
                ->collectShippingRates()
                ->setShippingMethod($parent->getShippingAddress()->getShippingMethod())
                ->setShippingDescription('Standard');
        
        $quote->getPayment()
                ->setMethod($parent->getShippingAddress()->getPaymentMethod())
                ->importData(['method' => $parent->getShippingAddress()->getPaymentMethod()]);
        
        $this->createQuotePayment($quote, $parent);
        
        $quote->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
        
		$this->quoteRepository->save($quote);
		
        $this->_applyCoupon($parent, $quote);
        
        return $quote;
    }
    
    /**
     * Submit quote
     *
     * @param Quote $quote
     * @param array $orderData
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|object
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function submitQuote(QuoteEntity $quote, $orderData = []) {
		$this->reportHelper->log(
			sprintf('Submitting quote ID %s for profile ID %s', $quote->getId(), $quote->getSubscriptionProfileDataParent()->getId()),
			[]
		);
		
        $order = $this->orderFactory->create();
		
        //$this->quoteValidator->validateBeforeSubmit($quote);
        /*if (!$quote->getCustomerIsGuest()) {
            if ($quote->getCustomerId()) {
                $this->_prepareCustomerQuote($quote);
            }
            $this->customerManagement->populateCustomerInfo($quote);
        }*/
		
		$order->setPreconfiguredMerchantSource($quote->getSubscriptionProfileDataParent()->getMerchantSource());
		$order->setAdminId($quote->getSubscriptionProfileDataParent()->getAdminId());
        
        $addresses = [];
        $quote->reserveOrderId();
		
        if($quote->isVirtual()) {
            $this->dataObjectHelper->mergeDataObjects(
                '\Magento\Sales\Api\Data\OrderInterface',
                $order,
                $this->quoteAddressToOrder->convert($quote->getBillingAddress(), $orderData)
            );
        } else {
            $this->dataObjectHelper->mergeDataObjects(
                '\Magento\Sales\Api\Data\OrderInterface',
                $order,
                $this->quoteAddressToOrder->convert($quote->getShippingAddress(), $orderData)
            );
			
            $shippingAddress = $this->quoteAddressToOrderAddress->convert(
                $quote->getShippingAddress(),
                [
                    'address_type' => 'shipping',
                    'email' => $quote->getCustomerEmail()
                ]
            );
			
            $addresses[] = $shippingAddress;
            $order->setShippingAddress($shippingAddress);
            $order->setShippingMethod($quote->getShippingAddress()->getShippingMethod());
        }
		
        $billingAddress = $this->quoteAddressToOrderAddress->convert(
            $quote->getBillingAddress(),
            [
                'address_type' => 'billing',
                'email' => $quote->getCustomerEmail()
            ]
        );
		
        $addresses[] = $billingAddress;
        $order->setBillingAddress($billingAddress);
		
        $this->createOrderPayment($order, $quote);
        $order->setAddresses($addresses);
        $order->setItems($this->resolveItems($quote));
		
        if($quote->getCustomer()) {
            $order->setCustomerId($quote->getCustomer()->getId());
        }
		
        $order->setQuoteId($quote->getId());
        $order->setCustomerEmail($quote->getCustomerEmail());
        $order->setCustomerFirstname($quote->getCustomerFirstname());
        $order->setCustomerMiddlename($quote->getCustomerMiddlename());
        $order->setCustomerLastname($quote->getCustomerLastname());
        // $order->setCouponCode('');
		
        $this->updateOrderTotals($order);
        
        $this->checkOrderTotals($order);
        
        $this->eventManager->dispatch(
            'sales_model_service_quote_submit_before',
            [
                'order' => $order,
                'quote' => $quote
            ]
        );
		
        try {
			$this->reportHelper->log(
				sprintf('Placing order for quote ID %s for profile ID %s', $quote->getId(), $quote->getSubscriptionProfileDataParent()->getId()),
				[]
			);
			
            $order = $this->orderManagement->place($order);
			
            /*
             * fix error - Missing required argument $debugHintsPath of Magento\Developer\Model\TemplateEngine\Plugin\DebugHints.
             * https://community.magento.com/t5/Programming-Questions/Email-template-with-block/td-p/31724
             */
            $this->registry->unregister('command-line-order-notify');
            $this->registry->register('command-line-order-notify',true);
			
            $this->orderManagement->notify($order->getId());
			
            $quote->setIsActive(false);
            $this->quoteRepository->save($quote);
			
            $this->orderRelations($quote->getSubscriptionProfileDataParent(), $quote->getSubscriptionProfileDataChildren(), $order);
			
            $this->eventManager->dispatch(
                'sales_model_service_quote_submit_success',
                [
                    'order' => $order,
                    'quote' => $quote
                ]
            );
			
            $this->quoteRepository->save($quote);
        } catch(\Exception $e) {
            $quote->setIsActive(false);
            $this->quoteRepository->save($quote);
			
            $this->eventManager->dispatch(
                'sales_model_service_quote_submit_failure',
                [
                    'order'     => $order,
                    'quote'     => $quote,
                    'exception' => $e
                ]
            );
			
            throw $e;
        }
		
        return $order;
    }
    
    protected function _applyCoupon($profile, $quote) {
        if($profile->getOnetimeCouponCode() !== null) {
            $code   = (string) $profile->getOnetimeCouponCode();
            $coupon = $this->_objectManager->get('\Magento\SalesRule\Model\CouponFactory')->create();
            
            $coupon->load($code, 'code');
            
            if($coupon->getId()) {
                if((int) $coupon->getTimesUsed() < 1) {
                    $rule = $this->_objectManager->get('\Magento\SalesRule\Model\RuleFactory')->create();
                    
                    $rule->load($coupon->getRuleId());
                    
                    if($rule->getId()) {
                        $this->reportHelper->log(
                            sprintf(
                                'Apply Coupon Code %s',
                                $code
                            ),
                            []
                        );
                        
                        $quote->setCouponCode($code);
                        $quote->getShippingAddress()->setCouponCode($code);
                        
                        foreach($quote->getAllVisibleItems() as $_item) {
                            $appliedRuleIds = $this->rulesApplier->applyRules(
                                $_item,
                                array($rule),
                                false,
                                $code
                            );
                            
                            $this->rulesApplier->setAppliedRuleIds($_item, $appliedRuleIds);
                            
                            $this->reportHelper->log(
                                sprintf(
                                    'Applied rule IDs: %s, item ID %s, item sku %s, item price %s, item row total %s, item discount %s',
                                    implode(', ', $appliedRuleIds),
                                    $_item->getId(),
                                    $_item->getSku(),
                                    $_item->getPrice(),
                                    $_item->getRowTotal(),
                                    $_item->getDiscountAmount()
                                ),
                                []
                            );
                        }
                        
                        $quote->setTotalsCollectedFlag(false)
                                ->collectTotals()
                                ->save();
                        
                        $this->quoteRepository->save($quote);
                    }
                }
            }
        }
    }
    
    private function generateQuoteItems($profile, $childProfiles, Quote $quote) {
        $quote->getItemsCollection()->removeAllItems();
		
        $profiles = array_merge(array($profile), $childProfiles);
        
        foreach($profiles as $_profile) {
            $this->reportHelper->log(
                sprintf(
                    'generateQuoteItems -> Items count for profile ID %s quote ID %s is %s',
                    $_profile->getId(),
                    $quote->getId(),
                    count($_profile->getAllVisibleItems())
                ),
                []
            );
            
            foreach($_profile->getAllVisibleItems() as $_item) {
                $options    = null;
                $product    = null;
                $simple     = null;
                
                if(!empty($_item->getItemOptions())) {
                    $options = unserialize($_item->getItemOptions());
                }
                
                try {
                    $product = $this->productRepository->getById($_item->getProductId(), false, $_profile->getStoreId(), true);
                    
                    if(is_array($options) && count($options) > 0) {
                        foreach($options as $_k => $optionData) {
                            if(isset($optionData['code']) && in_array($optionData['code'], $this->_representOptions) && isset($optionData['value'])) {
                                $value = $optionData['value'];
                                
                                try {
                                    $value = unserialize($optionData['value']);
                                    $value = $this->serializer->serialize($value);
                                } catch(\Exception $e) {
                                    $value = $optionData['value'];
                                }
                                
                                $options[$_k]['value'] = $value;
                                $product->addCustomOption($optionData['code'], $value);
                            }
                        }
                    }
                } catch(\Exception $e) {
                    throw new \Exception(sprintf('%s: product # %s', $e->getMessage(), $_item->getSku()));
                }
                
                $item = $this->getItemByProduct($quote, $product);
                
                if(!$item) {
                    $this->reportHelper->log(
                        sprintf(
                            ' -> Adding product ID %s (%s) %s into quote ID %s with price %s',
                            $_item->getProductId(),
                            $_item->getProductType(),
                            $_item->getSku(),
                            $quote->getId(),
                            $_item->getPrice()
                        ),
                        []
                    );
                    
                    $item = $this->_quoteItemFactory->create();
                    
                    $item->setQuote($quote)->setProduct($product);
                    
                    $item
                        ->addData($_item->getData())
                        ->setId(null)
                        ->setItemId(null)
                        ->setParentItemId(null)
                        ->setOriginalPrice($product->getFinalPrice())
                        ->setBaseOriginalPrice($product->getFinalPrice())
                        ->setQuote($quote);
                    
                    try {
                        if($_profile->hasData('subscription_item_json') && $_profile->getData('subscription_item_json')) {
                            $finalPrice 			= $item->getPrice();
                            $itemJson               = $_profile->getData('subscription_item_json');
                            $subscriptionItem       = new \Magento\Framework\DataObject(\Zend\Json\Json::decode($itemJson, \Zend\Json\Json::TYPE_ARRAY));
                            $subscriptionFinalPrice = $subscriptionItem->getRegularPrice();
                            
                            $this->reportHelper->log(
                                sprintf('generateQuoteItems -> price -> %s -> %s -> %s', $_profile->getId(), $finalPrice, $subscriptionFinalPrice),
                                []
                            );
                            
                            if($finalPrice && $subscriptionFinalPrice && $subscriptionFinalPrice > 0 && $finalPrice != $subscriptionFinalPrice) {
                                $finalPrice = $subscriptionFinalPrice;
                                $item->setPrice($finalPrice);
                                $item->calcRowTotal();
                                
                                $this->reportHelper->log(
                                    sprintf('generateQuoteItems -> price update -> %s -> %s -> %s', $_profile->getId(), $item->getPrice(), $item->getRowTotal()),
                                    []
                                );
                                
                            }
                        }
                    } catch(\Exception $e) {
                        $this->reportHelper->log($e->getMessage());
                    }
                    
                    if($_item->getHasChildren()) {
                        foreach($_item->getChildren() as $child) {
                            if($child->getProductId()) {
                                try {
                                    $simple = $this->productRepository->getById($child->getProductId(), false, $_profile->getStoreId(), true);
                                } catch(\Exception $e) {
                                    throw new \Exception(sprintf('%s: product ID %s', $e->getMessage(), $child->getProductId()));
                                }
                            }
                        }
                    }
                    
                    if(is_array($options) && count($options) > 0) {
                        foreach($options as $optionData) {
                            if(isset($optionData['code']) && $optionData['code'] == 'simple_product' && isset($optionData['value'])) {
                                try {
                                    $simple = $this->productRepository->getById($optionData['value'], false, $_profile->getStoreId(), true);
                                } catch(\Exception $e) {
                                    throw new \Exception(sprintf('%s: product ID %s', $e->getMessage(), $optionData['value']));
                                }
                            }
                        }
                        
                        foreach($options as $optionData) {
                            /* @var \Magento\Quote\Model\Quote\Item\Option $option */
                            $option = $this->itemOptionFactory->create();
                            
                            $option->setData($optionData);
                            
                            if($simple) {
                                $option->setProduct($simple);
                            }
                            
                            if(in_array($option->getCode(), $this->_notRepresentOptions)) {
                                continue;
                            }
                            
                            $item->addOption($option);
                        }
                        
                        if($_item->getHasChildren()) {
                            if($simple) {
                                /* @var \Magento\Quote\Model\Quote\Item\Option $option */
                                $option = $this->itemOptionFactory->create();
                                
                                $option->setData(array('code' => 'product_qty_' . $simple->getId(), 'value' => 1, 'product_id' => $simple->getId()));
                                $option->setProduct($simple);
                                
                                $item->addOption($option);
                            }
                        }
                    }
                    
                    $item->save();
                    
                    $quote->getItemsCollection()->addItem($item);
                    
                    $this->reportHelper->log(
                        sprintf(
                            ' -> Added product ID %s (%s) %s and item ID %s into quote ID %s with price %s',
                            $item->getProductId(),
                            $item->getProductType(),
                            $item->getSku(),
                            $item->getId(),
                            $quote->getId(),
                            $item->getPrice()
                        ),
                        []
                    );
                    
                    if($_item->getHasChildren()) {
                        foreach($_item->getChildren() as $child) {
                            $child_item = $this->_quoteItemFactory->create();
                            
                            $child_item 
                                ->addData($child->getData())
                                ->setId(null)
                                ->setItemId(null)
                                ->setParentItemId(null)
                                ->setParentItem($item)
                                ->setQuote($quote);
                            
                            $child_item->save();
                            
                            $quote->getItemsCollection()->addItem($child_item);
                            
                            $this->reportHelper->log(
                                sprintf(
                                    ' -> Added child product ID %s (%s) and item ID %s into quote ID %s with price %s and parent ID %s',
                                    $child_item->getProductId(),
                                    $child_item->getProductType(),
                                    $child_item->getId(),
                                    $quote->getId(),
                                    $child_item->getPrice(),
                                    $child->getParentItemId()
                                ),
                                []
                            );
                        }
                    }
                } else {
                    $item->addQty($_item->getQty());
                    
                    $this->reportHelper->log(
                        sprintf(
                            ' -> Added QTY (%s) to product ID %s (%s) and item ID %s into quote ID %s with price %s and final qty %s',
                            $_item->getQty(),
                            $item->getProductId(),
                            $item->getProductType(),
                            $item->getId(),
                            $quote->getId(),
                            $item->getPrice(),
                            $item->getQty()
                        ),
                        []
                    );
                }
            }
        }
        
		if(count($quote->getItemsCollection()) < 1) {
			throw new \Exception(
				sprintf(
					'No items were added to order # %s (%s) and quote ID %s',
					$quote->getReservedOrderId(),
					count($quote->getItemsCollection()),
					$quote->getId()
				)
			);
		}
    }
    
    protected function createQuoteAddress(Address $quoteAddress, $addressData) {
		$country_id = isset($addressData['country_id']) ? $addressData['country_id'] : null;
		$region_id 	= isset($addressData['region_id']) ? $addressData['region_id'] : null;
		
		$country = $this->countryFactory->create();
		$country->load($country_id);
		
		if(!$country->getId()) {
			throw new \Exception(
				sprintf('createQuoteAddress -> Country with ID %s does not exist for %s address', $country_id, $quoteAddress->getAddressType())
			);
		}
		
		$region = $this->regionFactory->create();
		$region->load($region_id);
		
		/* if(!$region->getId()) {
			throw new \Exception(
				sprintf('createQuoteAddress -> Region with ID %s does not exist for %s address', $region_id, $quoteAddress->getAddressType())
			);
		} */
		
        $regionDataObject = clone $this->regionData;
		
        $this->dataObjectHelper->populateWithArray(
            $regionDataObject,
            $region->getData(),
            'Magento\Customer\Api\Data\RegionInterface'
        );
		
		$addressData['region'] = $regionDataObject;
		$addressData['street'] = isset($addressData['street']) ? array($addressData['street']) : array();
		
        /**
         * @var \Magento\Customer\Api\Data\AddressInterface $addressDataObject
         */
        $addressDataObject = $this->customerAddressFactory->create();
		
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            '\Magento\Customer\Api\Data\AddressInterface'
        );
		
		$quoteAddress->importCustomerAddressData($addressDataObject);
        
        $quoteAddress->unsetData('customer_address_id');
        $quoteAddress->unsetData('cached_items_all');
		
        return $this;
    }
	
    protected function createQuotePayment($quote, $profile) {
		if(!$profile->getPaymentTokenId()) {
			throw new \Exception(sprintf('createQuotePayment -> Empty token ID for profile ID %s', $profile->getId()));
		}
		
        $quotePayment = $quote->getPayment();
        $quotePayment->setQuote($quote);
        $quotePayment->setPaymentTokenId($profile->getPaymentTokenId());
        return $quotePayment;
    }
	
    protected function createOrderPayment($order, $quote) {
        $quotePayment = $quote->getPayment();
		
		if(!$quotePayment->getPaymentTokenId()) {
			throw new \Exception(sprintf('createOrderPayment -> Empty token ID for profile ID %s', $quote->getSubscriptionProfileDataParent()->getId()));
		}
		
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->load($quotePayment->getPaymentTokenId());
		
		if(!$paymentToken->getId()) {
			throw new \Exception(
				sprintf(
					'createOrderPayment -> Payment token with ID %s does not exist for profile ID %s',
					$quotePayment->getPaymentTokenId(),
					$quote->getSubscriptionProfileDataParent()->getId()
				)
			);
		}
		
        $payment = $this->quotePaymentToOrderPayment->convert($quotePayment);
        $payment->setQuote($quote);
        
        $additionalInformation = $payment->getAdditionalInformation();
		
        $additionalInformation[\Magento\Vault\Api\Data\PaymentTokenInterface::CUSTOMER_ID] = $paymentToken->getCustomerId();
        $additionalInformation[\Magento\Vault\Api\Data\PaymentTokenInterface::PUBLIC_HASH] = $paymentToken->getPublicHash();
        
        $additionalInformation[Vault::TOKEN_METADATA_KEY] = [
            \Magento\Vault\Api\Data\PaymentTokenInterface::CUSTOMER_ID => $paymentToken->getCustomerId(),
            \Magento\Vault\Api\Data\PaymentTokenInterface::PUBLIC_HASH => $paymentToken->getPublicHash(),
        ];
		
        $payment->setAdditionalInformation($additionalInformation);
		
        $extensionAttributes = $payment->getExtensionAttributes();
        $extensionAttributes->setVaultPaymentToken($paymentToken);
        $payment->setExtensionAttributes($extensionAttributes);
		
        $order->setPayment($payment);
		
        return $this;
    }
    
    /**
     * @param Quote $quote
     * @return array
     */
    protected function resolveItems(QuoteEntity $quote) {
        $quoteItems = [];
        $orderItems = [];
        
        foreach($quote->getAllItems() as $quoteItem) {
            /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $quoteItem */
            $quoteItems[$quoteItem->getId()] = $quoteItem;
        }
        
        foreach($quoteItems as $quoteItem) {
            $parentItem = (isset($orderItems[$quoteItem->getParentItemId()])) ? $orderItems[$quoteItem->getParentItemId()] : null;
            
            $orderItems[$quoteItem->getId()] =
                $this->quoteItemToOrderItem->convert(
                    $quoteItem,
                    [
                        'parent_item'   => $parentItem,
                        'sku'           => $quoteItem->getSku(),
                        'name'          => $quoteItem->getName()
                    ]
                );
        }
        
		$this->reportHelper->log(
			sprintf('Items count for order is %s', count($orderItems)),
			[]
		);
        
        return array_values($orderItems);
    }
    
    private function updateOrderTotals(Order $order) {
        $baseDiscountAmount = 0;
        $discountAmount = 0;

        $baseShippingAmount = $order->getBaseShippingAmount();
        $shippingAmount = $order->getShippingAmount();


        $baseShippingTaxAmount = $order->getBaseShippingTaxAmount();
        $shippingTaxAmount = $order->getShippingTaxAmount();

        $baseTaxAmount = 0;
        $taxAmount = 0;

        $baseTotalQtyOrdered = 0;
        $totalQtyOrdered = 0;

        $baseShippingDiscountAmount = $order->getBaseShippingDiscountAmount();
        $shippingDiscountAmount = $order->getShippingDiscountAmount();

        if($this->subscriptionHelper->useFreeShipping()) {
            $baseShippingAmount = 0;
            $shippingAmount = 0;
            $baseShippingDiscountAmount = 0;
            $shippingDiscountAmount = 0;
            $baseShippingTaxAmount = 0;
            $shippingTaxAmount = 0;
        }

        $baseSubtotal = 0;
        $subtotal = 0;

        $weight = 0;

        foreach($order->getAllItems() as $item) {
            $baseDiscountAmount -= $item->getBaseDiscountAmount();
            $discountAmount -= $item->getDiscountAmount();

            $baseTaxAmount += $item->getBaseTaxAmount();
            $taxAmount += $item->getTaxAmount();

            $baseTotalQtyOrdered += $item->getQtyOrdered();
            $totalQtyOrdered += $item->getQtyOrdered();

            $baseSubtotal += $item->getBaseRowTotal();
            $subtotal += $item->getRowTotal();

			$this->reportHelper->log(
				sprintf(
					' -> updateOrderTotals -> Item ID %s and product ID %s (%s) and price %s and row_total %s',
					$item->getId(),
					$item->getProductId(),
					$item->getProductType(),
					$item->getPrice(),
					$item->getRowTotal()
				),
				[]
			);
			
            $weight += $item->getWeight();
        }

        $baseSubtotalInclTax = $baseSubtotal + $baseTaxAmount;
        $subtotalInclTax = $subtotal + $taxAmount;

        $baseGrandTotal = $baseSubtotal + $baseShippingAmount + $baseTaxAmount + $baseDiscountAmount;
        $grandTotal = $subtotal + $shippingAmount + $taxAmount + $discountAmount;

        $baseTotalDue = $baseGrandTotal;
        $totalDue = $grandTotal;

		$this->reportHelper->log(sprintf(' -> updateOrderTotals -> subtotal %s', $subtotal), []);
		$this->reportHelper->log(sprintf(' -> updateOrderTotals -> taxAmount %s', $taxAmount), []);
		$this->reportHelper->log(sprintf(' -> updateOrderTotals -> shippingAmount %s', $shippingAmount), []);
		$this->reportHelper->log(sprintf(' -> updateOrderTotals -> discountAmount %s', $discountAmount), []);
		$this->reportHelper->log(sprintf(' -> updateOrderTotals -> grandTotal %s', $grandTotal), []);
		
        $order
            ->setBaseDiscountAmount($baseDiscountAmount)
            ->setDiscountAmount($discountAmount)

            ->setBaseGrandTotal($baseGrandTotal)
            ->setGrandTotal($grandTotal)

            ->setBaseShippingAmount($baseShippingAmount)
            ->setShippingAmount($shippingAmount)

            ->setBaseShippingTaxAmount($baseShippingTaxAmount)
            ->setShippingTaxAmount($shippingTaxAmount)

            ->setBaseSubtotal($baseSubtotal)
            ->setSubtotal($subtotal)

            ->setBaseTaxAmount($baseTaxAmount)
            ->setTaxAmount($taxAmount)

            ->setBaseTotalQtyOrdered($baseTotalQtyOrdered)
            ->setTotalQtyOrdered($totalQtyOrdered)

            ->setBaseShippingDiscountAmount($baseShippingDiscountAmount)
            ->setShippingDiscountAmount($shippingDiscountAmount)

            ->setBaseSubtotalInclTax($baseSubtotalInclTax)
            ->setSubtotalInclTax($subtotalInclTax)

            ->setBaseTotalDue($baseTotalDue)
            ->setTotalDue($totalDue)

            ->setWeight($weight);
		
		return $this;
    }
    
	public function validateProfile($profile) {
		try {
            foreach($profile->getAllVisibleItems() as $_item) {
                if(empty($_item->getSku())) {
                    throw new \Exception(sprintf('Empty sku for profile ID %s', $profile->getId()));
                }
            }
            
            $this->verifyStock(array($profile));
		} catch(\Exception $e) {
			throw new \Exception(
                $e->getMessage(),
                \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_STOCK
            );
		}
        
		try {
            if(!$profile->getCustomerId()) {
                throw new \Exception(sprintf('Empty customer ID for profile ID %s', $profile->getId()));
            }
            
            $customer = $this->customerRepository->getById($profile->getCustomerId());
            
            if(!$customer->getId()) {
                throw new \Exception(sprintf('Customer with ID %s does not exist for profile ID %s', $profile->getCustomerId(), $profile->getId()));
            }
		} catch(\Exception $e) {
			throw new \Exception(
                sprintf(
                    '%s: customer ID %s',
                    $e->getMessage(),
                    $profile->getCustomerId()
                ),
                \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_CUSTOMER
            );
		}
        
		try {
            if(!$profile->getPaymentTokenId()) {
                throw new \Exception(sprintf('Empty token ID for profile ID %s', $profile->getId()));
            }
            
            $paymentToken = $this->paymentTokenFactory->create();
            $paymentToken->load($profile->getPaymentTokenId());
            
            if(!$paymentToken->getId()) {
                throw new \Exception(
                    sprintf(
                        'Payment token with ID %s does not exist for profile ID %s',
                        $profile->getPaymentTokenId(),
                        $profile->getId()
                    )
                );
            }
		} catch(\Exception $e) {
			throw new \Exception(
                sprintf(
                    '%s: token ID %s',
                    $e->getMessage(),
                    $profile->getPaymentTokenId()
                ),
                \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_PAYMENT_TOKEN
            );
		}
	}
	
    public function verifyStock(array $profiles) {
        foreach($profiles as $profile) {
            foreach($profile->getAllVisibleItems() as $_item) {
                /* @var \Magento\Catalog\Model\Product $product */
                $product = $this->productRepository->get($_item->getSku(), false, $profile->getStoreId(), true);
                
                if(!$product->isSalable() || !$this->stockState->checkQty($product->getId(), $_item->getQty())) {
                    $stockItem = ($product->getExtensionAttributes()) ? $product->getExtensionAttributes()->getStockItem() : null;
                    
                    throw new \Exception(
                        sprintf(
                            'Suspending profile ID %s due to out of stock - product # %s, qty is %s, manage_stock is %s',
                            $profile->getId(),
                            $product->getSku(),
                            ($stockItem ? $stockItem->getQty() : ''),
                            (($stockItem && $stockItem->getManageStock()) ? 'Yes' : 'No')
                        ),
                        \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_STOCK
                    );
                }
            }
        }
    }
    
    /**
     * Retrieve quote item by product id
     *
     * @param   \Magento\Quote\Model\Quote $quote
     * @param   \Magento\Catalog\Model\Product $product
     * @return  \Magento\Quote\Model\Quote\Item|bool
     */
    public function getItemByProduct($quote, $product) {
        foreach($quote->getAllItems() as $item) {
            if($this->representProduct($item, $product)) {
                return $item;
            }
        }
        
        return false;
    }
    
    /**
     * Check product representation in item
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @return  bool
     */
    public function representProduct($item, $product) {
        $itemProduct = $item->getProduct();
        
        if(!$product || $itemProduct->getId() != $product->getId()) {
            return false;
        }
        
        /**
         * Check maybe product is planned to be a child of some quote item - in this case we limit search
         * only within same parent item
         */
        $stickWithinParent = $product->getStickWithinParent();
        
        if($stickWithinParent) {
            if($item->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }
        
        // Check options
        $itemOptions = $item->getOptionsByCode();
        $productOptions = $product->getCustomOptions();
        
        if(!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        
        if(!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if two options array are identical
     * First options array is prerogative
     * Second options array checked against first one
     *
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    public function compareOptions($options1, $options2) {
        foreach($options1 as $option) {
            $code = $option->getCode();
            
            if(!in_array($code, $this->_representOptions)) {
                continue;
            }
            
            if(!isset($options2[$code])) {
                return false;
            }
            
            if(!isset($options2[$code]) || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        
        return true;
    }
    
    private function orderRelations(Profile $parent, $children, Order $order) {
        $this->profileResourceModel->addOrderRelation($parent, $order);
		
        foreach($children as $child) {
            $this->profileResourceModel->addOrderRelation($child, $order);
        }
		
        return $this;
    }
    
    private function checkOrderTotals(Order $order) {
        if($order->getBaseGrandTotal() == $order->getBaseTaxAmount() || $order->getBaseGrandTotal() == $order->getTaxAmount()) {
            throw new \Exception(
                sprintf('Wrong BaseGrandTotal: %s for Order  %s address', $order->getBaseGrandTotal(), $order->getIncrementId())
            );
        }
        
        return true;
    }
    
}
