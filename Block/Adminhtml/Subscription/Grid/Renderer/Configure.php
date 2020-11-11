<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Subscription\Grid\Renderer;

class Configure extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text {
    
    public function render(\Magento\Framework\DataObject $row) {
        $html = '';
        
        if($row->getData('type_id') == \Toppik\Subscriptions\Model\Profile\Points::TYPE_PRODUCT) {
            $html = sprintf('<a href="#" rel="%s" class="js-save_the_sale-search-product action-choose" onclick="subscriptionSaveTheSale.productLinkRowClick(this); return false;">%s</a>', $row->getId(), __('Please select option for gift'));
        } else if($row->getData('type_id') == \Toppik\Subscriptions\Model\Profile\Points::TYPE_COUPON) {
            $html = sprintf('<a href="#" rel="%s" class="js-save_the_sale-search-coupon action-choose" onclick="subscriptionSaveTheSale.ruleLinkRowClick(this); return false;">%s</a>', $row->getId(), __('Choose'));
        } else if($row->getData('type_id') == \Toppik\Subscriptions\Model\Profile\Points::TYPE_PRODUCT_PRICE) {
            /*$html = sprintf('<input type="text" name="option[%s][price]" value="" placeholder="%s" class="action-choose" />', $row->getId(), __('Type new price'));*/
            $html = sprintf('<select name="option[%s][price]" class="action-choose"><option value="">Select new price<option><option value="34">34<option></select>', $row->getId(), __('Type new price'));
        }
        
        return $html;
    }
    
}
