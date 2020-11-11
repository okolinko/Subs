<?php
namespace Toppik\Subscriptions\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order;
use Toppik\Subscriptions\Model\Settings\Subscription;
use Toppik\Subscriptions\Model\Settings\SubscriptionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    
    const ATTRIBUTE_GROUP   = 'subscriptions_group_id';
    const ATTRIBUTE_USE     = 'subscriptions_use';
    
    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    private $productSubscriptions = [];

    private $subscriptions = [];
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    private $quoteSession;

    private $state;

    /**
     * Data constructor.
     * @param ResourceConnection $resourceConnection
     * @param SubscriptionFactory $subscriptionFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        SubscriptionFactory $subscriptionFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $state,
        \Magento\Backend\Model\Session\Quote $quoteSession
    )
    {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->quoteSession = $quoteSession;
        $this->state = $state;
    }

    public function getCouponCode() {
        return $this->scopeConfig->getValue('subscriptions_settings/promotions/coupon_code');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function productHasSubscription(\Magento\Catalog\Model\Product $product) {
        $this->loadSubscriptionByProduct($product);
        if($this->productSubscriptions[$product->getId()]) {
            return true;
        }
        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return Subscription|bool
     */
    public function getSubscriptionByProduct(\Magento\Catalog\Model\Product $product) {
        $this->loadSubscriptionByProduct($product);
        return $this->productSubscriptions[$product->getId()];
    }

    private function loadSubscriptionByProduct(\Magento\Catalog\Model\Product $product)
    {
        if(! isset($this->productSubscriptions[$product->getId()])) {
          $storeId = $this->storeManager->getStore()->getId();
          
          if($this->scopeConfig->getValue('subscriptions_settings/general_options/enable_multistore_mode')) {
              if($this->state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                  if($this->quoteSession->getQuote() && $this->quoteSession->getQuote()->getId() && $this->quoteSession->getQuote()->getStoreId()) {
                      $storeId = $this->quoteSession->getQuote()->getStoreId();
                  } else if($product->hasStoreId() && $product->getStoreId()) {
                      $storeId = $product->getStoreId();
                  }
              } else {
                  $storeId = $this->storeManager->getStore()->getId();
              }
          } else {
              $storeId = $this->scopeConfig->getValue('subscriptions_settings/general_options/default_store');
          }
          
            /* @var Subscription $subscription */
          $subscription = $this->subscriptionFactory->create();
          $subscriptions = $subscription->getCollection()
              ->addFieldToFilter('product_id', $product->getId())
              ->addFieldToFilter('store_id', $storeId );
          if ($subscriptions->getSize()) {

              $subscription = $subscriptions->getFirstItem();

              if($subscription->getId()) {
                  $this->productSubscriptions[$product->getId()] = $subscription;
                  $this->subscriptions[$subscription->getId()] = $subscription;
              }
          } else {
              $this->productSubscriptions[$product->getId()] = false;
          }

          // $subscription = $this->subscriptionFactory->create();
          //   $subscription->load($product->getId(), 'product_id');
          //   if($subscription->getId()) {
          //       $this->productSubscriptions[$product->getId()] = $subscription;
          //       $this->subscriptions[$subscription->getId()] = $subscription;
          //   } else {
          //       $this->productSubscriptions[$product->getId()] = false;
          //   }
        }
        return $this->productSubscriptions[$product->getId()];
    }
    
    public function getIsCustomerMode() {
        return (bool) $this->scopeConfig->getValue('subscriptions_settings/general_options/customer_mode', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getId());
    }
    
    public function getIsCancelMode() {
        return (bool) $this->scopeConfig->getValue('subscriptions_settings/general_options/cancel_mode', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getId());
    }
    
    public function getIsRemoveMode() {
        return (bool) $this->scopeConfig->getValue('subscriptions_settings/general_options/remove_mode', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getId());
    }
    
    public function getIsChangeMode() {
        return (bool) $this->scopeConfig->getValue('subscriptions_settings/general_options/change_mode', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getId());
    }
    
    public function getIsChangeModeFull() {
        return (bool) $this->scopeConfig->getValue('subscriptions_settings/general_options/change_mode_full', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->storeManager->getStore()->getId());
    }
    
    public function useFreeShipping() {
        return true;
    }

    /**
     * @param Order $order
     * @return int
     */
    public function profileHasOrderByOrder(Order $order) {
        $connection = $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $subscriptionsProfilesOrders = $connection->getTableName('subscriptions_profiles_orders');
        $profileId = $connection->fetchOne(
            'SELECT `po`.`profile_id`
                FROM `' . $subscriptionsProfilesOrders . '` as `po`
                WHERE `po`.`order_id` = :order_id
                LIMIT 1',
            ['order_id' => $order->getId()]
        );
        if($profileId) {
            $totalOrders = (int) $connection->fetchOne(
                'SELECT COUNT(*)
                    FROM `' . $subscriptionsProfilesOrders . '` as `po`
                    WHERE `po`.`profile_id` = :profile_id',
                ['profile_id' => $profileId]
            );
            if($totalOrders > 1) {
                return false;
            }
        }
        return true;
    }

    public function getSubscriptionItemCollectionForDisplayingOnStore(Subscription $subscription, $store = null)
    {
        return $subscription->getItemsCollection();
    }

    /**
     * Get store id
     *
     * @return  int
    */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

}
