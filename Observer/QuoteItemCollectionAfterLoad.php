<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/19/16
 * Time: 2:33 PM
 */

namespace Toppik\Subscriptions\Observer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\ObserverInterface;
use Toppik\Subscriptions\Model\Preferences;

class QuoteItemCollectionAfterLoad implements ObserverInterface
{

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    private $subscriptionHelper;
    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    private $optionFactory;
    /**
     * @var \Magento\Catalog\Model\Product\Option\ValueFactory
     */
    private $valueFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Toppik\Subscriptions\Helper\Product
     */
    private $productHelper;

    /**
     * QuoteItemCollectionAfterLoad constructor.
     * @param CheckoutSession $checkoutSession
     * @param \Toppik\Subscriptions\Helper\Data $subscriptionHelper
     * @param \Toppik\Subscriptions\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param \Magento\Catalog\Model\Product\Option\ValueFactory $valueFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        \Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        \Toppik\Subscriptions\Helper\Product $productHelper,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Catalog\Model\Product\Option\ValueFactory $valueFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->optionFactory = $optionFactory;
        $this->valueFactory = $valueFactory;
        $this->storeManager = $storeManager;
        $this->productHelper = $productHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Event\Observer $observer)
    {
        /* @var Collection $productCollection */
        $productCollection = $observer->getCollection();
        foreach($productCollection as $product) {
            /* @var \Magento\Catalog\Model\Product $product */
            if($this->subscriptionHelper->productHasSubscription($product)) {
                $option = $this->productHelper->getSubscriptionTypeProductOption($product, Preferences::SUBSCRIPTION_CART_LABEL);
                $product->addOption($option);
                $product->setHasOptions(true);
                $product->setRequiredOptions(true);
            }
        }
    }
}