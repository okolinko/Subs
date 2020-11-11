<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11/1/16
 * Time: 4:44 PM
 */

namespace Toppik\Subscriptions\Block\Cart\Update;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote;
use Toppik\Subscriptions\Helper\Data;
use Toppik\Subscriptions\Model\Preferences;
use Toppik\Subscriptions\Model\Settings\SubscriptionFactory;

class Form extends Template
{

    /**
     * @var Quote
     */
    private $quote;
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var Cart
     */
    private $cart;
    private $_products = [];
    private $_items = [];
    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;
    /**
     * @var Data
     */
    private $subscriptionHelper;
    /**
     * @var Image
     */
    private $imageHelper;

    public function __construct(
        Image $imageHelper,
        Data $subscriptionHelper,
        SubscriptionFactory $subscriptionFactory,
        Cart $cart,
        Session $checkoutSession,
        Template\Context $context,
        array $data = []
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        parent::__construct($context, $data);
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->imageHelper = $imageHelper;
    }

    public function getQuoteItems() {
        return $this->checkoutSession->getQuote()->getAllVisibleItems();
    }

    public function getFrequencyAttribute()
    {
        return Preferences::SUBSCRIPTION_OPTION_ID;
    }

    public function getItemsByProductId($id) {
        $items = array();

        if(!isset($this->_products[$id])) {
            /* @var \Toppik\Subscriptions\Model\Settings\Subscription $subscription */
            $subscription 			= $this->subscriptionFactory->create();
            try {
                $subscription->load($id, 'product_id');
            } catch (\Exception $e) {
                $subscription = false;
            }
            $this->_products[$id] 	= $subscription;
        }

        $subscription = $this->_products[$id];

        if($subscription && $subscription->getId()) {
            if(!isset($this->_items[$subscription->getId()])) {
                $this->_items[$subscription->getId()] = $this->subscriptionHelper
                    ->getSubscriptionItemCollectionForDisplayingOnStore($subscription);
            }

            $items = $this->_items[$subscription->getId()];
        }

        return $items;
    }

    public function getProductImage(Product $product) {
        return $this->imageHelper
            ->init($product, 'product_page_image_small')
            ->setImageFile($product->getImage())
            ->getUrl();
    }

}