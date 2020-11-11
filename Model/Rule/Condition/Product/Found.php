<?php
namespace Toppik\Subscriptions\Model\Rule\Condition\Product;

class Found extends \Magento\SalesRule\Model\Rule\Condition\Product\Found {
    
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;
    
    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        array $data = [],
        \Toppik\Subscriptions\Helper\Quote $quoteHelper
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType('Toppik\Subscriptions\Model\Rule\Condition\Product\Found');
        $this->quoteHelper = $quoteHelper;
    }
    
    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml() {
        $html = $this->getTypeElement()->getHtml() . __(
            "If a non-subscription item is %1 in the cart with %2 of these conditions true:",
            $this->getValueElement()->getHtml(),
            $this->getAggregatorElement()->getHtml()
        );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }
    
    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model) {
        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();
        $found = false;
        foreach ($model->getAllItems() as $item) {
            if($this->quoteHelper->shouldApplySubscriptionPriceForQuoteItem($item)) {
                continue;
            }
            
            $found = $all;
            foreach ($this->getConditions() as $cond) {
                $validated = $cond->validate($item);
                if ($all && !$validated || !$all && $validated) {
                    $found = $validated;
                    break;
                }
            }
            if ($found && $true || !$true && $found) {
                break;
            }
        }
        // found an item and we're looking for existing one
        if ($found && $true) {
            return true;
        } elseif (!$found && !$true) {
            // not found and we're making sure it doesn't exist
            return true;
        }
        return false;
    }
    
}
