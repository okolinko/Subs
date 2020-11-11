<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/16/16
 * Time: 6:54 PM
 */

namespace Toppik\Subscriptions\Block\Product\View\Subscription;

use Magento\Framework\Registry;
use Magento\Catalog\Model\Product\Option;
use Toppik\Subscriptions\Model\Preferences;

class Select extends \Magento\Catalog\Block\Product\View\Options\Type\Select
{

    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Option\ValueFactory
     */
    private $optionValueFactory;
    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    private $subscriptionHelper;
    /**
     * @var \Toppik\Subscriptions\Helper\Product
     */
    private $productHelper;

    /**
     * Select constructor.
     * @param \Toppik\Subscriptions\Helper\Data $subscriptionHelper
     * @param Registry $registry
     * @param Option\ValueFactory $optionValueFactory
     * @param \Toppik\Subscriptions\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param array $data
     */
    public function __construct(
        \Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        Registry $registry,
        Option\ValueFactory $optionValueFactory,
        \Toppik\Subscriptions\Helper\Product $productHelper,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->optionValueFactory = $optionValueFactory;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->productHelper = $productHelper;
        $this->init();
        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }

    private function init()
    {
        $product = $this->registry->registry('product');

        $this->setProduct($product);
        $this->_option = $this->productHelper->getSubscriptionTypeProductOption($product, __(Preferences::SUBSCRIPTION_CART_LABEL));

        $preconfiguredValues = $this->getProduct()->getPreconfiguredValues();
        $this->getProduct()->setData('preconfigured_values', $preconfiguredValues);
        $options = $preconfiguredValues->getData('options');
        if(! is_array($options)) {
            $options = [];
        }
        $options[$this->_option->getId()] = Preferences::SUBSCRIPTION_OPTION_EMPTY_VALUE;
        $preconfiguredValues->setData('options', $options);
    }

}