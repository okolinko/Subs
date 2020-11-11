<?php
namespace Toppik\Subscriptions\Block\Customer\Account\Edit;

use Braintree;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Braintree\Model\Ui\TokenUiComponentProvider;

class Cc extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var string
     */
    protected $_template = 'customer/account/edit/cc.phtml';
	
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
	
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    protected $_cards;
	
    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @var CustomerTokenManagement
     */
    private $customerTokenManagement;

    /**
     * @var TokenUiComponentProvider
     */
    private $tokenUiComponentProvider;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
	
    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeAdapterFactory
     */
    private $_braintreeAdapterFactory;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerTokenManagement $customerTokenManagement,
        TokenUiComponentProvider $tokenUiComponentProvider,
		\Magento\Framework\Registry $registry,
        PaymentTokenManagement $paymentTokenManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Braintree\Model\Adapter\BraintreeAdapterFactory $braintreeAdapterFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
		$this->registry = $registry;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->customerTokenManagement = $customerTokenManagement;
        $this->tokenUiComponentProvider = $tokenUiComponentProvider;
        $this->storeManager = $storeManager;
        $this->_braintreeAdapterFactory = $braintreeAdapterFactory;
        parent::__construct($context, $data);
    }
	
    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->pageConfig->getTitle()->set($this->getTitle());
		$this->_initCards();
    }
    
    protected function _initCards() {
        if($this->_customerSession->getCustomerId()) {
            if(!$this->_cards) {
                $this->_cards = array();

                $tokens = $this->customerTokenManagement->getCustomerSessionTokens();

                foreach($tokens as $i => $token) {
                    $paymentCode = $token->getPaymentMethodCode();
                    
                    if($paymentCode !== 'braintree') {
                        continue;
                    }
                    
                    $component = $this->tokenUiComponentProvider->getComponentForToken($token);
                    $_tokenDetails = $component->getConfig()['details'];
                    $_tokenDetails['id'] = $token->getId();
                    $_tokenDetails['gateway_token'] = $token->getData('gateway_token');
                    $_tokenDetails['public_hash'] = $token['public_hash'];
                    $this->_cards[] = $_tokenDetails;
                }
            }
        }
    }
	
    public function getProfile() {
		return $this->registry->registry('current_profile');
    }
    
    public function getTitle() {
		return __('Edit Subscription # %1', $this->getProfile()->getId());
    }
    
    public function getCreditCardList() {
        return $this->_cards;
    }
	
    public function hasSavedCards() {
        return count($this->_cards) > 0;
    }
	
    public function isCardAssignedToProfile() {
        if($this->hasSavedCards()) {
            foreach($this->_cards as $_card) {
                if((int) $_card['id'] === (int) $this->getProfile()->getPaymentTokenId()) {
					return true;
                }
            }
        }
		
		return false;
    }
	
    public function hasMoreThanOneCard() {
        return count($this->_cards) > 1;
    }
	
}
