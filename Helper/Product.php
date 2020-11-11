<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/19/16
 * Time: 4:10 PM
 */

namespace Toppik\Subscriptions\Helper;

use \Toppik\Subscriptions\Model\Preferences;


class Product
{

    /**
     * @var Data
     */
    private $subscriptionHelper;
    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    private $optionFactory;
    /**
     * @var \Magento\Catalog\Model\Product\Option\ValueFactory
     */
    private $valueFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Product constructor.
     * @param Data $subscriptionHelper
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param \Magento\Catalog\Model\Product\Option\ValueFactory $valueFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $subscriptionHelper,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Catalog\Model\Product\Option\ValueFactory $valueFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->optionFactory = $optionFactory;
        $this->valueFactory = $valueFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $title
     * @return \Magento\Catalog\Model\Product\Option
     */
    public function getSubscriptionTypeProductOption(\Magento\Catalog\Model\Product $product, $title) {
        /* @var \Magento\Catalog\Model\Product\Option $option */
        $option = $this->optionFactory->create();
        $option->setProduct($product);
        $option->setOptionId(Preferences::SUBSCRIPTION_OPTION_ID);
        $option->setTitle($title);
        $option->setType(\Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN);
        $option->setSortOrder(99999);

        /* @var \Magento\Catalog\Model\Product\Option\Value $value */
        $value = $this->valueFactory->create();
        $value->setOptionTypeId(Preferences::SUBSCRIPTION_OPTION_EMPTY_VALUE);
        $value->setTitle(__(Preferences::SUBSCRIPTION_OPTION_LABEL));
        $value->setOption($option);
        $value->setProduct($product);
        $option->addValue($value);

        $subscription = $this->subscriptionHelper->getSubscriptionByProduct($product);
        if(! $subscription->getIsSubscriptionOnly()) {
            /* @var \Magento\Catalog\Model\Product\Option\Value $value */
            $value = $this->valueFactory->create();
            $value->setOptionTypeId(Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_VALUE);
            $value->setTitle(__(Preferences::SUBSCRIPTION_OPTION_NO_SUBSCRIPTION_LABEL));
            $value->setOption($option);
            $value->setProduct($product);
            $option->addValue($value);
        }

        $items = $subscription->getItemsCollection();
        foreach($items as $item) {
            /* @var \Toppik\Subscriptions\Model\Settings\Item $item */
            $period = $item->getPeriod();
            if(! $period->getIsVisible() or ! (in_array(0, $period->getStoreIds()) or in_array($this->storeManager->getStore()->getId(), $period->getStoreIds()))) {
                continue;
            }
            /* @var \Magento\Catalog\Model\Product\Option\Value $value */
            $value = $this->valueFactory->create();
            $value->setOptionTypeId($item->getId());
            $value->setTitle(__($item->getPeriod()->getTitle()));
            $value->setOption($option);
            $value->setProduct($product);
            $value->setSubscriptionPrice($item->getRegularPrice());
            $value->setPrice($item->getRegularPrice() - $product->getFinalPrice());
            $option->addValue($value);
        }
        return $option;
    }

}