<?php
namespace Toppik\Subscriptions\Block\Customer\Account\View;

class Purchase extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var Data
     */
    protected $_subscriptionHelper;
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        array $data = []
    ) {
		$this->registry = $registry;
        $this->objectManager = $objectManager;
		$this->_subscriptionHelper = $subscriptionHelper;
        parent::__construct($context, $data);
    }
    
    public function getProfile() {
		return $this->registry->registry('current_profile');
    }
    
    public function getInfoBoxTitle() {
        return __('Purchased Item');
    }
    
    public function getInfoBoxFields() {
        $fields = array();
        
        foreach($this->getProfile()->getAllVisibleItems() as $_item) {
            $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($_item->getProductId());
            
            if((int) $_item->getIsOnetimeGift() === 1) {
                $fields[] = array(
                    'title' => '<hr />',
                    'value' => '<hr />'
                );
            }
            
            $fields[] = array(
                'title' => __('Product Name:'),
                'value' => $_item->getName(),
                'url'   => $product->getProductUrl()
            );
            
            $fields[] = array(
                'title' => __('SKU:'),
                'value' => $_item->getSku()
            );
            
            $fields[] = array(
                'title' => __('Quantity:'),
                'value' => (int) $_item->getQty() . ($this->getProfile()->canEditQuantityOfItem($_item) ? ' <a href="' . $this->getUrl('subscriptions/customer/quantity', array('id' => $this->getProfile()->getId())) . '"><u>(' . __('edit') . ')</u></a>' : '')
            );
            
            if((int) $_item->getIsOnetimeGift() === 1) {
                if($this->getProfile()->canRemoveOneTimeProduct()) {
                    $fields[] = array(
                        'title' => '',
                        'value' => '<a href="' . $this->getUrl('subscriptions/add/remove', array('id' => $this->getProfile()->getId(), 'item_id' => $_item->getId())) . '"><u>' . __('Remove') . '</u></a>'
                    );
                }
            } else {
                if($this->getProfile()->canEditProduct()) {
                    $fields[] = array(
                        'title' => '',
                        'value' => '<a href="' . $this->getUrl('subscriptions/customer/product', array('id' => $this->getProfile()->getId())) . '"><u>' . __('Change Product') . '</u></a>'
                    );
                }
            }
        }
        
		return $fields;
    }
    
}
