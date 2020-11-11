<?php
namespace Toppik\Subscriptions\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ItemFactory;
use Symfony\Component\Config\Definition\Exception\Exception;

class Gift {
    
    const IS_ENABLED = 'subscriptions_settings/gift/enabled';

    const PRODUCT_SKU = 'subscriptions_settings/gift/sku';

    private $_allowedProductTypeIds = array(
        'simple',
    );

    /**
     * @var ProductInterface
     */
    private $_product;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * Gift constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resourceConnection
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resourceConnection,
        ItemFactory $itemFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->resourceConnection = $resourceConnection;
        $this->itemFactory = $itemFactory;
    }

    /**
     * @return bool
     */
    public function isEnabled() {
        return $this->scopeConfig->getValue(self::IS_ENABLED) and $this->getProduct();
    }

    /**
     * @return string
     */
    public function getSku() {
        return $this->scopeConfig->getValue(self::PRODUCT_SKU);
    }

    /**
     * @return bool|ProductInterface
     */
    public function getProduct() {
        if(is_null($this->_product)) {
            try {
                $sku = $this->getSku();
                $product = $this->productRepository->get($sku);
                $this->_product = $this->isAllowedProduct($product) ? $product : false;
            } catch (Exception $e) {
                $this->_product = false;
            }
        }
        return $this->_product;
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function isAllowedProduct(ProductInterface $product)
    {
        return in_array($product->getTypeId(), $this->_allowedProductTypeIds);
    }

    /**
     * @param int $customerId
     * @return bool
     */
    public function isFirstSubscriptionForCustomer($customerId) {
        $connection = $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $subscriptionsProfiles = $connection->getTableName('subscriptions_profiles');
        $subscriptionsProfilesOrders = $connection->getTableName('subscriptions_profiles_orders');
        return ! $connection->fetchOne(
            'SELECT `' . $subscriptionsProfilesOrders . '`.`order_id` 
                FROM `' . $subscriptionsProfiles . '`
                INNER JOIN `' . $subscriptionsProfilesOrders . '`
                  ON `' . $subscriptionsProfiles . '`.`profile_id` = `' . $subscriptionsProfilesOrders . '`.`profile_id`
                WHERE `' . $subscriptionsProfiles . '`.`customer_id` = :customer_id
                LIMIT 1',
            ['customer_id' => $customerId, ]
        );
    }

    /**
     * @param Order $order
     */
    public function manageFreeGiftItem(Order $order, $quote) {
        if($quote->getSkipFreeGift() === true) {
            return $this;
        }
        
        if(!$this->isEnabled()) {
            return;
        }
        
        if(!$this->isFirstSubscriptionForCustomer($order->getCustomerId())) {
            return;
        }
        
        $product = $this->getProduct();
        
        /* @var Order\Item $item */
        $item = $this->itemFactory->create();
        
        $item
            ->setProductId($product->getId())
            ->setStoreId($order->getStoreId())
            ->setSku($product->getSku())
            ->setName($product->getName())
            ->setWeight($product->getWeight())
            ->setPrice(0)
            ->setBasePrice(0)
            ->setOriginalPrice(0)
            ->setBaseOriginalPrice(0)
            ->setTaxAmount(0)
            ->setBaseTaxAmount(0)
            ->setDiscountAmount(0)
            ->setBaseDiscountAmount(0)
            ->setRowTotal(0)
            ->setBaseRowTotal(0)
            ->setPriceInclTax(0)
            ->setBasePriceInclTax(0)
            ->setRowTotalInclTax(0)
            ->setBaseRowTotalInclTax(0)
            ->setQtyOrdered(1)
            ->setTaxPercent(0)
            ->setDiscountPercent(0);
        $order
            ->setTotalItemCount($order->getTotalItemCount() + $item->getQtyOrdered())
            ->setTotalQtyOrdered($order->getTotalQtyOrdered() + $item->getQtyOrdered())
            ->setBaseTotalQtyOrdered($order->getBaseTotalQtyOrdered() + $item->getQtyOrdered())
            ->addItem($item);
    }
    
}
