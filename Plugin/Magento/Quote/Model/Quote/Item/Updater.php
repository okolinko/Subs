<?php
namespace Toppik\Subscriptions\Plugin\Magento\Quote\Model\Quote\Item;

class Updater {
    
    public function aroundUpdate(
        \Magento\Quote\Model\Quote\Item\Updater $original,
        callable $proceed,
        \Magento\Quote\Model\Quote\Item $item,
        array $info
    ) {
        $result = $proceed($item, $info);
        
        if($item->getLinkedChildQuoteItem()) {
            if($item->hasCustomPrice()) {
                $item->getLinkedChildQuoteItem()->setCustomPrice($item->getCustomPrice());
                $item->getLinkedChildQuoteItem()->setOriginalCustomPrice($item->getCustomPrice());
            } else {
                $item->getLinkedChildQuoteItem()->unsetData('custom_price');
                $item->getLinkedChildQuoteItem()->unsetData('original_custom_price');
            }
        }
        
        return $result;
    }
    
}
