<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/22/16
 * Time: 4:30 PM
 */

namespace Toppik\Subscriptions\Model\Quote\Address\Total;


use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class RemoveSubscriptionFromSubtotal extends AbstractTotal
{

    /**
     * @var \Toppik\Subscriptions\Helper\Quote
     */
    private $quoteHelper;

    /**
     * RemoveSubscriptionFromSubtotal constructor.
     * @param \Toppik\Subscriptions\Helper\Quote $quoteHelper
     */
    public function __construct(
        \Toppik\Subscriptions\Helper\Quote $quoteHelper
    )
    {
        $this->quoteHelper = $quoteHelper;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
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
                $tax = $total->getTotalAmount('tax');
                $baseTax = $total->getBaseTotalAmount('tax');
                $discountTaxCompensation = $total->getTotalAmount('discount_tax_compensation');
                $baseDiscountTaxCompensation = $total->getBaseTotalAmount('discount_tax_compensation');
                $subtotalInclTax = $total->getSubtotalInclTax();
                $baseSubtotalInclTax = $total->getBaseSubtotalInclTax();
                $weight = $address->getWeight();
                $qty = $address->getTotalQty();

                $subtotal = max(0, (bcsub($subtotal, $item->getRowTotal(), 2)));
                $baseSubtotal = max(0, (bcsub($baseSubtotal, $item->getRowTotal(), 2)));
				
                $discountTaxCompensation -= $item->getDiscountTaxCompensationAmount();
                $baseDiscountTaxCompensation -= $item->getDiscountTaxCompensationAmount();
				
                $tax = max(0, (bcsub($tax, $item->getTaxAmount(), 2)));
                $baseTax = max(0, (bcsub($baseTax, $item->getBaseTaxAmount(), 2)));
				
                $subtotalInclTax = max(0, (bcsub($subtotalInclTax, $item->getRowTotalInclTax(), 2)));
                $baseSubtotalInclTax = max(0, (bcsub($baseSubtotalInclTax, $item->getRowTotalInclTax(), 2)));
				
                $weight -= $item->getWeight();
                $qty -= $item->getQty();

                $total->setTotalAmount('subtotal', $subtotal);
                $total->setBaseTotalAmount('subtotal', $baseSubtotal);
                $total->setTotalAmount('tax', $tax);
                $total->setBaseTotalAmount('tax', $baseTax);
                $total->setTotalAmount('discount_tax_compensation', $discountTaxCompensation);
                $total->setBaseTotalAmount('discount_tax_compensation', $baseDiscountTaxCompensation);
                $total->setSubtotalInclTax($subtotalInclTax);
                $total->setBaseSubtotalTotalInclTax($baseSubtotalInclTax);
                $total->setBaseSubtotalInclTax($baseSubtotalInclTax);
                $address->setTotalQty($qty);
                $total->setSubtotalWithDiscount(
                    $total->getSubtotal() + $total->getDiscountAmount()
                );
                $total->setBaseSubtotalWithDiscount(
                    $total->getBaseSubtotal() + $total->getBaseDiscountAmount()
                );
                $address->setWeight($weight);
            }
        }
        return $this;
    }

}