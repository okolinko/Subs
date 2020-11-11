<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/19/16
 * Time: 7:46 PM
 */

namespace Toppik\Subscriptions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Toppik\Subscriptions\Model\Preferences;

class QuoteAddItem implements ObserverInterface
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
     * QuoteAddItem constructor.
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
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // TODO: Replace this dirty fix
        // Error was because this event was when creating subscriptions from console or cron
        if(! isset($_SERVER['REQUEST_METHOD'])) {
            return;
        }
        /* @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $observer->getQuoteItem();
        $product = $quoteItem->getProduct();
        if($this->subscriptionHelper->productHasSubscription($product)) {
            $subscription = $this->subscriptionHelper->getSubscriptionByProduct($product);
            $subscriptionTypeOption = $this->quoteHelper->getSubscriptionTypeOptionFromQuoteItem($quoteItem);
            $validSubscriptionOption = false;
            if(! $subscription->getIsSubscriptionOnly()) {
                if($subscriptionTypeOption === Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE) {
                    $validSubscriptionOption = true;
                }
            }
            $items = $subscription->getItemsCollection();
            foreach($items as $item) {
                /* @var \Toppik\Subscriptions\Model\Settings\Item $item */
                if($item->getId() == $subscriptionTypeOption) {
                    $validSubscriptionOption = true;
                    break;
                }
            }
            if(! $validSubscriptionOption) {
                throw new LocalizedException(__('Select delivery frequency to continue'));
            }
        }
    }
}