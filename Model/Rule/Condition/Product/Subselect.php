<?php
namespace Toppik\Subscriptions\Model\Rule\Condition\Product;

class Subselect extends \Magento\SalesRule\Model\Rule\Condition\Product\Subselect {
    
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
        $this->setType('Toppik\Subscriptions\Model\Rule\Condition\Product\Subselect')->setValue(null);
        $this->quoteHelper = $quoteHelper;
    }
    
    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml() {
        $html = $this->getTypeElement()->getHtml() . __(
            "If %1 %2 %3 for a subselection of non-subscription items in cart matching %4 of these conditions:",
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
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
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model) {
        if (!$this->getConditions()) {
            return false;
        }
        $attr = $this->getAttribute();
        $total = 0;
        foreach ($model->getQuote()->getAllVisibleItems() as $item) {
            if($this->quoteHelper->shouldApplySubscriptionPriceForQuoteItem($item)) {
                continue;
            }
            
            if (parent::validate($item)) {
                $total += $item->getData($attr);
            }
        }
        return $this->validateAttribute($total);
    }
    
}
