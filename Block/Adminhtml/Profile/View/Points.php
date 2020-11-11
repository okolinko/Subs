<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\View;

class Points extends \Magento\Backend\Block\Widget\Grid\Extended {
    
    /**
     * @var \Toppik\Subscriptions\Model\ResourceModel\Profile\Points\CollectionFactory
     */
    private $collectionFactory;
    
    /**
     * @var Registry
     */
    private $registry;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
    
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;
    
    /**
     * Points constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Toppik\Subscriptions\Model\ResourceModel\Profile\Points\Collection $collectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Toppik\Subscriptions\Model\ResourceModel\Profile\Points\CollectionFactory $collectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
		$this->reportHelper = $reportHelper;
        $this->authSession = $authSession;
        parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _construct() {
        parent::_construct();
        $this->setId('profile_points_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection() {
        $collection = $this->collectionFactory->create();
        
        $isAllowed  = false;
        $role_id    = $this->reportHelper->getSaveRoleId();
        $role       = $this->authSession->getUser()->getRole();
        
        if((int) $role->getId() === 2) {
            $isAllowed = true;
        }
        
        if($role_id && $role_id === (int) $role->getId()) {
            $isAllowed = true;
        }
        
        if($isAllowed !== true) {
            $collection->addFieldToFilter('manager', 0);
        }
        
        $maxOnetimePoints = $this->reportHelper->getMaxOnetimePoints();
        
        if($maxOnetimePoints > 0) {
            $collection->getSelect()->columns(array('admin_points' => new \Zend_Db_Expr(sprintf('(%s - points)', $maxOnetimePoints))));
        }
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns() {
        $this->addColumn(
            'choose',
            [
                'header' => __('Choose'),
                'index' => 'choose',
                'html_name' => 'choose',
                'type' => ($this->reportHelper->getIsSingleMode() ? 'radio' : 'checkbox'),
                'values' => [],
                'filter' => false,
                'sortable' => false
            ]
        );
        
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'index' => 'id',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'points',
            [
                'header' => __('Points'),
                'index' => 'points',
                'type' => 'text'
            ]
        );
        
        if($this->reportHelper->getMaxOnetimePoints() > 0) {
            $this->addColumn(
                'admin_points',
                [
                    'header' => __('CSR Points'),
                    'index' => 'admin_points',
                    'type' => 'text'
                ]
            );
        }
        
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'description',
            [
                'header' => __('Description'),
                'index' => 'description',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'index' => 'position',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'action',
                'type' => 'text',
				'renderer' => 'Toppik\Subscriptions\Block\Adminhtml\Subscription\Grid\Renderer\Configure',
                'filter' => false,
                'sortable' => false
            ]
        );
        
        return parent::_prepareColumns();
    }
    
    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback() {
        return '
            function (grid, event) {
                if(!window.subscriptionPointsRegistry) {
                    window.subscriptionPointsRegistry = {};
                }
                
                var element     = jQuery(Event.element(event));
                
                if(!element[0] || !element[0].tagName) {
                    return false;
                }
                
                if(element[0].tagName.toLowerCase() == \'a\') {
                    return false;
                }
                
                var trElement       = element.parents(\'tr\');
                var inputs          = trElement.find(\'input\');
                var checkbox        = inputs.eq(0);
                var isInputCheckbox = element[0].tagName.toLowerCase() == \'input\' && (element[0].type == \'checkbox\' || element[0].type == \'radio\')
                var isSingle        = (jQuery.trim(checkbox.attr(\'type\').toLowerCase()) == \'radio\') ? true : false;
                
                if(isSingle === true) {
                    if(!isInputCheckbox && checkbox.is(\':checked\')) {
                        return false;
                    }
                    
                    inputs.attr(\'checked\', false);
                    trElement.parent().find(\'tr\').removeClass(\'selected-row\');
                    window.subscriptionPointsRegistry = {};
                    
                    checkbox.attr(\'checked\', true);
                    trElement.addClass(\'selected-row\');
                    var title = trElement.find(\'.col-title\').text();
                    var points = trElement.find(\'.col-points\').text();
                    window.subscriptionPointsRegistry[checkbox.val()] = {label: jQuery.trim(title), value: parseInt(jQuery.trim(points))};
                } else {
                    if((!isInputCheckbox && checkbox.is(\':checked\')) || (isInputCheckbox && !checkbox.is(\':checked\'))) {
                        checkbox.attr(\'checked\', false);
                        
                        if(window.subscriptionPointsRegistry[checkbox.val()] != undefined) {
                            window.subscriptionPointsRegistry[checkbox.val()] = null;
                            delete window.subscriptionPointsRegistry[checkbox.val()];
                            jQuery(trElement).removeClass(\'selected-row\');
                        }
                    } else {
                        checkbox.attr(\'checked\', true);
                        var title = trElement.find(\'.col-title\').text();
                        var points = trElement.find(\'.col-points\').text();
                        window.subscriptionPointsRegistry[checkbox.val()] = {label: jQuery.trim(title), value: parseInt(jQuery.trim(points))};
                        trElement.addClass(\'selected-row\');
                    }
                }
                
                jQuery(document).trigger(\'subscription:points:select:option\');
            }
        ';
    }
    
    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/pointsGrid', ['_current' => true]);
    }
    
}
