<?php
namespace Toppik\Subscriptions\Model\Quote\Address\Total;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Toppik\Subscriptions\Helper\Quote as QuoteHelper;

class SubscriptionSubtotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal {
    
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * SubscriptionSubtotal constructor.
     * @param QuoteHelper $quoteHelper
     */
    public function __construct(
        QuoteHelper $quoteHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->dateTime = $dateTime;
    }
    
    public function getLabel() {
        return __('Subscription Subtotal');
    }
    
    public function fetch(Quote $quote, Total $total) {
        $subscriptionData = [];
        $subtotal = 0;
        foreach($quote->getAllVisibleItems() as $item) {
            if($this->quoteHelper->shouldApplySubscriptionPriceForQuoteItem($item)) {
                $subtotal += $item->getRowTotal();
                
                $percent                = 10;
                $savings                = 0;
                $displayRegularPrice    = 0;
                $displayFinalPrice      = 0;
                $nextOrderDate          = '';
                
                if($item->getProduct()) {
                    $price_type             = \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE;
                    
                    $displayRegularPrice    = $item->getProduct()->getPriceInfo()->getPrice($price_type)->getAmount()->getValue();
                    $displayFinalPrice      = $item->getPrice();
                    
                    if($displayRegularPrice > $displayFinalPrice) {
                        $savings = max(0, ($displayRegularPrice - $displayFinalPrice));
                    }
                }
                
                $length = $this->quoteHelper->getQuoteItemPeriodLength($item);
                
                if($length && (int) $length > 0) {
                    $nextOrderDate = date('M d, Y', ($this->dateTime->gmtTimestamp() + $length));
                }
                
                $subscriptionData[] = [
                    'item_id' => $item->getId(),
                    'subscription_name' => $item->getName(),
                    'name' => $this->quoteHelper->getQuoteItemName($item),
                    'period' => $this->quoteHelper->getQuoteItemPeriod($item),
                    'row_total_incl_tax' => $item->getRowTotalInclTax(),
                    'row_total' => $item->getRowTotal(),
                    'tax_amount' => $item->getTaxAmount(),
                    'shipping' => 0,
                    'savings_name' => ($savings > 0 ? __('Savings (%1%)', $percent) : ''),
                    'savings' => ($savings > 0 ? -($savings * $item->getQty()) : 0),
                    'next_order_date' => $nextOrderDate
                ];
            }
        }
        
        return [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'value' => $subtotal,
            'items' => $subscriptionData,
            'grand_total' => $quote->getGrandTotal(),
        ];
    }

}