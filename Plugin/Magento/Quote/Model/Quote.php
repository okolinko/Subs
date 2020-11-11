<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/20/16
 * Time: 2:56 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\Quote\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model;
use Magento\Quote\Model\Quote\Item;
use Toppik\Subscriptions\Model\Preferences;
use Toppik\Subscriptions\Model\Settings\ItemFactory;
use Toppik\Subscriptions\Model\Settings;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Cart as CustomerCart;


class Quote
{

    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CustomerCart
     */
    private $cart;
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var bool
     */
    private $subscriptionItemsHidden = false;

    /**
     * Quote constructor.
     * @param ItemFactory $itemFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param CustomerCart $cart
     * @param ManagerInterface $eventManager
     * @internal param ProductFactory $productFactory
     * @internal param ProductFactory $product
     */
    public function __construct(
        ItemFactory $itemFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        CustomerCart $cart,
        ManagerInterface $eventManager
    )
    {
        $this->itemFactory = $itemFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->cart = $cart;
        $this->eventManager = $eventManager;
    }

    /**
     * Adds hidden product
     * @param Model\Quote $quote
     * @param callable $proceed
     * @param Product $product
     * @param null|float|DataObject $request
     * @param null|string $processMode
     * @return Item|string
     * @throws LocalizedException
     */
    public function aroundAddProduct(
        Model\Quote $quote,
        callable $proceed,
        Product $product,
        $request = null,
        $processMode = AbstractType::PROCESS_MODE_FULL
    ) {
        $return = $proceed($product, $request, $processMode);

        if($return instanceof Item and $request instanceof DataObject) {
            $request = clone $request;
            $options = $request->getOptions();
            if(isset($options[Preferences::SUBSCRIPTION_OPTION_ID])) {
                $subscriptionType = $options[Preferences::SUBSCRIPTION_OPTION_ID];
                if($subscriptionType != Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE) {
                    /* @var Settings\Item $subscriptionItem */
                    $subscriptionItem = $this->itemFactory->create();
                    $subscriptionItem->load($subscriptionType);
                    if($subscriptionItem->getUseCouponCode()) {
                        $options[Preferences::SUBSCRIPTION_OPTION_ID] = Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE;
                        $options[Preferences::LINKED_ITEM_OPTION_ID] = $subscriptionItem->getId();
                        $request->setOptions($options);
                        /* @var Product $product */
                        $productId = $product->getId();
                        $storeId = $product->getStoreId();;
                        $this->productRepository->cleanCache();
                        $product = $this->productRepository->getById($productId, false, $storeId, true);
                        $linkedQuoteItem = $quote->addProduct($product, $request, $processMode);
                        $return->setLinkedChildQuoteItem($linkedQuoteItem);
                    }
                }
            }
        }

        return $return;
    }

    public function beforeRemoveItem(Model\Quote $quote, $itemId) {
        $item = $quote->getItemById($itemId);
        if($item) {
            if($item->getLinkedParentQuoteItem()) {
                throw new \Exception(
                    __('Can\'t remove child linked item from cart, you should remove parent linked item.'),
                    \Toppik\Subscriptions\Model\Settings\Error::ERROR_REMOVE_CHILD_SUBSCRIPTION
                );
            }
            if($item->getLinkedChildQuoteItem()) {
                /* @var Item $linkedItem */
                $linkedItem = $item->getLinkedChildQuoteItem();
                $linkedItem->isDeleted(true);

                $children = $linkedItem->getChildren();
                if(! empty($children)) {
                    foreach($children as $child) {
                        $child->isDeleted(true);
                    }
                }

                $parent = $linkedItem->getParentItem();
                if($parent) {
                    $parent->isDeleted(true);
                }
            }
        }
        return [$itemId];
    }

    /**
     * @param Model\Quote $self
     * @param callable $proceed
     * @param Model\Quote $quote
     * @return Model\Quote
     */
    public function aroundMerge(Model\Quote $self, callable $proceed, Model\Quote $quote) {
        $this->eventManager->dispatch(
            $self->getEventPrefix() . '_merge_before',
            ['quote' => $self, 'source' => $quote]
        );

        foreach ($quote->getAllVisibleItems() as $item) {
            $found = false;
            foreach ($self->getAllItems() as $quoteItem) {
                if ($quoteItem->compare($item)) {
                    $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                    $found = true;
                    break;
                }
            }

            if($item->getLinkedParentQuoteItem()) {
                continue;
            }

            if (!$found) {
                $newItem = clone $item;
                $self->addItem($newItem);
                if($item->getLinkedChildQuoteItem()) {
                    $childNewItem = clone $item->getLinkedChildQuoteItem();
                    $newItem->setLinkedChildQuoteItem($childNewItem);
                    $self->addItem($childNewItem);
                }
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $self->addItem($newChild);
                        if($child->getLinkedChildQuoteItem()) {
                            $linkedNewChild = clone $child->getLinkedChildQuoteItem();
                            $newChild->setLinkedChildQuoteItem($linkedNewChild);
                            $linkedNewChild->setParentItem($childNewItem);
                            $self->addItem($linkedNewChild);
                        }
                    }
                }
            }
        }

        /**
         * Init shipping and billing address if quote is new
         */
        if (!$self->getId()) {
            $self->getShippingAddress();
            $self->getBillingAddress();
        }

        if ($quote->getCouponCode()) {
            $self->setCouponCode($quote->getCouponCode());
        }

        $this->eventManager->dispatch(
            $self->getEventPrefix() . '_merge_after',
            ['quote' => $self, 'source' => $quote]
        );

        return $self;
    }

    /**
     * @param Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Item[] $result
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function afterGetAllVisibleItems(Model\Quote $quote, array $result) {
        return array_filter($result, function($item) {
            if($item->getLinkedParentQuoteItem() and Preferences::HIDE_LINKED_ITEM_FROM_QUOTE) {
                return false;
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param Model\Quote $quote
     * @param array $result
     * @return array
     */
    public function afterGetAllItems(Model\Quote $quote, array $result) {
        if($this->subscriptionItemsHidden) {
            $result = array_values(array_filter($result, function($item) {
                if($item->getLinkedChildQuoteItem()) {
                    return false;
                }
                return true;
            }, ARRAY_FILTER_USE_BOTH));
        }
        return $result;
    }

    /**
     * @param Model\Quote $quote
     * @param \Magento\Quote\Api\Data\CartItemInterface[]|null $result
     * @return \Magento\Quote\Api\Data\CartItemInterface[]|null
     */
    public function afterGetItems(Model\Quote $quote, $result) {
        if(is_array($result) and $this->subscriptionItemsHidden) {
            $result = array_values(array_filter($result, function($item) {
                if($item->getLinkedChildQuoteItem()) {
                    return false;
                }
                return true;
            }, ARRAY_FILTER_USE_BOTH));
        }
        return $result;
    }

    /**
     * @return $this
     */
    public function hideSubscriptionItems() {
        $this->subscriptionItemsHidden = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function showSubscriptionItems() {
        $this->subscriptionItemsHidden = false;
        return $this;
    }

}
