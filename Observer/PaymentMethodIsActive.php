<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/26/16
 * Time: 8:47 PM
 */

namespace Toppik\Subscriptions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Model\Quote;
use Toppik\Subscriptions\Model\Settings\ItemFactory;

class PaymentMethodIsActive implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /* @var Quote $quote */
        $quote = $observer->getQuote();
		
        if(is_null($quote)) {
            return;
        }
		
        /* @var AbstractMethod $pm */
        $pm = $observer->getMethodInstance();
        $subscriptionPayments = [];
		$subscriptionItems = [];
		
        foreach($quote->getAllItems() as $item) {
            /* @var Quote\Item $item */
            if($item->getLinkedChildQuoteItem()) {
                /* @var \Toppik\Subscriptions\Model\Settings\Item $subscriptionItem */
                $subscriptionItem = $item->getSubscriptionItem();
				
                if($subscriptionItem === false) {
                    continue;
                }
				
				if(!$item->getParentItemId()) {
					$subscriptionItems[] = $item->getName();
				}
				
                $engineCode = $subscriptionItem->getPeriod()->getEngineCode();
				
                if(! in_array($engineCode, $subscriptionPayments)) {
                    $subscriptionPayments[] = $engineCode;
                }
            }
        }
		
		/* @var DataObject $result */
		$result = $observer->getResult();
		
        if(!empty($subscriptionPayments)) {
            if(count($subscriptionPayments) > 1) {
                $result->setIsAvailable(false);
            } else {
                $engineCode = array_shift($subscriptionPayments);
                if($pm->getCode() !== $engineCode) {
                    $result->setIsAvailable(false);
                }
            }
        }
		
		$config 		= \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface');
		$paymentCode 	= $config->getValue('subscriptions_settings/paypal_payment/payment_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		if($pm->getCode() == $paymentCode) {
			$message 	= null;
			
			if($result->getIsAvailable() === true) {
				$message = $config->getValue('subscriptions_settings/paypal_payment/eligible_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			} else {
				$message = $config->getValue('subscriptions_settings/paypal_payment/non_eligible_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			}
			
			if($message && !empty($message)) {
				$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Toppik\Subscriptions\Helper\Cart');
				$helper->setCartPaypalMessage($message);
				$helper->setSubscriptionItems($subscriptionItems);
			}
		}
    }
	
}
