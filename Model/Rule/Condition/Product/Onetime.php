<?php
namespace Toppik\Subscriptions\Model\Rule\Condition\Product;

class Onetime extends \Magento\SalesRule\Model\Rule\Condition\Product {
    
    const ATTRIBUTE_SUFFIX_ONETIME      = '_onetimeonly';
    const ATTRIBUTE_SUFFIX_SUBSCRIPTION = '_subscriptiononly';
    
    /**
     * @var \Toppik\Subscriptions\Helper\Quote
     */
    private $quoteHelper;
    
    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Toppik\Subscriptions\Helper\Quote $quoteHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource, $attrSetCollection, $localeFormat, $data);
        $this->quoteHelper = $quoteHelper;
    }
    
    /**
     * Validate Product Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model) {
        if($this->isOnetimeAttribute($this->getAttribute()) === true && $this->quoteHelper->shouldApplySubscriptionPriceForQuoteItem($model)) {
            return false;
        }
        
        if($this->isSubscriptionAttribute($this->getAttribute()) === true && !$this->quoteHelper->shouldApplySubscriptionPriceForQuoteItem($model)) {
            return false;
        }
        
        $oldAttrCode = $this->getAttribute();
        
        $this->setAttribute($this->getAttributeRealCode($this->getAttribute()));
        
        $result = parent::validate($model);
        
        $this->setAttribute($oldAttrCode);
        
        return $result;
    }
    
    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions() {
        $attributes         = [];
        $productAttributes  = $this->_productResource->loadAllAttributes()->getAttributesByCode();
        
        foreach($productAttributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if(!$attribute->isAllowedForRuleCondition() || !$attribute->getDataUsingMethod($this->_isUsedForRuleProperty)) {
                continue;
            }
            
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            $attributes[$this->getAttributeOnetimeCode($attribute->getAttributeCode())] = __('%1 (One-Time Only)', $attribute->getFrontendLabel());
            $attributes[$this->getAttributeSubscriptionCode($attribute->getAttributeCode())] = __('%1 (Subscription Only)', $attribute->getFrontendLabel());
        }
        
        $this->_addSpecialAttributes($attributes);
        
        asort($attributes);
        $this->setAttributeOption($attributes);
        
        return $this;
    }
    
    /**
     * Add special attributes
     *
     * @param array $attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes) {
        parent::_addSpecialAttributes($attributes);
        
        $attributes[$this->getAttributeOnetimeCode('quote_item_qty')] = __('Quantity in cart (One-Time Only)');
        $attributes[$this->getAttributeSubscriptionCode('quote_item_qty')] = __('Quantity in cart (Subscription Only)');
        
        $attributes[$this->getAttributeOnetimeCode('quote_item_price')] = __('Price in cart (One-Time Only)');
        $attributes[$this->getAttributeSubscriptionCode('quote_item_price')] = __('Price in cart (Subscription Only)');
        
        $attributes[$this->getAttributeOnetimeCode('quote_item_row_total')] = __('Row total in cart (One-Time Only)');
        $attributes[$this->getAttributeSubscriptionCode('quote_item_row_total')] = __('Row total in cart (Subscription Only)');
        
        $attributes[$this->getAttributeOnetimeCode('attribute_set_id')] = __('Attribute Set (One-Time Only)');
        $attributes[$this->getAttributeSubscriptionCode('attribute_set_id')] = __('Attribute Set (Subscription Only)');
        
        $attributes[$this->getAttributeOnetimeCode('category_ids')] = __('Category (One-Time Only)');
        $attributes[$this->getAttributeSubscriptionCode('category_ids')] = __('Category (Subscription Only)');
    }
    
    /**
     * Retrieve attribute code type
     *
     * @return bool
     */
    public function isOnetimeAttribute($code) {
        return (substr($code, -strlen(self::ATTRIBUTE_SUFFIX_ONETIME)) === self::ATTRIBUTE_SUFFIX_ONETIME);
    }
    
    /**
     * Retrieve attribute code type
     *
     * @return bool
     */
    public function isSubscriptionAttribute($code) {
        return (substr($code, -strlen(self::ATTRIBUTE_SUFFIX_SUBSCRIPTION)) === self::ATTRIBUTE_SUFFIX_SUBSCRIPTION);
    }
    
    /**
     * Retrieve attribute code
     *
     * @return string
     */
    public function getAttributeRealCode($code) {
        if($this->isOnetimeAttribute($this->getAttribute()) === true) {
            return preg_replace(sprintf('/%s$/', self::ATTRIBUTE_SUFFIX_ONETIME), '', $code);
        }
        
        if($this->isSubscriptionAttribute($this->getAttribute()) === true) {
            return preg_replace(sprintf('/%s$/', self::ATTRIBUTE_SUFFIX_SUBSCRIPTION), '', $code);
        }
        
        return $code;
    }
    
    /**
     * Retrieve attribute code
     *
     * @return string
     */
    public function getAttributeOnetimeCode($code) {
        return sprintf('%s%s', $code, self::ATTRIBUTE_SUFFIX_ONETIME);
    }
    
    /**
     * Retrieve attribute code
     *
     * @return string
     */
    public function getAttributeSubscriptionCode($code) {
        return sprintf('%s%s', $code, self::ATTRIBUTE_SUFFIX_SUBSCRIPTION);
    }
    
    /**
     * Retrieve attribute object
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getAttributeObject() {
        try {
            $obj = $this->_config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $this->getAttributeRealCode($this->getAttribute()));
        } catch (\Exception $e) {
            $obj = new \Magento\Framework\DataObject();
            $obj->setEntity($this->_productFactory->create())->setFrontendInput('text');
        }
        return $obj;
    }
    
    /**
     * Prepares values options to be used as select options or hashed array
     * Result is stored in following keys:
     *  'value_select_options' - normal select array: array(array('value' => $value, 'label' => $label), ...)
     *  'value_option' - hashed array: array($value => $label, ...),
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareValueOptions() {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if ($this->getAttributeRealCode($this->getAttribute()) === 'attribute_set_id') {
            $entityTypeId = $this->_config->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
            $selectOptions = $this->_attrSetCollection
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();
        } elseif ($this->getAttributeRealCode($this->getAttribute()) === 'type_id') {
            foreach ($selectReady as $value => $label) {
                if (is_array($label) && isset($label['value'])) {
                    $selectOptions[] = $label;
                } else {
                    $selectOptions[] = ['value' => $value, 'label' => $label];
                }
            }
            $selectReady = null;
        } elseif (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
        }

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }
    
    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml() {
        $html = '';

        switch ($this->getAttributeRealCode($this->getAttribute())) {
            case 'sku':
            case 'category_ids':
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __(
                    'Open Chooser'
                ) . '" /></a>';
        }
        return $html;
    }
    
    /**
     * Collect validated attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection) {
        $attribute = $this->getAttributeRealCode($this->getAttribute());
        if ('category_ids' != $attribute) {
            $productCollection->addAttributeToSelect($attribute, 'left');
            if ($this->getAttributeObject()->isScopeGlobal()) {
                $attributes = $this->getRule()->getCollectedAttributes();
                $attributes[$attribute] = true;
                $this->getRule()->setCollectedAttributes($attributes);
            } else {
                $this->_entityAttributeValues = $productCollection->getAllAttributeValues($attribute);
            }
        }

        return $this;
    }
    
    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType() {
        if ($this->getAttributeRealCode($this->getAttribute()) === 'attribute_set_id') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        if ($this->getAttributeObject()->getAttributeCode() == 'category_ids') {
            return 'category';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }
    
    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType() {
        if ($this->getAttributeRealCode($this->getAttribute()) === 'attribute_set_id') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }
    
    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl() {
        $url = false;
        switch ($this->getAttributeRealCode($this->getAttribute())) {
            case 'sku':
            case 'category_ids':
                $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttributeRealCode($this->getAttribute());
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }
    
    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply() {
        switch ($this->getAttributeRealCode($this->getAttribute())) {
            case 'sku':
            case 'category_ids':
                return true;
            default:
                break;
        }
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
                default:
                    break;
            }
        }
        return false;
    }
    
    /**
     * Get argument value to bind
     *
     * @return array|float|int|mixed|string|\Zend_Db_Expr
     */
    public function getBindArgumentValue() {
        if ($this->getAttributeRealCode($this->getAttribute()) == 'category_ids') {
            return new \Zend_Db_Expr(
                $this->_productResource->getConnection()
                ->select()
                ->from(
                    $this->_productResource->getTable('catalog_category_product'),
                    ['product_id']
                )->where(
                    'category_id IN (?)',
                    $this->getValueParsed()
                )->__toString()
            );
        }
        return parent::getBindArgumentValue();
    }
    
    /**
     * Get mapped sql field
     *
     * @return string
     */
    public function getMappedSqlField() {
        if (!$this->isAttributeSetOrCategory()) {
            $mappedSqlField = $this->getEavAttributeTableAlias() . '.value';
        } elseif ($this->getAttributeRealCode($this->getAttribute()) == 'category_ids') {
            $mappedSqlField = 'e.entity_id';
        } else {
            $mappedSqlField = parent::getMappedSqlField();
        }
        return $mappedSqlField;
    }
    
    /**
     * Validate product by entity ID
     *
     * @param int $productId
     * @return bool
     */
    public function validateByEntityId($productId) {
        if ('category_ids' == $this->getAttributeRealCode($this->getAttribute())) {
            $result = $this->validateAttribute($this->_getAvailableInCategories($productId));
        } elseif ('attribute_set_id' == $this->getAttributeRealCode($this->getAttribute())) {
            $result = $this->validateAttribute($this->_getAttributeSetId($productId));
        } else {
            $product = $this->productRepository->getById($productId);
            $result = $this->validate($product);
            unset($product);
        }
        return $result;
    }
    
    /**
     * Check is attribute set or category
     *
     * @return bool
     */
    protected function isAttributeSetOrCategory() {
        return in_array($this->getAttributeRealCode($this->getAttribute()), ['attribute_set_id', 'category_ids']);
    }
    
}
