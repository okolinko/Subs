<?php
namespace Toppik\Subscriptions\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Cart extends AbstractHelper
{
	
	protected $_cartPaypalMessage = null;
	protected $_subscriptionItems = array();
	
	public function setCartPaypalMessage($message) {
		$this->_cartPaypalMessage = $message;
	}
	
	public function getCartPaypalMessage() {
		return $this->_cartPaypalMessage;
	}
	
	public function setSubscriptionItems($items) {
		if(is_array($items)) {
			foreach($items as $_item) {
				if(!in_array($_item, $this->_subscriptionItems)) {
					$this->_subscriptionItems[] = $_item;
				}
			}
		}
	}
	
	public function getSubscriptionItems() {
		return $this->_subscriptionItems;
	}
	
}
