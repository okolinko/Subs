<?php
namespace Toppik\Subscriptions\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends Command {
    
    protected $_productMap = array(
        72 => 201,
        130 => 597,
        131 => 597,
        252 => 600
    );
    
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var ManagerInterface
     */
    private $eventManager;
	
    /**
     * @var ResourceConnection
     */
    private $resource;
	
    /**
     * @var State
     */
    private $state;
    
    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    
    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;
	
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;
    
    private $success = array();
    private $error = array();
    private $info = array();
    
    private $product_data;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->objectManager = $objectManager;
        $this->dateTime = $dateTime;
        $this->eventManager = $eventManager;
        $this->resource = $resource;
        $this->state = $state;
        $this->productRepository = $productRepository;
        $this->_filesystem = $filesystem;
        $this->directoryList = $directoryList;
        parent::__construct();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('subscriptions:migrate');
        $this->setDescription('Migrate');
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->info[] = sprintf(
            '%s::%s() %s, memory %s',
            __CLASS__,
            __FUNCTION__,
            date(DATE_RFC2822, time()),
            $this->_getMemoryUsage()
        );
        
		$start = time();
        
        $this->state->setAreaCode('frontend');
        
        $items = $this->_getItems();
        
        $this->info[] = sprintf("Found %s item(s)", count($items));
        
        foreach($items as $_id) {
            try {
                $updated = false;
                
                $profile = $this->objectManager->create('Toppik\Subscriptions\Model\Profile');
                
                $profile->load($_id);
                
                if(!$profile->getId()) {
                    throw new \Exception(sprintf('Profile with ID %s does not exist', $_id));
                }
                
                if($this->_migrateBillingAddress($profile) === true) {
                    $this->success[] = sprintf('%s Profile ID %s -> Updating Billing Address', str_repeat('-', 2), $profile->getId());
                    $updated = true;
                }
                
                if($this->_migrateShippingAddress($profile) === true) {
                    $this->success[] = sprintf('%s Profile ID %s -> Updating Shipping Address', str_repeat('-', 2), $profile->getId());
                    $updated = true;
                }
                
                if($this->_migrateItems($profile) === true) {
                    $this->success[] = sprintf('%s Profile ID %s -> Updating Items', str_repeat('-', 2), $profile->getId());
                    $updated = true;
                }
                
                if($this->_aggregate($profile) === true) {
                    $this->success[] = sprintf('%s Profile ID %s -> Updating Aggregations', str_repeat('-', 2), $profile->getId());
                    $updated = true;
                }
                
                if($updated === true) {
                    $profile->save();
                    $this->success[] = sprintf('Profile ID %s has been updated', $profile->getId());
                }
            } catch(\Exception $e) {
                $this->error[] = sprintf(
                    '%s Error during processing profile ID %s (%s): %s',
                    str_repeat('=', 5),
                    $profile->getId(),
                    $profile->getStatus(),
                    $e->getMessage()
                );
            }
        }
        
        $this->info[] = sprintf(
            '----- Finish %s::%s() %s, memory %s, time %s',
            __CLASS__,
            __FUNCTION__,
            date(DATE_RFC2822, time()),
            $this->_getMemoryUsage(),
            time() - $start
        );
        
        //file_put_contents(BP . '/var/log/subscriptions-migrate-info.log', implode("\n", $this->info), FILE_APPEND | LOCK_EX);
        //file_put_contents(BP . '/var/log/subscriptions-migrate-success.log', implode("\n", $this->success), FILE_APPEND | LOCK_EX);
        //file_put_contents(BP . '/var/log/subscriptions-migrate-error.log', implode("\n", $this->error), FILE_APPEND | LOCK_EX);
        
        $output->write("The operation has been completed. Please check log files.\n");
    }
    
	protected function _getItems() {
		$collection = array();
		
		$data = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)->fetchAll(
			sprintf(
				'SELECT profile_id FROM %s',
				$this->resource->getTableName('subscriptions_profiles')
			)
		);
		
		if(count($data)) {
			foreach($data as $_item) {
                if(isset($_item['profile_id'])) {
                    $collection[] = $_item['profile_id'];
                }
			}
		}
		
		return $collection;
	}
    
    protected function _migrateBillingAddress($profile) {
        if(
            $profile->getBillingAddress()->getId()
            && $profile->getBillingAddress()->getEmail()
            && $profile->getBillingAddress()->getStreet()
            && $profile->getBillingAddress()->getCity()
            && $profile->getBillingAddress()->getRegion()
            && $profile->getBillingAddress()->getPostcode()
            && $profile->getBillingAddress()->getCountryId()
            && $profile->getBillingAddress()->getTelephone()
        ) {
            return false;
        }
        
        $json_object = new \Magento\Framework\DataObject(\Zend\Json\Json::decode($profile->getBillingAddressJson(), \Zend\Json\Json::TYPE_ARRAY));
        
        if(!$json_object->getPostcode()) {
            $json_object->setPostcode('00000');
        }
        
        if(!$json_object->getTelephone()) {
            $json_object->setTelephone('00000000');
        }
        
        if(
            !$json_object->getStreet()
            || !$json_object->getCity()
            || !$json_object->getPostcode()
            || !$json_object->getCountryId()
            || !$json_object->getTelephone()
        ) {
            throw new \Exception(
                sprintf(
                    'Billing address JSON not found - street: %s, city: %s, region: %s, postcode: %s, country_id: %s, telephone: %s',
                    $json_object->getStreet(),
                    $json_object->getCity(),
                    $json_object->getRegion(),
                    $json_object->getPostcode(),
                    $json_object->getCountryId(),
                    $json_object->getTelephone()
                )
            );
        }
        
        $json_object
            ->setId(null)
            ->setAddressId(null)
            
            ->setEmail(($json_object->getEmail() ? $json_object->getEmail() : $profile->getCustomer()->getEmail()))
            
            ->setProfile($profile)
            ->setProfileId($profile->getId());
        
        $profile->setBillingAddress($json_object);
        
        if($profile->getBillingAddress()->getId()) {
            throw new \Exception(sprintf('Found billing address ID %s', $profile->getBillingAddress()->getId()));
        }
        
        return true;
    }
    
    protected function _migrateShippingAddress($profile) {
        if(
            $profile->getShippingAddress()->getId()
            && $profile->getShippingAddress()->getEmail()
            && $profile->getShippingAddress()->getStreet()
            && $profile->getShippingAddress()->getCity()
            && $profile->getShippingAddress()->getRegion()
            && $profile->getShippingAddress()->getPostcode()
            && $profile->getShippingAddress()->getCountryId()
            && $profile->getShippingAddress()->getTelephone()
            && $profile->getShippingAddress()->getShippingMethod()
            && $profile->getShippingAddress()->getPaymentMethod()
        ) {
            return false;
        }
        
        $json_object = new \Magento\Framework\DataObject(\Zend\Json\Json::decode($profile->getShippingAddressJson(), \Zend\Json\Json::TYPE_ARRAY));
        
        if(!$json_object->getTelephone()) {
            $json_object->setTelephone('00000000');
        }
        
        if(!$json_object->getPaymentMethod()) {
            $json_object->setPaymentMethod('braintree_cc_vault');
        }
        
        if(
            !$json_object->getStreet()
            || !$json_object->getCity()
            || !$json_object->getPostcode()
            || !$json_object->getCountryId()
            || !$json_object->getTelephone()
            || !$json_object->getShippingMethod()
            || !$json_object->getPaymentMethod()
        ) {
            throw new \Exception(
                sprintf(
                    'Shipping address JSON not found - street: %s, city: %s, region: %s, postcode: %s, country: %s, telephone: %s, shipping: %s, payment: %s',
                    $json_object->getStreet(),
                    $json_object->getCity(),
                    $json_object->getRegion(),
                    $json_object->getPostcode(),
                    $json_object->getCountryId(),
                    $json_object->getTelephone(),
                    $json_object->getShippingMethod(),
                    $json_object->getPaymentMethod()
                )
            );
        }
        
        $json_object
            ->setId(null)
            ->setAddressId(null)
            
            ->setEmail(($json_object->getEmail() ? $json_object->getEmail() : $profile->getCustomer()->getEmail()))
            
            ->setProfile($profile)
            ->setProfileId($profile->getId())
            
            ->setBaseShippingAmount(0)
            ->setShippingAmount(0)
            ->setBaseShippingTaxAmount(0)
            ->setShippingTaxAmount(0)
            ->setBaseShippingDiscountAmount(0)
            ->setShippingDiscountAmount(0)
            ->setShippingInclTax(0)
            ->setBaseShippingInclTax(0)
            ->setShippingTaxCalculationAmount(0)
            ->setBaseShippingTaxCalculationAmount(0)
            ->setItemsAppliedTaxes(false)
            ->setAppliedTaxes(false)
            ->setTaxAmount(0)
            ->setBaseTaxAmount(0)
            ->setGrandTotal(0)
            ->setBaseGrandTotal(0)
            ->setBaseSubtotalInclTax(0)
            ->setBaseSubtotalTotalInclTax(0)
            ->setSubtotalInclTax(0);
        
        $profile->setShippingAddress($json_object);
        
        if($profile->getShippingAddress()->getId()) {
            throw new \Exception(sprintf('Found shipping address ID %s', $profile->getShippingAddress()->getId()));
        }
        
        return true;
    }
    
    protected function _migrateItems($profile) {
        if(count($profile->getAllVisibleItems()) > 0) {
            return false;
        }
        
        $json_object = new \Magento\Framework\DataObject(\Zend\Json\Json::decode($profile->getItemsJson(), \Zend\Json\Json::TYPE_ARRAY));
        
        if($json_object->getBasePrice() != $json_object->getPrice()) {
            $json_object->setBasePrice($json_object->getPrice());
        }
        
        if($json_object->getBaseRowTotal() != $json_object->getRowTotal()) {
            $json_object->setBaseRowTotal($json_object->getRowTotal());
        }
        
        if(
            !$json_object->getProductId()
            || !$json_object->getProductType()
            || !$json_object->getSku()
            || !$json_object->getName()
            || !$json_object->getQty()
            || !$json_object->getPrice()
            || !$json_object->getBasePrice()
            || !$json_object->getRowTotal()
            || !$json_object->getBaseRowTotal()
        ) {
            throw new \Exception(
                sprintf(
                    'Items JSON not found - product: %s, type: %s, sku: %s, name: %s, qty: %s, price: %s, base_price: %s, total: %s, base_total: %s',
                    $json_object->getProductId(),
                    $json_object->getProductType(),
                    $json_object->getSku(),
                    $json_object->getName(),
                    $json_object->getQty(),
                    $json_object->getPrice(),
                    $json_object->getBasePrice(),
                    $json_object->getRowTotal(),
                    $json_object->getBaseRowTotal()
                )
            );
        }
        
        $quote_item = clone $json_object;
        
        if(isset($this->_productMap[$quote_item->getProductId()]) && $this->_productMap[$quote_item->getProductId()] >= 1) {
            $quote_item->setProductId($this->_productMap[$quote_item->getProductId()]);
        }
        
        try {
            $configurable = $this->productRepository->getById(
                $quote_item->getProductId(),
                false,
                $quote_item->getData('store_id')
            );
            
            if($configurable->getTypeId() != $quote_item->getProductType()) {
                $quote_item->setProductType($configurable->getTypeId());
            }
            
            if($quote_item->getProductType() == 'simple') {
                $quote_item->setHasChildren(false);
            }
        } catch(\Exception $e) {
            throw new \Exception(sprintf('%s: product ID %s', $e->getMessage(), $quote_item->getProductId()));
        }
        
        $quote_item->setProduct($configurable);
        
        if($quote_item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            if(!$quote_item->getHasChildren()) {
                $quote_item->setHasChildren(true);
            }
            
            if(!is_array($quote_item->getChildren()) || count($quote_item->getChildren()) < 1) {
                $this->info[] = sprintf("--------------- Profile ID %s: Fix Configurable item # %s", $profile->getId(), $quote_item->getSku());
                
                $attributes = array();
                
                if($quote_item->getProductOptions()) {
                    $productOptions = $quote_item->getProductOptions();
                    $attributes     = isset($productOptions['info_buyRequest']) && isset($productOptions['info_buyRequest']['super_attribute']) ? $productOptions['info_buyRequest']['super_attribute'] : array();
                }
                
                if(count($attributes) < 1) {
                    $buy_request = $quote_item->getData('info_buyRequest');
                    
                    if($buy_request) {
                        $buy_request = unserialize($buy_request);
                    }
                }
                
                if(count($attributes) < 1) {
                    throw new \Exception('Attributes not found');
                }
                
                $simple_product = $this->getProductByAttributes($attributes, $configurable);
                
                if($simple_product === null) {
                    $product_data = $this->getProductDataByProfile($profile->getId());
                    
                    if($product_data !== null && isset($product_data['simple']) && (int) $product_data['simple'] >= 1) {
                        try {
                            $simple_product = $this->productRepository->getById((int) $product_data['simple']);
                        } catch(\Exception $e) {
                            throw new \Exception(sprintf('%s: product ID %s', $e->getMessage(), (int) $product_data['simple']));
                        }
                    }
                }
                
                if($simple_product === null) {
                    throw new \Exception(   
                        sprintf('Simple product not found for configurable product ID %s from %s', $configurable->getId(), print_r($attributes, true))
                    );
                }
                
                $quote_item->setChildren(
                    [
                        array(
                            'parent_item_id' => $quote_item->getItemId(),
                            'product_type' => $simple_product->getTypeId(),
                            'sku' => $simple_product->getSku(),
                            'name' => $simple_product->getName(),
                            'product_id' => $simple_product->getId(),
                            'store_id' => $quote_item->getData('store_id'),
                            'qty' => $quote_item->getData('qty'),
                            'tax_class_id' => $quote_item->getData('tax_class_id'),
                            'price' => 0,
                            'base_price' => 0
                        )
                    ]
                );
                
                if(!is_array($quote_item->getOptions()) || count($quote_item->getOptions()) < 1) {
                    $option1 = $this->objectManager->create('Magento\Quote\Model\Quote\Item\OptionFactory')->create();
                    $option2 = $this->objectManager->create('Magento\Quote\Model\Quote\Item\OptionFactory')->create();
                    
                    $option1->setData(array('item_id' => $quote_item->getItemId(), 'code' => 'attributes', 'value' => serialize($attributes), 'product_id' => $configurable->getId()));
                    
                    $option2->setData(array('item_id' => $quote_item->getItemId(), 'code' => 'simple_product', 'value' => $simple_product->getId(), 'product_id' => $simple_product->getId()));
                    
                    $option2->setProduct($simple_product);
                    
                    $quote_item->setOptions([$option1->getData(), $option2->getData()]);
                }
            }
        }
        
        $item_options = array();
        
        if(is_array($quote_item->getOptions())) {
            foreach($quote_item->getOptions() as $option) {
                $item_options[] = $option;
            }
        }
        
        $newQuoteItem = clone $quote_item;
        
        $newQuoteItem
            ->setId(null)
            ->setItemId(null)
            ->setParentItemId(null)
            ->setQuoteItemId($quote_item->getItemId())
            ->setItemOptions((count($item_options) ? serialize($item_options) : null));
        
        $profileItem = $profile->setItem($newQuoteItem);
        
        if($quote_item->getHasChildren()) {
            foreach($quote_item->getChildren() as $_childQuoteItem) {
                $_childQuoteItem = new \Magento\Framework\DataObject($_childQuoteItem);
                
                if(
                    !$_childQuoteItem->getProductId()
                    || !$_childQuoteItem->getProductType()
                    || !$_childQuoteItem->getSku()
                    || !$_childQuoteItem->getName()
                    || !$_childQuoteItem->getQty()
                ) {
                    throw new \Exception('Child item JSON not found');
                }
                
                $item_options = array();
                
                if(is_array($_childQuoteItem->getOptions())) {
                    foreach($_childQuoteItem->getOptions() as $option) {
                        $item_options[] = $option;
                    }
                }
                
                $newChildQuoteItem = clone $_childQuoteItem;
                
                $newChildQuoteItem
                    ->setId(null)
                    ->setItemId(null)
                    ->setParentItemId(null)
                    ->setQuoteItemId($_childQuoteItem->getItemId())
                    ->setItemOptions((count($item_options) ? serialize($item_options) : null));
                
                $childProfileItem = $profile->setItem($newChildQuoteItem);
                $childProfileItem->setParentItem($profileItem);
            }
        }
        
        return true;
    }
    
    protected function _aggregate($profile) {
        $updated = false;
        
        if(count($profile->getAllVisibleItems()) > 0) {
            $grand_total        = 0;
            $base_ground_total  = 0;
            $items_count        = 0;
            $items_qty          = 0;
            
            foreach($profile->getAllVisibleItems() as $_item) {
                $grand_total        = $grand_total + $_item->getData('row_total');
                $base_ground_total  = $base_ground_total + $_item->getData('base_row_total');
                $items_count        = $items_count + 1;
                $items_qty          = $items_qty + $_item->getQty();
            }
            
            if($profile->getGrandTotal() != $grand_total) {
                $profile->setGrandTotal($grand_total);
                $updated = true;
            }
            
            if($profile->getBaseGrandTotal() != $base_ground_total) {
                $profile->setBaseGrandTotal($base_ground_total);
                $updated = true;
            }
            
            if($profile->getItemsCount() != $items_count) {
                $profile->setItemsCount($items_count);
                $updated = true;
            }
            
            if($profile->getItemsQty() != $items_qty) {
                $profile->setItemsQty($items_qty);
                $updated = true;
            }
        }
        
        return $updated;
    }
    
    /**
     * Retrieve used product by attribute values
     *  $attributes = array(
     *      $attributeId => $attributeValue
     *  )
     *
     * @param  array $attributes
     * @param  \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProductByAttributes($attributes, $product) {
        if($attributes) {
            if(is_array($attributes)) {
                foreach($attributes as $key => $val) {
                    if(empty($val)) {
                        unset($attributes[$key]);
                    }
                }
            } else {
                $attributes = [];
            }
            
            $subProduct = true;
            
            foreach($product->getTypeInstance()->getConfigurableAttributes($product) as $attributeItem) {
                /* @var $attributeItem \Magento\Framework\DataObject */
                $attrId = $attributeItem->getData('attribute_id');
                
                if(!isset($attributes[$attrId]) || empty($attributes[$attrId])) {
                    $subProduct = null;
                    break;
                }
            }
            
            if($subProduct) {
                $productCollection = $product->getTypeInstance()->getUsedProductCollection($product)->addAttributeToSelect('name');
                
                foreach($attributes as $attributeId => $attributeValue) {
                    $productCollection->addAttributeToFilter($attributeId, $attributeValue);
                }
                
                /** @var \Magento\Catalog\Model\Product $productObject */
                $productObject = $productCollection->getFirstItem();
                $productLinkFieldId = $productObject->getId();
                
                if($productLinkFieldId) {
                    try {
                        $simple = $this->productRepository->getById($productLinkFieldId);
                        return $simple;
                    } catch(\Exception $e) {
                        throw new \Exception(sprintf('%s: product ID %s', $e->getMessage(), $productLinkFieldId));
                    }
                }
            }
        }
        
        return null;
    }
    
    public function getProductDataByProfile($profile_id) {
        if($this->product_data === null) {
            $this->product_data = array();
            
            try {
                $csvFile = 'subscriptions_products.csv';
                $tmpDirectory = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
                $stream = $tmpDirectory->openFile($csvFile);
                
                while(false !== ($csvLine = $stream->readCsv())) {
                    if(empty($csvLine)) {
                        continue;
                    }
                    
                    $_profile_id         = array_shift($csvLine);
                    $_configurable_id    = array_shift($csvLine);
                    $_simple_id          = array_shift($csvLine);
                    
                    if($_profile_id && $_configurable_id && $_simple_id) {
                        $this->product_data[$_profile_id] = array('configurable' => $_configurable_id, 'simple' => $_simple_id);
                    }
                }
                
                $stream->close();
            } catch(\Exception $e) {
                if(isset($stream)) {
                    $stream->close();
                }
                
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while generating data: %1', $e->getMessage())
                );
            }
        }
        
        return isset($this->product_data[$profile_id]) ? $this->product_data[$profile_id] : null;
    }
    
	protected function _getMemoryUsage() {
		return sprintf('%s MB', number_format(memory_get_usage(true) / 1048576, 2));
	}
	
}
