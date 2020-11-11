<?php
namespace Toppik\Subscriptions\Processor;

class ProcessDrtvCs {
	
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
	
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
	
    /**
     * @var \Toppik\Subscriptions\Model\Settings\SubscriptionFactory
     */
    private $subscriptionFactory;
	
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
	
    /**
     * @var \Toppik\Subscriptions\Converter\QuoteToProfile
     */
    private $quoteToProfile;
	
    /**
     * @var ResourceConnection
     */
    private $resource;
	
    /**
     * @var ProductRepository
     */
    private $productRepository;
	
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
	
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
	
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;
	
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;
	
    /**
     * @var ManagerInterface
     */
    private $eventManager;
	
    /** @var RegionInterface */
    protected $regionData;
	
    /** @var CountryFactory */
    protected $countryFactory;
	
    /** @var RegionFactory */
    protected $regionFactory;
	
    /**
     * @var CustomerAddressInterfaceFactory
     */
    protected $customerAddressFactory;
	
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;
	
    protected $_storeManager;
    
    /**
     * ActiveProfiles constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Toppik\Subscriptions\Converter\QuoteToProfile $quoteToProfile
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Toppik\Subscriptions\Model\Settings\SubscriptionFactory $subscriptionFactory,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
		\Toppik\Subscriptions\Converter\QuoteToProfile $quoteToProfile,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Api\Data\RegionInterface $regionData,
		\Magento\Directory\Model\CountryFactory $countryFactory,
		\Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $customerAddressFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->objectManager = $objectManager;
        $this->dateTime = $dateTime;
        $this->subscriptionFactory = $subscriptionFactory;
		$this->reportHelper = $reportHelper;
		$this->quoteToProfile = $quoteToProfile;
        $this->resource = $resource;
        $this->productRepository = $productRepository;
		$this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
		$this->quoteFactory = $quoteFactory;
		$this->quoteRepository = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->regionData = $regionData;
		$this->countryFactory = $countryFactory;
		$this->regionFactory = $regionFactory;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->_storeManager = $storeManager;
		$this->dataObjectHelper = $dataObjectHelper;
    }
	
    public function execute() {
        if($this->reportHelper->isDRTVEnabled()) {
			try {
				$this->reportHelper->log("ProcessDrtvCs - > start", []);
				
                $this->updateTritonOrders();
                
				$processed = array();
				$order_ids = $this->getPendingQueue();
				
				$this->reportHelper->log(sprintf('%s Found %s order(s)', str_repeat('-', 5), count($order_ids)), []);
				
				foreach($order_ids as $_order_id) {
					if(in_array($_order_id, $processed)) {
						continue;
					}
					
					$this->_process($_order_id);
					
					$processed[] = $_order_id;
				}
				
				$this->reportHelper->log(sprintf('%s ProcessDrtvCs -> end', str_repeat('-', 10)));
			} catch(\Exception $e) {
				$message = sprintf('Error during processing subscription_drtv_cs: %s', $e->getMessage());
				
				$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
				
				$this->eventManager->dispatch(
					'toppikreport_system_add_message',
					['entity_type' => 'subscription_drtv_cs', 'entity_id' => null, 'message' => $message]
				);
			}
        }
    }
	
	protected function _process($order_id) {
        try {
			$customer = null;
			
			$this->reportHelper->log(sprintf(' %s> Start processing order ID %s <%s ', str_repeat('-', 10), $order_id, str_repeat('-', 10)), []);
			
			$count = 0;
			$order = $this->orderRepository->get($order_id);
			
			if($order->getId()) {
                $this->_storeManager->setCurrentStore($order->getStoreId());
                
				$customer = $this->customerRepository->getById($order->getCustomerId());
				
				if(!$customer->getId()) {
					throw new \Exception(sprintf('Customer with ID %s does not exist in order ID %s', $order->getCustomerId(), $order->getId()));
				}
				
				$this->updateById($order->getId());
				
				foreach($order->getAllVisibleItems() as $_item) {
					if(strpos($_item->getSku(), 'DRTV') !== false) {
						$this->reportHelper->log(sprintf('Found item ID %s and sku %s', $_item->getId(), $_item->getSku()), []);
						
						$subscriptions = $this->getSubscriptionBySku($_item->getSku());
						
						if(count($subscriptions)) {
							$quote = $this->createQuote($order);
							
							foreach($subscriptions as $_subscription) {
								$this->generateQuoteItems($quote, $_subscription);
							}
							
							$profiles = $this->quoteToProfile->process($quote, $order, []);
							
							$count++;
							
							$info = array();
							
							foreach($profiles as $_profile) {
                                $_profile->setOrigData();
								$_profile->scheduleNextOrder($order->getCreatedAt())->save();
								$info[] = sprintf('%s (%s - %s)', $_profile->getId(), $_profile->getSku(), $_profile->getFrequencyTitle());
							}
							
							$message = sprintf(
								'Created %s new profile(s) from item %s with IDs %s',
								count($profiles),
								$_item->getSku(),
								implode(', ', $info)
							);
							
							$this->reportHelper->log($message);
							
							$order->addStatusHistoryComment($message);
                            $order->setData('processed_drtv_cs', 1);
							$order->save();
							
							$quote->setIsActive(false);
							$this->quoteRepository->save($quote);
						} else {
							throw new \Exception(sprintf('Subscriptions not found for sku %s', $_item->getSku()));
						}
					}
				}
                
                $this->validateById($order->getId());
			}
			
			if($count < 1) {
				throw new \Exception(sprintf('No items found for order ID %s', $order_id));
			}
        } catch(\Exception $e) {
			$message = sprintf('Error during processing order ID %s: %s', $order_id, $e->getMessage());
			
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				[
					'entity_type' 		=> 'subscription_drtv_cs',
					'entity_id' 		=> $order_id,
					'message' 			=> $message,
					'amount' 			=> '',
					'customer_id' 		=> ($customer ? $customer->getId() : null),
					'customer_name' 	=> ($customer ? sprintf('%s %s', $customer->getFirstname(), $customer->getLastname()) : null),
					'customer_email' 	=> ($customer ? $customer->getEmail() : null),
					'customer_phone' 	=> $order->getBillingAddress()->getTelephone()
				]
			);
			
			if(isset($quote) && $quote instanceof \Magento\Quote\Model\Quote && $quote->getId()) {
				try {
					$quote->setIsActive(false);
					$this->quoteRepository->save($quote);
					$this->reportHelper->log(sprintf('Deactivated quote ID: %s', $quote->getId()), []);
				} catch(\Exception $e) {
					$this->reportHelper->log(
						sprintf('%s CANNOT deactivate quote ID %s: %s', str_repeat('=', 5), $quote->getId(), $e->getMessage()),
						[],
						\Toppik\Subscriptions\Logger\Logger::ERROR
					);
				}
			}
        }
	}
	
	public function getPendingQueue() {
		$collection = array();
		$connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        
        $sql = sprintf(
            'SELECT o.entity_id AS order_id, %s FROM %s AS oi
            INNER JOIN %s AS o ON o.entity_id = oi.order_id
            WHERE o.admin_id IS NOT NULL AND o.state IN("complete") AND oi.sku LIKE "%%DRTV%%" AND o.processed_drtv_cs = 0',
            $connection->quoteInto('? AS time_stamp', time()),
            $this->resource->getTableName('sales_order_item'),
            $this->resource->getTableName('sales_order')
        );
        
		$data = $connection->fetchAll($sql);
		
		if(count($data)) {
			foreach($data as $_item) {
				$collection[] = $_item['order_id'];
			}
		}
		
        $this->reportHelper->log(
            sprintf(
                'Preparing collection, query was: %s, items found: %s',
                preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "", $sql)),
                implode(', ', $collection)
            )
        );
        
		return $collection;
	}
	
	public function getSubscriptionBySku($sku, $required = true) {
		$collection = array();
		$connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        
		$data = $connection->fetchRow(
			sprintf(
				'SELECT * FROM %s WHERE %s',
				$this->resource->getTableName('subscriptions_sku_relations'),
                $connection->quoteInto('sku = ?', $sku)
			)
		);
		
		if($data && is_array($data) && isset($data['sku']) && isset($data['subscription_sku']) && isset($data['subscription_length'])) {
			$subscription_sku = explode(',', $data['subscription_sku']);
			
			if(count($subscription_sku)) {
				foreach($subscription_sku as $_sku) {
					$parent 	= null;
					$product 	= null;
					
					try {
						$product = $this->productRepository->get($_sku, false, null, true);
					} catch(\Exception $e) {
						throw new \Exception(sprintf('%s: product # %s', $e->getMessage(), $_sku));
					}
					
					if($product->getTypeId() != 'simple') {
						throw new \Exception(sprintf('Only simple products are allowed: %s - %s', $_sku, $product->getTypeId()));
					}
					
					$configurables = $this->objectManager
											->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
											->getParentIdsByChild($product->getId());
					
					if(isset($configurables[0])) {
						try {
							$parent = $this->productRepository->getById($configurables[0], false, null, true);
						} catch(\Exception $e) {
							throw new \Exception(sprintf('%s: product ID %s', $e->getMessage(), $configurables[0]));
						}
					}
					
					$subscription = $this->subscriptionFactory->create();
					
					if($parent !== null) {
						$subscription->load($parent->getId(), 'product_id');
					}
					
					if(!$subscription->getId()) {
						$subscription->load($product->getId(), 'product_id');
					}
					
					if(!$subscription->getId()) {
						throw new \Exception(sprintf('Invalid Subscription Product %s', $_sku));
					}
					
					$subscriptionItem = $subscription->addPeriodFilterToItemsCollection($data['subscription_length'])->getItemsCollection()->getFirstItem();
					
					if(!$subscriptionItem || !$subscriptionItem->getId()) {
						throw new \Exception(sprintf('Invalid Subscription Term: %s - %s', $_sku, $data['subscription_length']));
					}
					
					$collection[] = array('subscription_item' => $subscriptionItem, 'product' => $product, 'parent' => $parent);
				}
			} else {
				throw new \Exception(sprintf('Subscription data not valid for sku %s: %s', $sku, print_r($data['subscription_sku'], true)));
			}
		} else {
            if($required === true) {
                throw new \Exception(sprintf('Subscription data does not exist for sku %s', $sku));
            }
		}
		
		return $collection;
	}
	
	public function updateTritonOrders() {
        $admin_id = $this->reportHelper->getTritonAdminID();
        
        if($admin_id && $admin_id > 0) {
            $this->reportHelper->log(sprintf('Updating triton orders with admin ID %s', $admin_id));
            
            $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            
            $sql = sprintf(
                'UPDATE %s SET %s WHERE %s AND admin_id IS NULL',
                $this->resource->getTableName('sales_order'),
                $connection->quoteInto('admin_id = ?', $admin_id),
                $connection->quoteInto('merchant_source = ?', \Toppik\OrderSource\Model\Merchant\Source::SOURCE_2)
            );
            
            $result = $connection->query($sql);
            
            $this->reportHelper->log(
                sprintf('Updated triton orders with ID %s, query was: %s, result: %s', $admin_id, $sql, ($result ? $result->rowCount() : ''))
            );
        }
	}
	
	public function updateById($id) {
		if($id && (int) $id > 0) {
            $this->reportHelper->log(sprintf('Updating order ID %s', $id));
            
            $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            
            $sql = sprintf(
                'UPDATE %s SET processed_drtv_cs = 1 WHERE %s',
                $this->resource->getTableName('sales_order'),
                $connection->quoteInto('entity_id = ?', (int) $id)
            );
            
			$result = $connection->query($sql);
            $this->reportHelper->log(sprintf('Updated order ID %s, query was: %s, result: %s', $id, $sql, ($result ? $result->rowCount() : '')));
		}
	}
	
	public function validateById($id) {
		if($id && (int) $id > 0) {
            $this->reportHelper->log(sprintf('Validating order ID %s', $id));
            
            $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            
            $sql = sprintf(
                'SELECT processed_drtv_cs FROM %s WHERE %s',
                $this->resource->getTableName('sales_order'),
                $connection->quoteInto('entity_id = ?', (int) $id)
            );
            
			$data = (int) $connection->fetchOne($sql);
            
            $this->reportHelper->log(sprintf('Validated order ID %s, current flag is "%s", query was: %s', $id, $data, $sql));
            
            if($data !== 1) {
                $this->reportHelper->log(sprintf('INVALID data found for order ID %s, current flag is "%s", query was: %s', $id, $data, $sql));
                $this->updateById($id);
            }
		}
	}
	
    public function createQuote($order) {
		if(!$order->getCustomerId()) {
			throw new \Exception(sprintf('Order ID %s does not have customer id', $order->getId()));
		}
		
        $customer = $this->customerRepository->getById($order->getCustomerId());
		
		if(!$customer->getId()) {
			throw new \Exception(sprintf('Customer with ID %s does not exist in order ID %s', $order->getCustomerId(), $order->getId()));
		}
		
		$this->reportHelper->log(sprintf('createQuote -> Start processing order ID %s', $order->getId()));
		
        $quote = $this->quoteFactory->create();
		
        $quote->setIsSuperMode(true);
        $quote->setIsSuperModeForce(true);
        
        $quote->setCustomer($customer);
		$quote->setCustomerFirstname($customer->getFirstname());
		$quote->setCustomerLastname($customer->getLastname());
		$quote->setCustomerEmail($customer->getEmail());
		
        $quote->setCurrency();
        $quote->setStoreId($order->getStoreId());
		
        $quote->getItemsCollection()->removeAllItems();
        $quote->setInventoryProcessed(false);
		
        $quote->save();
		$this->quoteRepository->save($quote);
		
        $this->createQuoteAddress($quote->getShippingAddress(), $order->getShippingAddress()->getData());
        $this->createQuoteAddress($quote->getBillingAddress(), $order->getBillingAddress()->getData());
		
        $quote->getPayment()
            ->setMethod($order->getPayment()->getMethod())
            ->importData(['method' => $order->getPayment()->getMethod()]);
		
        $quote->getShippingAddress()->setPaymentMethod($order->getPayment()->getMethod());
        $quote->getShippingAddress()->setShippingMethod($order->getShippingMethod());
		
        $quote->save();
		$this->quoteRepository->save($quote);
		
        return $quote;
    }
	
    protected function createQuoteAddress($quoteAddress, $addressData) {
		$country_id = isset($addressData['country_id']) ? $addressData['country_id'] : null;
		$region_id 	= isset($addressData['region_id']) ? $addressData['region_id'] : null;
		
		$country = $this->countryFactory->create();
		$country->load($country_id);
		
		if(!$country->getId()) {
			throw new \Exception(
				sprintf('createQuoteAddress -> Country with ID %s does not exist for %s address', $country_id, $quoteAddress->getAddressType())
			);
		}
		
        if($region_id) {
            $region = $this->regionFactory->create();
            $region->load($region_id);
            
            if(!$region->getId()) {
                throw new \Exception(
                    sprintf('createQuoteAddress -> Region with ID %s does not exist for %s address', $region_id, $quoteAddress->getAddressType())
                );
            }
            
            $regionDataObject = clone $this->regionData;
            
            $this->dataObjectHelper->populateWithArray(
                $regionDataObject,
                $region->getData(),
                'Magento\Customer\Api\Data\RegionInterface'
            );
            
            $addressData['region'] = $regionDataObject;
        }
        
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
		
		$quoteAddress->setFirstname($quoteAddress->getQuote()->getCustomerFirstname());
		$quoteAddress->setLastname($quoteAddress->getQuote()->getCustomerLastname());
		
        $quoteAddress->unsetData('customer_address_id');
        $quoteAddress->unsetData('cached_items_all');
		
        return $this;
    }
	
    private function generateQuoteItems(\Magento\Quote\Model\Quote $quote, $subscription) {
		if(isset($subscription['subscription_item']) && isset($subscription['product'])) {
			$this->addItemToQuote($quote, $subscription, true);
		} else {
			throw new \Exception(
				sprintf(
					'generateQuoteItems -> Invalid data -> %s - %s - %s',
					isset($subscription['subscription_item']),
					isset($subscription['product']),
					isset($subscription['parent'])
				)
			);
		}
        
        $quote->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();
		
		$this->quoteRepository->save($quote);
		
		if(count($quote->getItemsCollection()) < 1) {
			throw new \Exception(sprintf('No items were added to quote ID %s (%s)', $quote->getId(), count($quote->getItemsCollection())));
		}
        
        return $this;
    }
	
    protected function addItemToQuote(\Magento\Quote\Model\Quote $quote, $subscription, $isSubscription = false){
        $simple 			= null;
        $product 			= $subscription['product'];
        $qty 				= 1;
        $price      		= $product->getPrice();
        $options 			= null;
		
        if(is_object($subscription['parent'])) {
            $simple 	= $product;
            $product 	= $subscription['parent'];
        }
		
        if($product->getTypeId() == 'configurable') {
            $found 			= false;
            $allProducts 	= $product->getTypeInstance()->getUsedProducts($product, null);
            $attributes 	= $product->getTypeInstance()->getConfigurableAttributes($product);
			
            foreach($allProducts as $_product) {
                if($simple->getId() == $_product->getId()) {
                    $found = true;
                    break;
                }
            }
			
            if($found !== true) {
                throw new \Exception(sprintf('Configurable product # %s does not have simple # %s', $product->getSku(), $simple->getSku()));
            }
			
            $options = array();
			
            foreach($attributes as $_attribute) {
                if(
                    $simple->hasData($_attribute->getProductAttribute()->getAttributeCode())
                    && $simple->getData($_attribute->getProductAttribute()->getAttributeCode())
                ) {
                    $options[$_attribute->getProductAttribute()->getId()] = $simple->getData($_attribute->getProductAttribute()->getAttributeCode());
                }
            }
        }
		
        $subscriptionOption = \Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE;
		
        if($isSubscription === true) {
			$subscriptionItem 	= $subscription['subscription_item'];
            $subscriptionOption = $subscriptionItem->getId();
            $price 				= $subscriptionItem->getRegularPrice();
        }
		
        $params = new \Magento\Framework\DataObject(array(
            'product' 						=> $product->getId(),
            'qty' 							=> $qty,
            'super_attribute' 				=> $options,
            'selected_configurable_option' 	=> '',
            'options' 						=> array(
                \Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_ID => $subscriptionOption
            )
        ));
		
		try {
			$resultItem = $quote->addProduct($product, $params);
            
			if(is_string($resultItem)) {
				throw new \Exception($resultItem);
			}
		} catch(\Exception $e) {
			$stockItem = ($product->getExtensionAttributes()) ? $product->getExtensionAttributes()->getStockItem() : null;
			
			throw new \Exception(
				sprintf(
					'%s - product # %s, qty is %s, manage_stock is %s',
					$e->getMessage(),
					$product->getSku(),
					($stockItem ? $stockItem->getQty() : ''),
					(($stockItem && $stockItem->getManageStock()) ? 'Yes' : 'No')
				)
			);
		}
		
		$tax_rate = null;
		$tax_per_lineitem = null;
		
        $rowTotal 			= $price * $qty;
        $rowTotalInclTax 	= $rowTotal + $tax_per_lineitem;
		$priceInclTax 		= $rowTotalInclTax / $qty;
		$rowWeight 			= $product->getWeight() * $qty;
        
        $resultItem
            ->setRowWeight($rowWeight)
			
            ->setPrice($price)
            ->setBasePrice($product->getPrice())
            ->setOriginalPrice($price)
            ->setBaseOriginalPrice($price)
            ->setCustomPrice($price)
            ->setOriginalCustomPrice($price)
			
            ->setTaxPercent($tax_rate)
            ->setTaxAmount($tax_per_lineitem)
            ->setBaseTaxAmount($tax_per_lineitem)
			
            ->setDiscountPercent(0)
            ->setDiscountAmount(0)
            ->setBaseDiscountAmount(0)
			
            ->setRowTotal($rowTotal)
            ->setBaseRowTotal($rowTotal)
			
            ->setPriceInclTax($priceInclTax)
            ->setBasePriceInclTax($priceInclTax)
			
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setBaseRowTotalInclTax($rowTotalInclTax)
			
			->save();
        
		$this->reportHelper->log(
			sprintf(
				' -> Added item to quote ID %s: id %s - sku %s - name %s - qty %s - price %s',
				$quote->getId(),
				$resultItem->getId(),
				$resultItem->getSku(),
				$resultItem->getName(),
				$resultItem->getQty(),
				$resultItem->getPrice()
			)
		);
    }
	
}
