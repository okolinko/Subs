<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/20/16
 * Time: 7:52 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\Quote\Model\Quote;

use Magento\Quote\Model\Quote;
use Toppik\Subscriptions\Helper\Quote as QuoteHelper;
use Toppik\Subscriptions\Model\Preferences;
use Toppik\Subscriptions\Model\Settings\ItemFactory;
use Toppik\Subscriptions\Model\Settings;

class Item
{

    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * Item constructor.
     * @param ItemFactory $itemFactory
     * @param QuoteHelper $quoteHelper
     */
    public function __construct(
        ItemFactory $itemFactory,
        QuoteHelper $quoteHelper
    )
    {
        $this->itemFactory = $itemFactory;
        $this->quoteHelper = $quoteHelper;
    }

    public function afterBeforeSave(Quote\Item $self, $result) {
        if($self->getLinkedParentQuoteItem()) {
            $self->setLinkedItemId($self->getLinkedParentQuoteItem()->getId());
        }
        return $result;
    }

    public function around__call(Quote\Item $item, callable $proceed, $name, $args) {
        if(method_exists($this, $name) and $name !== __METHOD__) {
            array_unshift($args, $item);
            return call_user_func_array([$this, $name], $args);
        } else {
            return $proceed($name, $args);
        }
    }

    /**
     * @param Quote\Item $self
     * @param Quote\Item $item
     * @return Quote\Item
     */
    private function setLinkedChildQuoteItem(Quote\Item $self, Quote\Item $item) {
        $item->setData('linked_parent_quote_item', $self);
        $self->setData('linked_child_quote_item', $item);
        $selfChildren = $self->getChildren();
        $itemChildren = $item->getChildren();
        foreach($selfChildren as $selfChild) {
            foreach($itemChildren as $itemChild) {
                if($selfChild->getProductId() == $itemChild->getProductId()) {
                    $itemChild->setData('linked_parent_quote_item', $selfChild);
                    $selfChild->setData('linked_child_quote_item', $itemChild);
                }
            }
        }
        if($item->getParentItem() and $self->getParentItem()) {
            $item->getParentItem()->setData('linked_parent_quote_item', $self->getParentItem());
            $self->getParentItem()->setData('linked_child_quote_item', $item->getParentItem());
        }
        return $self;
    }

    /**
     * @param Quote\Item $self
     * @param Quote\Item $item
     * @return Quote\Item
     */
    private function setLinkedParentQuoteItem(Quote\Item $self, Quote\Item $item) {
        $item->setLinkedChildQuoteItem($self);
        return $self;
    }

    /**
     * @param Quote\Item $self
     * @return Quote\Item|null
     */
    private function getLinkedParentQuoteItem(Quote\Item $self) {
        if(is_null($self->getData('linked_parent_quote_item'))) {
            $linkedItem = false;
            if($self->getLinkedItemId()) {
                foreach($self->getQuote()->getItemsCollection() as $item) {
                    if($item->getId() == $self->getLinkedItemId()) {
                        $linkedItem = $item;
                        break;
                    }
                }
            }
            $self->setData('linked_parent_quote_item', $linkedItem);
        }
        return $self->getData('linked_parent_quote_item');
    }

    /**
     * @param Quote\Item $self
     * @return Quote\Item|null
     */
    private function getLinkedChildQuoteItem(Quote\Item $self) {
        if(is_null($self->getData('linked_child_quote_item'))) {
            $linkedItem = FALSE;
            foreach($self->getQuote()->getItemsCollection() AS $item) {
                if($item->getLinkedItemId() AND $item->getLinkedItemId() == $self->getId()) {
                    $linkedItem = $item;
                    break;
                }
            }
            $self->setData('linked_child_quote_item', $linkedItem);
        }
        return $self->getData('linked_child_quote_item');
    }

    /**
     * @param Quote\Item $self
     * @return Settings\Item|false
     */
    private function getSubscriptionItem(Quote\Item $self) {
        if(! $self->hasData('subscription_item')) {
            $subscriptionItem = false;
            $subscriptionTypeOption = $this->quoteHelper->getSubscriptionTypeOptionFromQuoteItem($self);
            if($subscriptionTypeOption !== false and $subscriptionTypeOption !== Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE) {
                /* @var Settings\Item $subscriptionItem */
                $subscriptionItem = $this->itemFactory->create();
                $subscriptionItem->load($subscriptionTypeOption);
            }
            $self->setData('subscription_item', $subscriptionItem);
        }
        return $self->getData('subscription_item');
    }

}