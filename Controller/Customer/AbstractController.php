<?php
namespace Toppik\Subscriptions\Controller\Customer;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

abstract class AbstractController extends Action\Action
{
	
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
	
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;
	
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
	
    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }
	
	protected function _initProfile() {
        $id = (int) $this->getRequest()->getParam('id');
		
		if(!$id) {
			return false;
		}
		
        $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile')->load($id);
		
		if(!$model->getId()) {
			return false;
		}
		
		$session = $this->_objectManager->get('Magento\Customer\Model\Session');
		
		if((int) $model->getCustomerId() !== (int) $session->getCustomerId()) {
			return false;
		}
		
        $this->_objectManager->get('Magento\Framework\Registry')->register('current_profile', $model);
		
		return $model;
	}
	
    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = [])
    {
        /** @var \Magento\Framework\UrlInterface $urlBuilder */
        $urlBuilder = $this->_objectManager->create('Magento\Framework\UrlInterface');
        return $urlBuilder->getUrl($route, $params);
    }
	
    protected function _toJson($array) {
        $this->_cleanupArray($array);
        return Json::prettyPrint(Json::encode($array, true));
    }
	
    protected function _cleanupArray(&$array)
    {
        if(!$array || !is_array($array)) {
            return;
        }
		
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $this->_cleanupArray($array[$key]);
            } elseif(!is_scalar($value)) {
                unset($array[$key]);
            }
        }
    }
	
}
