<?php
namespace Toppik\Subscriptions\Model\Profile;

class Points extends \Magento\Framework\Model\AbstractModel {
    
    const TYPE_PRODUCT = 1;
    const TYPE_COUPON = 2;
    const TYPE_PRODUCT_PRICE = 3;
    
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Profile\Points');
    }
    
    public function getAvailableTypes() {
        return array(
            0 => __('N/A'),
            self::TYPE_PRODUCT => __('Product'),
            self::TYPE_COUPON => __('Coupon'),
            self::TYPE_PRODUCT_PRICE => __('Product Price')
        );
    }
    
    public function getAvailableManager() {
        return array(
            0 => __('No'),
            1 => __('Yes')
        );
    }
    
    public function getCoupon() {
        if((int) $this->getTypeId() !== self::TYPE_COUPON || !$this->getRuleId()) {
            throw new \Exception(__('The item does not have coupons'));
        }
        
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $ruleFactory    = $objectManager->create('\Magento\SalesRule\Model\RuleFactory');
        $collection     = $objectManager->create('\Magento\SalesRule\Model\ResourceModel\Coupon\Collection');
        $rule           = $ruleFactory->create()->load($this->getRuleId());
        
        if(!$rule->getId()) {
            throw new \Exception(__('Rule ID %1 does not exist', $this->getRuleId()));
        }
        
        $collection->addRuleToFilter($rule);
        
        $collection->getSelect()
            ->where(
                new \Zend_Db_Expr(
                    sprintf(
                        'code NOT IN (
                            SELECT DISTINCT main_table.value
                            FROM subscriptions_save AS main_table
                            INNER JOIN subscriptions_save_points AS p ON p.id = main_table.option_id
                            WHERE p.type_id = %s
                        )',
                        self::TYPE_COUPON
                    )
                )
            );
        
        $coupon = $collection->getFirstItem();
        
        if(!$coupon || !$coupon->getId()) {
            throw new \Exception(__('Rule ID %1 does not have available coupons!', $this->getRuleId()));
        }
        
        return new \Magento\Framework\DataObject(array('name' => $rule->getName(), 'code' => $coupon->getCode()));
    }
    
}
