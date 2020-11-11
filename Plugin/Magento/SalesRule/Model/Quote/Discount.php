<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/6/16
 * Time: 7:44 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\SalesRule\Model\Quote;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Discount {

    public function aroundCollect(
        Total\AbstractTotal $subtotal,
        callable $proceed,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Address\Total $total
    ) {
        $helper                             = \Magento\Framework\App\ObjectManager::getInstance()->get('Toppik\Subscriptions\Helper\Quote');
        
        $originalItems                      = $shippingAssignment->getItems();
        $originalItemsWithoutSubscription   = array_filter($originalItems, [$this, 'filterSubscriptionItems'], ARRAY_FILTER_USE_BOTH);
        
        $address                            = $shippingAssignment->getShipping()->getAddress();
        $oldSubtotal                        = $address->getSubtotal();
        $oldBaseSubtotal                    = $address->getBaseSubtotal();
        $oldQty                             = $address->getTotalQty();
        $oldWeight                          = $address->getWeight();
        
        foreach($originalItems as $item) {
            /* @var \Magento\Quote\Model\Quote\Item */
            if($item->getParentItem()) {
                continue;
            }
            
            if($helper->shouldApplySubscriptionPriceForQuoteItem($item)) {
                if(!$helper->shouldAddSubscriptionPriceForSubtotal($item)) {
                    $subtotal       = max(0, (bcsub($address->getSubtotal(), $item->getRowTotal(), 2)));
                    $baseSubtotal   = max(0, (bcsub($address->getBaseSubtotal(), $item->getRowTotal(), 2)));
                    
                    $qty            = $address->getTotalQty() - $item->getQty();
                    $weight         = $address->getWeight() - $item->getWeight();
                    
                    $address->setSubtotal($subtotal);
                    $address->setBaseSubtotal($baseSubtotal);
                    $address->setTotalQty($qty);
                    $address->setWeight($weight);
                }
            }
        }
        
        $shippingAssignment->setItems($originalItemsWithoutSubscription);
        
        $result = $proceed($quote, $shippingAssignment, $total);
        
        $address->setSubtotal($oldSubtotal);
        $address->setBaseSubtotal($oldBaseSubtotal);
        $address->setTotalQty($oldQty);
        $address->setWeight($oldWeight);
        
        $shippingAssignment->setItems($originalItems);
        
        return $result;
    }
    
    /**
     * @param AbstractItem $item
     * @return bool
     */
    public function filterSubscriptionItems(AbstractItem $item) {
        return ! $item->getLinkedChildQuoteItem();
    }
    
}
