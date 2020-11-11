<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/23/16
 * Time: 5:05 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\Quote\Model;


use Magento\Quote\Model;
use Toppik\Subscriptions\Converter\QuoteToProfile;
use Toppik\Subscriptions\Plugin\Magento\Sales\Model\Order;

class QuoteManagement
{

    /**
     * @var Quote
     */
    private $quotePlugin;
    /**
     * @var Order
     */
    private $orderPlugin;
    /**
     * @var QuoteToProfile
     */
    private $quoteToProfile;
    
    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 101.1.0
     */
    protected $serializer;
    
    /**
     * QuoteManagement constructor.
     * @param QuoteToProfile $quoteToProfile
     * @param Quote $quotePlugin
     * @param Order $orderPlugin
     */
    public function __construct(
        QuoteToProfile $quoteToProfile,
        Quote $quotePlugin,
        Order $orderPlugin,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    )
    {
        $this->quotePlugin = $quotePlugin;
        $this->orderPlugin = $orderPlugin;
        $this->quoteToProfile = $quoteToProfile;
        $this->serializer = $serializer;
    }

    /**
     * @param Model\QuoteManagement $quoteManagement
     * @param callable $proceed
     * @param Model\Quote $quote
     * @param array $orderData
     * @return mixed
     */
    public function aroundSubmit(
        Model\QuoteManagement $quoteManagement,
        callable $proceed,
        Model\Quote $quote,
        $orderData = []) {
        /* Hide quote_item_taxes */
        $this->orderPlugin->flushHiddenSubscriptions();
        $hasSubscription = false;
        foreach($quote->getAllItems() as $quoteItem) {
            /* @var \Magento\Quote\Model\Quote\Item $quoteItem */
            if($quoteItem->getLinkedChildQuoteItem()) {
                $orderItem = $quoteItem->getLinkedChildQuoteItem();
                $optionLinkedId = $orderItem->getOptionByCode(sprintf('option_%s', \Toppik\Subscriptions\Model\Preferences::LINKED_ITEM_OPTION_ID));
                $optionSubscriptionId = $orderItem->getOptionByCode(sprintf('option_%s', \Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_ID));
                
                if($optionLinkedId && is_scalar($optionLinkedId->getValue()) && $optionSubscriptionId && is_scalar($optionSubscriptionId->getValue())) {
                    $optionSubscriptionId->setValue($optionLinkedId->getValue());
                    
                    $option = $orderItem->getOptionByCode('info_buyRequest');
                    $buyRequest = $option ? $this->serializer->unserialize($option->getValue()) : [];
                    
                    if(is_array($buyRequest) && isset($buyRequest['options']) && is_array($buyRequest['options'])) {
                        if(
                            isset($buyRequest['options'][\Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_ID])
                            && is_scalar($buyRequest['options'][\Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_ID])
                        ) {
                            $buyRequest['options'][\Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_ID] = $optionLinkedId->getValue();
                            $option->setValue($this->serializer->serialize($buyRequest));
                        }
                    }
                }
                
                $this->orderPlugin->addHiddenSubscription($quoteItem->getId());
                $hasSubscription = true;
            }
        }
        
        $quote->setHasSubscription($hasSubscription);
        $this->quotePlugin->hideSubscriptionItems();
        $result = $proceed($quote, $orderData);
        $this->quotePlugin->showSubscriptionItems();
        $this->orderPlugin->flushHiddenSubscriptions();

        if($result) {
            $this->quoteToProfile->process($quote, $result, $orderData);
        }

        return $result;
    }

}