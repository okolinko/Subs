<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/19/16
 * Time: 2:01 PM
 */

namespace Toppik\Subscriptions\Observer;


use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Toppik\Subscriptions\Model\Preferences;
use Magento\Framework\Event;


class CatalogProductPrepareFullOptions implements ObserverInterface
{

    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    private $subscriptionHelper;
    /**
     * @var \Toppik\Subscriptions\Helper\Quote
     */
    private $quoteHelper;

    /**
     * CatalogProductPrepareFullOptions constructor.
     * @param \Toppik\Subscriptions\Helper\Data $subscriptionHelper
     * @param \Toppik\Subscriptions\Helper\Quote $quoteHelper
     */
    public function __construct(
        \Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        \Toppik\Subscriptions\Helper\Quote $quoteHelper
    )
    {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Event\Observer $observer)
    {
        /* @var Product $product */
        $product = $observer->getProduct();
        $transport = $observer->getTransport();
        $buyRequest = $observer->getBuyRequest();
        if(! $this->subscriptionHelper->productHasSubscription($product)) {
            return $this;
        }
        $subscriptionTypeOption = $this->quoteHelper->getSubscriptionTypeOptionFromBuyRequest($buyRequest);
        if($subscriptionTypeOption === false) {
            return $this;
        }
        $transport->options[Preferences::SUBSCRIPTION_OPTION_ID] = $subscriptionTypeOption;

        $buyRequestOptions = $buyRequest->getOptions();
        if(isset($buyRequestOptions[Preferences::LINKED_ITEM_OPTION_ID])) {
            $transport->options[Preferences::LINKED_ITEM_OPTION_ID] = $buyRequestOptions[Preferences::LINKED_ITEM_OPTION_ID];
        }

        return $this;
    }
}