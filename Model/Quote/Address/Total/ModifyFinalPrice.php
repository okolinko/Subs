<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/22/16
 * Time: 1:08 PM
 */

namespace Toppik\Subscriptions\Model\Quote\Address\Total;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\QuoteValidator;
use Toppik\Subscriptions\Helper\Quote as QuoteHelper;

class ModifyFinalPrice extends Total\Subtotal {

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * Subtotal constructor.
     * @param QuoteHelper $quoteHelper
     * @param QuoteValidator $quoteValidator
     */
    public function __construct(
        QuoteHelper $quoteHelper,
        QuoteValidator $quoteValidator
    )
    {
        $this->quoteHelper = $quoteHelper;
        parent::__construct($quoteValidator);
    }

    /**
     * Collect address subtotal
     *
     * @param Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $this->setCode('subtotal');
        $this->_setAddress($shippingAssignment->getShipping()->getAddress());
        $this->_setTotal($total);

        $baseVirtualAmount = $virtualAmount = 0;

        $address = $shippingAssignment->getShipping()->getAddress();
        /**
         * Process address items
         */
        $items = $shippingAssignment->getItems();
        
        $subtotalWithoutSubscription        = 0;
        $baseSubtotalWithoutSubscription    = 0;
        $totalQtyWithoutSubscription        = 0;
        $weightWithoutSubscription          = 0;
        
        foreach($items as $item) {
            if(!$this->quoteHelper->shouldApplySubscriptionPriceForQuoteItem($item)) {
                if(!$item->getParentItem()) {
                    $subtotalWithoutSubscription        = number_format(($subtotalWithoutSubscription + $item->getRowTotal()), 2);
                    $baseSubtotalWithoutSubscription    = number_format(($baseSubtotalWithoutSubscription + $item->getBaseRowTotal()), 2);
                    $totalQtyWithoutSubscription        = $totalQtyWithoutSubscription + $item->getQty();
                    $weightWithoutSubscription          = $weightWithoutSubscription + $item->getWeight();
                }
                
                continue;
            }

            if ($this->_initItem($address, $item) && $item->getQty() > 0) {
                /**
                 * Separately calculate subtotal only for virtual products
                 */
                if ($item->getProduct()->isVirtual()) {
                    $virtualAmount += $item->getRowTotal();
                    $baseVirtualAmount += $item->getBaseRowTotal();
                }
            } else {
                try {
                    $this->_removeItem($address, $item);
                } catch(\Exception $e) {
                    if(!$e->getCode() || $e->getCode() !== \Toppik\Subscriptions\Model\Settings\Error::ERROR_REMOVE_CHILD_SUBSCRIPTION) {
                        throw $e;
                    }
                }
            }
        }
        
        $address->setSubtotalWithoutSubscription($subtotalWithoutSubscription);
        $address->setBaseSubtotalWithoutSubscription($baseSubtotalWithoutSubscription);
        $address->setTotalQtyWithoutSubscription($totalQtyWithoutSubscription);
        $address->setWeightWithoutSubscription($weightWithoutSubscription);
        
        $total->setBaseVirtualAmount($total->getBaseVirtualAmount() + $baseVirtualAmount);
        $total->setVirtualAmount($total->getVirtualAmount() + $virtualAmount);

        /**
         * Initialize grand totals
         */
        $this->quoteValidator->validateQuoteAmount($quote, $total->getSubtotal());
        $this->quoteValidator->validateQuoteAmount($quote, $total->getBaseSubtotal());
        $address->setSubtotal($total->getSubtotal());
        $address->setBaseSubtotal($total->getBaseSubtotal());
        return $this;
    }

    /**
     * Address item initialization
     *
     * @param Address $address
     * @param AddressItem|Item $item
     * @return bool
     */
    protected function _initItem($address, $item)
    {
        if ($item instanceof AddressItem) {
            $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
        } else {
            $quoteItem = $item;
        }
        $product = $quoteItem->getProduct();
        $product->setCustomerGroupId($quoteItem->getQuote()->getCustomerGroupId());

        /**
         * Quote super mode flag mean what we work with quote without restriction
         */
        if ($item->getQuote()->getIsSuperMode()) {
            if (!$product) {
                return false;
            }
        } else {
            if (!$product || !$product->isVisibleInCatalog()) {
                return false;
            }
        }

        $quoteItem->setConvertedPrice(null);
        $originalPrice = $product->getPrice();
        if ($quoteItem->getParentItem() && $quoteItem->isChildrenCalculated()) {
            $finalPrice = $quoteItem->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
                $quoteItem->getParentItem()->getProduct(),
                $quoteItem->getParentItem()->getQty(),
                $product,
                $quoteItem->getQty()
            );
            $this->_calculateRowTotal($item, $finalPrice, $originalPrice);
        } elseif (!$quoteItem->getParentItem()) {
            $finalPrice = $product->getFinalPrice($quoteItem->getQty());
            $subscriptionFinalPrice = $this->quoteHelper->getSubscriptionPriceForQuoteItem($quoteItem);
            if($subscriptionFinalPrice !== false) {
                $finalPrice = min($finalPrice, $subscriptionFinalPrice);
            }
            $this->_calculateRowTotal($item, $finalPrice, $originalPrice);
            $this->_addAmount($item->getRowTotal());
            $this->_addBaseAmount($item->getBaseRowTotal());
            $address->setTotalQty($address->getTotalQty() + $item->getQty());
        }
        return true;
    }

    public function fetch(Quote $quote, Total $total)
    {
        return [];
    }

}
