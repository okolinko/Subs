<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\View\Search\Product\Grid\Renderer;

class Product extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text {
    
    /**
     * Render product name to add Configure link
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row) {
        $row->setCanSkipSubscriptionOption(true);
        
        $rendered = parent::render($row);
        $isConfigurable = $row->canConfigure();
        $style = $isConfigurable ? '' : 'disabled';
        $prodAttributes = $isConfigurable ? sprintf(
            'list_type = "product_to_add" product_id = %s',
            $row->getId()
        ) : 'disabled="disabled"';
        
        return sprintf(
            '<a href="javascript:void(0)" class="action-configure %s" %s>%s</a>',
            $style,
            $prodAttributes,
            __('Configure')
        ) . sprintf('<span class="value">%s</span>', $rendered);
    }
    
}
