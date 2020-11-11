<?php
namespace Toppik\Subscriptions\Controller\Ajax\Item;

class Add extends \Magento\Framework\App\Action\Action {
    
    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
    */
    protected $_timezoneInterface;
	
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
	
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;
    
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $productHelper;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Helper\Product $productHelper
    ) {
		$this->_timezoneInterface = $timezoneInterface;
        $this->jsonFactory = $jsonFactory;
        $this->_customerSession = $customerSession;
        $this->authSession = $authSession;
        $this->productHelper = $productHelper;
        parent::__construct($context);
    }
    
    /**
     * Order view page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        $data = array();
        $json = $this->jsonFactory->create();
        
        try {
            if(!$this->_customerSession->isLoggedIn() || !$this->_customerSession->getCustomerId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please login your account to continue!'));
            }
            
            $profile_id = (int) $this->getRequest()->getParam('profile_id');
            $productId  = (int) $this->getRequest()->getParam('product');
            $params     = new \Magento\Framework\DataObject($this->getRequest()->getParams());
            
            if(!$profile_id || $profile_id < 1) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This profile no longer exists!'));
            }
            
            $profile    = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile')->load($profile_id);
            
            if(!$profile->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This profile no longer exists!'));
            }
            
            if((int) $profile->getCustomerId() !== (int) $this->_customerSession->getCustomerId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This profile no longer exists!'));
            }
            
            $product    = $this->productHelper->initProduct($productId, $this);
            
            if(!$product || !is_object($product)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Product not found!'));
            }
            
            $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($params, $product);
            
            /**
             * Error message
             */
            if(is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
                throw new \Magento\Framework\Exception\LocalizedException(__(strval($cartCandidates)));
            }
            
            /**
             * If prepare process return one object
             */
            if(!is_array($cartCandidates)) {
                $cartCandidates = [$cartCandidates];
            }
            
            $adminId = 0;
            
            if($this->_customerSession->getAdminId()) {
                $adminId = $this->_customerSession->getAdminId();
            }
            
            if($this->authSession && $this->authSession->getUser() && $this->authSession->getUser()->getId()) {
                $adminId = $this->authSession->getUser()->getId();
            }
            
            foreach($cartCandidates as $_candidate) {
                if($_candidate->getTypeId() == 'simple') {
                    $this->_objectManager->create('Toppik\Subscriptions\Model\Profile\Gift')
                        ->setData('profile_id', $profile_id)
                        ->setData('title', __('"%1" has been added by customer', $product->getName()))
                        ->setData('sku', $_candidate->getSku())
                        ->setData('price', $_candidate->getFinalPrice())
                        ->setData('qty', ($params->getQty() > 0 ? $params->getQty() : 1))
                        ->setData('admin_id', $adminId)
                        ->setData('customer_id', $this->_customerSession->getCustomerId())
                        ->setData('ip', (isset($_SERVER['REMOTE_ADDR']) ? ip2long($_SERVER['REMOTE_ADDR']) : null))
                        ->save();
                    
                    $data['success'] = true;
                    
                    $data['message'] = __(
                        '"%1" has been added to your subscription # %2 for your next order at %3',
                        $product->getName(),
                        $profile->getId(),
                        $this->_timezoneInterface->date(new \DateTime($profile->getNextOrderAt()))->format('M d, Y')
                    );
                }
            }
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $data['success'] = false;
            $data['message'] = $e->getMessage();
        } catch(\Exception $e) {
            $data['success'] = false;
            $data['message'] = __('Something went wrong while adding the item.');
        }
        
        $json->setData($data);
        
        return $json;
    }
    
}
