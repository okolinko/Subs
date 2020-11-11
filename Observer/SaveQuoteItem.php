<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11/7/16
 * Time: 6:25 PM
 */

namespace Toppik\Subscriptions\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveQuoteItem implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $product->setJustCreatedQuoteItem($quoteItem);
    }
}