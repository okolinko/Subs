<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/11/16
 * Time: 3:26 PM
 */

namespace Toppik\Subscriptions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Toppik\Subscriptions\Helper\Gift;

class GiftItem implements ObserverInterface
{

    /**
     * @var Gift
     */
    private $giftHelper;

    /**
     * GiftItem constructor.
     * @param Gift $giftHelper
     */
    public function __construct(Gift $giftHelper)
    {
        $this->giftHelper = $giftHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /* @var Quote $quote */
        $quote = $observer->getQuote();
        /* @var Order $order */
        $order = $observer->getOrder();
        if($quote->getHasSubscription()) {
            $this->giftHelper->manageFreeGiftItem($order, $quote);
        }
    }
}