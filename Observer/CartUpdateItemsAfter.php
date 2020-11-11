<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/21/16
 * Time: 2:46 PM
 */

namespace Toppik\Subscriptions\Observer;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event;

class CartUpdateItemsAfter implements ObserverInterface
{

    /**
     * @param Event\Observer $observer
     * @return void
     */
    public function execute(Event\Observer $observer)
    {
        /* @var Cart $cart */
        $cart = $observer->getCart();
        foreach($cart->getQuote()->getItemsCollection() AS $item) {
            /* @var \Magento\Quote\Model\Quote\Item $item */
            if($item->getLinkedChildQuoteItem()) {
                $item->getLinkedChildQuoteItem()->setQty($item->getQty());
            }
        }
    }
}