<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/21/16
 * Time: 2:50 PM
 */

namespace Toppik\Subscriptions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Toppik\Subscriptions\Helper\Quote as QuoteHelper;
use Toppik\Subscriptions\Helper\Data as SubscriptionHelper;

class QuoteCollectTotalsBefore implements ObserverInterface
{

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;
    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * QuoteCollectTotalsBefore constructor.
     * @param QuoteHelper $quoteHelper
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(QuoteHelper $quoteHelper, SubscriptionHelper $subscriptionHelper)
    {
        $this->quoteHelper = $quoteHelper;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * Update quantity
         */
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        foreach($quote->getItemsCollection() AS $item) {
            /* @var \Magento\Quote\Model\Quote\Item $item */
            if($item->getLinkedChildQuoteItem()) {
                $item->getLinkedChildQuoteItem()->setQty($item->getQty());
            }
        }

        /**
         * Update coupon code
         */
        $shouldUseCoupon = $this->quoteHelper->shouldApplyDiscount($quote);
        $couponCode = $this->subscriptionHelper->getCouponCode();
        
        if($shouldUseCoupon AND $couponCode) {
            $quote->setCouponCode($couponCode);
        } elseif(! $shouldUseCoupon AND $couponCode == $quote->getCouponCode()) {
            $quote->setCouponCode('');
        }

    }
}