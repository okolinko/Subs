<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/21/16
 * Time: 8:58 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\Quote\Model\Quote\Address\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Toppik\Subscriptions\Helper\Quote as QuoteHelper;
use Toppik\Subscriptions\Model\Quote\Address\Total\ModifyFinalPrice;

class Subtotal
{

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * Subtotal constructor.
     * @param QuoteHelper $quoteHelper
     */
    public function __construct(
        QuoteHelper $quoteHelper
    )
    {
        $this->quoteHelper = $quoteHelper;
    }

    public function aroundCollect(
        Total\AbstractTotal $subtotal,
        callable $proceed,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Address\Total $total) {
        if($subtotal instanceof ModifyFinalPrice) {
            return $proceed($quote, $shippingAssignment, $total);
        }
        $originalItems = $shippingAssignment->getItems();
        $originalItemsWithoutSubscription = array_filter($originalItems, [$this, 'filterNonSubscriptionItems'], ARRAY_FILTER_USE_BOTH);
        $originalItemsWithSubscription    = array_filter($originalItems, [$this, 'filterSubscriptionItems'], ARRAY_FILTER_USE_BOTH);
        if((bool)$quote->getCreateFromSubscriptionProfile() === true) {
            $shippingAssignment->setItems($originalItemsWithSubscription);
        } else {
            $shippingAssignment->setItems($originalItemsWithoutSubscription);
        }

        $result = $proceed($quote, $shippingAssignment, $total);
        $shippingAssignment->setItems($originalItems);
        return $result;
    }

    /**
     * @param AbstractItem $item
     * @return bool
     */
    public function filterNonSubscriptionItems(AbstractItem $item) {
        return ! $this->quoteHelper->shouldApplySubscriptionPriceForQuoteItem($item);
    }

    /**
     * @param AbstractItem $item
     * @return bool
     */
    public function filterSubscriptionItems(AbstractItem $item) {
        return  $this->quoteHelper->shouldApplySubscriptionPriceForQuoteItem($item);
    }

}