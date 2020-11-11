<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\View\Search\Product\Grid\Renderer;

class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
    
    /**
     * Render product name to add Configure link
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row) {
        $qty = $row->getData($this->getColumn()->getIndex());
        $qty *= 1;
        
        if(!$qty) {
            $qty = '';
        }
        
        $html = '<input type="text" ';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . $qty . '" ';
        $html .= 'class="input-text admin__control-text ' . $this->getColumn()->getInlineCss() . '" />';
        
        return $html;
    }
    
}
