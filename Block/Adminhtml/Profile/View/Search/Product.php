<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\View\Search;

class Product extends \Magento\Backend\Block\Widget {
    
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('profile_points_product_search');
    }
    
    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText() {
        return __('Please select product');
    }
    
}
