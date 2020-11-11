<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/19/16
 * Time: 2:13 PM
 */

namespace Toppik\Subscriptions\Helper;


use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Item;
use Toppik\Subscriptions\Helper\Data as SubscriptionHelper;
use Toppik\Subscriptions\Model\Preferences;

class Quote
{

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * Quote constructor.
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        SubscriptionHelper $subscriptionHelper
    )
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function quoteHasSubscriptionProductsWithoutSubscription(QuoteModel $quote) {
        foreach($quote->getAllItems() as $item) {
            /* @var Item $item */
            if(! $item->getLinkedChildQuoteItem()) {
                $subscriptionTypeOption = $this->getSubscriptionTypeOptionFromQuoteItem($item);
                if($subscriptionTypeOption === false) {
                    continue;
                }
                if($subscriptionTypeOption === Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param QuoteModel $quote
     * @return bool
     */
    public function quoteHasSubscription(QuoteModel $quote) {
        foreach($quote->getAllItems() as $item) {
            /* @var Item $item */
            if($item->getLinkedChildQuoteItem()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param QuoteModel $quote
     * @return bool
     */
    public function shouldApplyDiscount(QuoteModel $quote) {
        foreach($quote->getAllItems() as $item) {
            /* @var Item $item */
            if($item->getLinkedChildQuoteItem()) {
                $subscriptionTypeOption = $this->getSubscriptionTypeOptionFromQuoteItem($item);
                if($subscriptionTypeOption === false) {
                    continue;
                }
                if($subscriptionTypeOption !== Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE) {
                    $subscriptionItemId = $subscriptionTypeOption;
                } else {
                    continue;
                }
                $product = $item->getProduct();
                if($this->subscriptionHelper->productHasSubscription($product)) {
                    $subscription = $this->subscriptionHelper->getSubscriptionByProduct($product);
                    $subscriptionItems = $subscription->getItemsCollection();
                    foreach($subscriptionItems as $subscriptionItem) {
                        /* @var \Toppik\Subscriptions\Model\Settings\Item $subscriptionItem */
                        if($subscriptionItem->getId() == $subscriptionItemId) {
                            if($subscriptionItem->getUseCouponCode() == \Toppik\Subscriptions\Model\Settings\Item::USE_COUPON_CODE_YES) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param DataObject $buyRequest
     * @return bool|mixed
     */
    public function getSubscriptionTypeOptionFromBuyRequest(DataObject $buyRequest) {
        if($buyRequest->hasData('options')) {
            $options = $buyRequest->getData('options');
            if(is_array($options) and isset($options[Preferences::SUBSCRIPTION_OPTION_ID]) and is_scalar($options[Preferences::SUBSCRIPTION_OPTION_ID])) {
                return $options[Preferences::SUBSCRIPTION_OPTION_ID];
            }
        }
        return false;
    }

    /**
     * @param Item $quoteItem
     * @return bool|mixed
     */
    public function getSubscriptionTypeOptionFromQuoteItem(Item $quoteItem) {
        $buyRequest = $quoteItem->getBuyRequest();
        return $this->getSubscriptionTypeOptionFromBuyRequest($buyRequest);
    }

    /**
     * @param DataObject $buyRequest
     * @return bool|mixed
     */
    public function getLinkedTypeOptionFromBuyRequest(DataObject $buyRequest) {
        if($buyRequest->hasData('options')) {
            $options = $buyRequest->getData('options');
            if(is_array($options) and isset($options[Preferences::LINKED_ITEM_OPTION_ID]) and is_scalar($options[Preferences::LINKED_ITEM_OPTION_ID])) {
                return $options[Preferences::LINKED_ITEM_OPTION_ID];
            }
        }
        return false;
    }

    /**
     * @param Item $quoteItem
     * @return bool|mixed
     */
    public function getLinkedTypeOptionFromQuoteItem(Item $quoteItem) {
        $buyRequest = $quoteItem->getBuyRequest();
        return $this->getLinkedTypeOptionFromBuyRequest($buyRequest);
    }

    /**
     * @param Item $quoteItem
     * @return bool
     */
    public function shouldApplySubscriptionPriceForQuoteItem(Item $quoteItem) {
        $subscriptionTypeOption = $this->getSubscriptionTypeOptionFromQuoteItem($quoteItem);
        $linkedTypeOption = $this->getLinkedTypeOptionFromQuoteItem($quoteItem);
        if($subscriptionTypeOption === false) {
            return false;
        }
        if(
            $subscriptionTypeOption !== Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE
            or
            ($subscriptionTypeOption === Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE and $linkedTypeOption !== false)
        ) {
            return true;
        }
        return false;
    }

    public function shouldAddSubscriptionPriceForSubtotal(Item $quoteItem) {
        $subscriptionTypeOption = $this->getSubscriptionTypeOptionFromQuoteItem($quoteItem);
        if(
            $subscriptionTypeOption === Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param Item $quoteItem
     * @return bool|float
     */
    public function getSubscriptionPriceForQuoteItem(Item $quoteItem) {
        $subscriptionTypeOption = $this->getSubscriptionTypeOptionFromQuoteItem($quoteItem);
        if($subscriptionTypeOption === false) {
            return false;
        }
        $linkedTypeOption = $this->getLinkedTypeOptionFromQuoteItem($quoteItem);
        if($subscriptionTypeOption !== Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE) {
            $subscriptionItemId = $subscriptionTypeOption;
        } elseif($subscriptionTypeOption === Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE and $linkedTypeOption !== false) {
            $subscriptionItemId = $linkedTypeOption;
        } else {
            return false;
        }
        $product = $quoteItem->getProduct();
        if($this->subscriptionHelper->productHasSubscription($product)) {
            $subscription = $this->subscriptionHelper->getSubscriptionByProduct($product);
            $subscriptionItems = $subscription->getItemsCollection();
            foreach($subscriptionItems as $subscriptionItem) {
                /* @var \Toppik\Subscriptions\Model\Settings\Item $subscriptionItem */
                if($subscriptionItem->getId() == $subscriptionItemId) {
                    return $subscriptionItem->getRegularPrice();
                }
            }
        }
        return false;
    }

    /**
     * @param Item $item
     * @return string|Phrase
     */
    public function getQuoteItemName(Item $item)
    {
        foreach($item->getChildren() as $child) {
            return $child->getName();
        }
        return $item->getName();
    }

    /**
     * @param Item $item
     * @return string|Phrase
     */
    public function getQuoteItemPeriod(Item $item) {
        $subscriptionTypeOption = $this->getSubscriptionTypeOptionFromQuoteItem($item);
        if($subscriptionTypeOption === false) {
            return '';
        }
        $linkedTypeOption = $this->getLinkedTypeOptionFromQuoteItem($item);
        if($subscriptionTypeOption !== Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE) {
            $subscriptionItemId = $subscriptionTypeOption;
        } elseif($subscriptionTypeOption === Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE and $linkedTypeOption !== false) {
            $subscriptionItemId = $linkedTypeOption;
        } else {
            return '';
        }
        $product = $item->getProduct();
        if($this->subscriptionHelper->productHasSubscription($product)) {
            $subscription = $this->subscriptionHelper->getSubscriptionByProduct($product);
            $subscriptionItems = $subscription->getItemsCollection();
            foreach($subscriptionItems as $subscriptionItem) {
                /* @var \Toppik\Subscriptions\Model\Settings\Item $subscriptionItem */
                if($subscriptionItem->getId() == $subscriptionItemId) {
                    $period = $subscriptionItem->getPeriod();
                    return $period->getLength() . ' ' . $period->getUnit()->getTitle() . 's';
                }
            }
        }
        return '';
    }
    
    /**
     * @param Item $item
     * @return string|Phrase
     */
    public function getQuoteItemPeriodLength(Item $item) {
        $subscriptionItemId = null;
        $subscriptionTypeOption = $this->getSubscriptionTypeOptionFromQuoteItem($item);
        
        if($subscriptionTypeOption === false) {
            return '';
        }
        
        $linkedTypeOption = $this->getLinkedTypeOptionFromQuoteItem($item);
        
        if($subscriptionTypeOption !== Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE) {
            $subscriptionItemId = $subscriptionTypeOption;
        } elseif($subscriptionTypeOption === Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE and $linkedTypeOption !== false) {
            $subscriptionItemId = $linkedTypeOption;
        } else {
            return '';
        }
        
        $product = $item->getProduct();
        
        if($this->subscriptionHelper->productHasSubscription($product)) {
            $subscription = $this->subscriptionHelper->getSubscriptionByProduct($product);
            $subscriptionItems = $subscription->getItemsCollection();
            
            foreach($subscriptionItems as $subscriptionItem) {
                /* @var \Toppik\Subscriptions\Model\Settings\Item $subscriptionItem */
                if($subscriptionItem->getId() == $subscriptionItemId) {
                    $period = $subscriptionItem->getPeriod();
                    $unit   = $subscriptionItem->getUnit();
                    
                    return $period->getLength() * $unit->getLength();
                }
            }
        }
        
        return '';
    }
    
}
