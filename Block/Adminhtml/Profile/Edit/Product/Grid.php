<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\Edit\Product;

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
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
	
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        parent::__construct($context, $backendHelper, $data);
    }
    
    public function getRequireJsDependencies() {
        return [
            'Magento_Catalog/catalog/product/composite/configure',
            'Toppik_Subscriptions/js/product/configure'
        ];
    }
    
    public function getAdditionalJavaScript() {
        $params = array();
        $params['store_id'] = $this->getStoreIdForProfile();
        
        return 'if(window.productConfigure) {
            productConfigure.addListType("product_to_add", {
                urlFetch: "' . $this->getUrl('subscriptions/profiles/configureProductToAdd', $params) . '"
            });
        }';
    }
    
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('profile_edit_product');
        
        $this->setRowClickCallback('subscriptionProductConfigure.productGridRowClick.bind(subscriptionProductConfigure)');
        $this->setCheckboxCheckCallback('subscriptionProductConfigure.productGridCheckboxCheck.bind(subscriptionProductConfigure)');
        $this->setRowInitCallback('subscriptionProductConfigure.productGridRowInit.bind(subscriptionProductConfigure)');
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
            ->setStore($this->getStoreIdForProfile())
            ->addAttributeToSelect($attributes)
            ->addAttributeToSelect('sku')
            ->addStoreFilter($this->storeManager->getStore($this->getStoreIdForProfile()))
            ->addAttributeToFilter('type_id', \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            ->addAttributeToFilter(
                'status',
                ['eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]
            );
        
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
                'renderer' => 'Toppik\Subscriptions\Block\Adminhtml\Profile\Edit\Product\Grid\Renderer\Product',
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
                'renderer' => '\Toppik\Subscriptions\Block\Adminhtml\Profile\Edit\Product\Grid\Renderer\Qty',
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
        
        return parent::_prepareColumns();
    }
    
    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl() {
        $params = array('_current' => true);
        $params['store_id'] = $this->getStoreIdForProfile();
        return $this->getUrl('*/*/ProductChooserGrid', $params);
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
    
    public function getStoreIdForProfile() {
        $profile = $this->registry->registry('profile');
        return $profile ? $profile->getStoreId() : $this->getStoreId();
    }
    
}
