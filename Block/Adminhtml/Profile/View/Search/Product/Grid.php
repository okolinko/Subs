<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\View\Search\Product;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {
    
    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Sales config
     *
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Sales\Model\Config $salesConfig,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_salesConfig = $salesConfig;
        parent::__construct($context, $backendHelper, $data);
    }
    
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('profile_points_product_search_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }
    
    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column) {
        // Set custom filter for in product flag
        if($column->getId() == 'in_products') {
            $productIds = 0;
            
            if($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        
        return $this;
    }
    
    /**
     * Prepare collection to be displayed in the grid
     *
     * @return $this
     */
    protected function _prepareCollection() {
        $attributes = $this->_catalogConfig->getProductAttributes();
        
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productFactory->create()->getCollection();
        
        $collection
            ->setStore(0)
            ->addAttributeToSelect($attributes)
            ->addAttributeToSelect('sku')
            ->addStoreFilter()
            ->addAttributeToFilter('save_the_sale', 1);
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns() {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'index' => 'entity_id'
            ]
        );
        
        $this->addColumn(
            'name',
            [
                'header' => __('Product'),
                'renderer' => 'Toppik\Subscriptions\Block\Adminhtml\Profile\View\Search\Product\Grid\Renderer\Product',
                'index' => 'name'
            ]
        );
        
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        
        $this->addColumn(
            'in_products',
            [
                'header' => __('Select'),
                'type' => 'radio',
                'html_name' => 'choose',
                'name' => 'in_products',
                'values' => [],
                'index' => 'entity_id',
                'sortable' => false,
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );
        
        $this->addColumn(
            'qty',
            [
                'filter' => false,
                'sortable' => false,
                'header' => __('Quantity'),
                'renderer' => '\Toppik\Subscriptions\Block\Adminhtml\Profile\View\Search\Product\Grid\Renderer\Qty',
                'name' => 'qty',
                'inline_css' => 'qty',
                'type' => 'input',
                'validate_class' => 'validate-number',
                'index' => 'qty'
            ]
        );
        
        $this->addColumn(
            'price_value',
            [
                'header' => __('Price'),
                'column_css_class' => 'price',
                'type' => 'currency',
                'index' => 'price',
                'renderer' => 'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price'
            ]
        );
        
        $this->addColumn(
            'price',
            [
                'filter' => false,
                'sortable' => false,
                'header' => __('Price'),
                'name' => 'price',
                'inline_css' => 'input-text admin__control-text',
                'type' => 'input',
                'validate_class' => 'validate-price',
                'index' => 'price_zero'
            ]
        );
        
        return parent::_prepareColumns();
    }
    
    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/productGrid', ['_current' => true]);
    }
    
    /**
     * Add custom options to product collection
     *
     * @return $this
     */
    protected function _afterLoadCollection() {
        $this->getCollection()->addOptionsToResult();
        return parent::_afterLoadCollection();
    }
    
}
