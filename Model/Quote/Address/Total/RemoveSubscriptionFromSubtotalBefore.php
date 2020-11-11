<?php
namespace Toppik\Subscriptions\Model\Quote\Address\Total;

class RemoveSubscriptionFromSubtotalBefore extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal {
    
    /**
     * @var \Toppik\Subscriptions\Helper\Quote
     */
    private $quoteHelper;
    
    /**
     * RemoveSubscriptionFromSubtotalBefore constructor.
     * @param \Toppik\Subscriptions\Helper\Quote $quoteHelper
     */
    public function __construct(
        \Toppik\Subscriptions\Helper\Quote $quoteHelper
    ) {
        $this->quoteHelper = $quoteHelper;
    }
    
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $items = $shippingAssignment->getItems();
        /* @var \Magento\Quote\Model\Quote\Address */
        $address = $shippingAssignment->getShipping()->getAddress();
        
        foreach($items as $item) {
            /* @var \Magento\Quote\Model\Quote\Item */
            if($item->getParentItem()) {
                continue;
            }
            
            if($this->quoteHelper->shouldApplySubscriptionPriceForQuoteItem($item) and ! $this->quoteHelper->shouldAddSubscriptionPriceForSubtotal($item)) {
                $subtotal = $total->getTotalAmount('subtotal');
                $baseSubtotal = $total->getBaseTotalAmount('subtotal');
                $subtotalInclTax = $total->getSubtotalInclTax();
                $baseSubtotalInclTax = $total->getBaseSubtotalInclTax();
                $weight = $address->getWeight();
                $qty = $address->getTotalQty();
                
                $subtotal = max(0, (bcsub($subtotal, $item->getRowTotal(), 2)));
                $baseSubtotal = max(0, (bcsub($baseSubtotal, $item->getRowTotal(), 2)));
				
                $subtotalInclTax = max(0, (bcsub($subtotalInclTax, $item->getRowTotalInclTax(), 2)));
                $baseSubtotalInclTax = max(0, (bcsub($baseSubtotalInclTax, $item->getRowTotalInclTax(), 2)));
				
                $weight -= $item->getWeight();
                $qty -= $item->getQty();
                
                $address->setTotalQty($qty);
                $address->setWeight($weight);
                $address->setSubtotal($subtotal);
                $address->setBaseSubtotal($baseSubtotal);
                $address->setSubtotalInclTax($subtotalInclTax);
                $address->setBaseSubtotalInclTax($baseSubtotalInclTax);
                $address->setSubtotalWithDiscount($subtotal + $total->getDiscountAmount());
                $address->setBaseSubtotalWithDiscount($baseSubtotal + $total->getBaseDiscountAmount());
            }
        }
        
        return $this;
    }
    
}
