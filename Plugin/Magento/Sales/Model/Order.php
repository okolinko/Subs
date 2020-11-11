<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/23/16
 * Time: 6:41 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\Sales\Model;


use Magento\Framework\DataObject;

class Order
{

    /**
     * @var string[]
     */
    private $subscriptionItemsHidden = [];

    /**
     * We should remove subscription items from tax count
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Api\Data\OrderExtensionInterface|null $result
     * @return \Magento\Sales\Api\Data\OrderExtensionInterface|null
     */
    public function afterGetExtensionAttributes(\Magento\Sales\Model\Order $order, $result) {
        if(! empty($this->subscriptionItemsHidden) and is_object($result)) {
            $class = get_class($result);
            $result = $result->__toArray();
            if(isset($result['item_applied_taxes']) and is_array($result['item_applied_taxes'])) {
                $result['item_applied_taxes'] = array_filter($result['item_applied_taxes'], [$this, 'filterSubscriptions'], ARRAY_FILTER_USE_BOTH);
            }
            $newResult = new $class($result);
            return $newResult;
        }
        return $result;
    }
    
    public function aroundCanReorder(\Magento\Sales\Model\Order $order, callable $proceed) {
        foreach($order->getItemsCollection() as $_item) {
			if(strpos($_item->getSku(), 'DRTV') !== false) {
				return false;
			}
            
            if((int) $_item->getIsSubscription() === 1) {
                return false;
            }
            
            if(!$_item->getProductOptionByCode('info_buyRequest')) {
                return false;
            }
        }
		
		return $proceed();
    }
    
    public function filterSubscriptions($item) {
        if(in_array($item['item_id'], $this->subscriptionItemsHidden)) {
            return false;
        }
        return true;
    }

    /**
     * @return $this
     */
    public function flushHiddenSubscriptions() {
        $this->subscriptionItemsHidden = [];
        return $this;
    }

    /**
     * @param $quoteItemId
     * @return $this
     */
    public function addHiddenSubscription($quoteItemId) {
        $this->subscriptionItemsHidden[] = $quoteItemId;
        return $this;
    }

}