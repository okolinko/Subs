<?php
namespace Toppik\Subscriptions\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfterObserver implements ObserverInterface
{
	
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    protected $messageManager;
	
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectmanager,
		\Magento\Framework\Message\ManagerInterface $messageManager
	) {
        $this->_objectManager = $objectmanager;
		$this->messageManager = $messageManager;
    }
	
    /**
     * Catalog Product After Save
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
			$product = $observer->getEvent()->getProduct();
			
			if($product->getId()) {
				$sourceSku = (string) $product->getOrigData('sku');
				$targetSku = (string) $product->getSku();
				
				if(!empty($sourceSku) && !empty($targetSku) && $sourceSku !== $targetSku) {
					$profileCollection = $this->_objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
					$totalChanged = $profileCollection->changeSku($sourceSku, $targetSku);
					
					if($totalChanged) {
						$this->messageManager->addSuccess(
							__('Changed sku from "' . $sourceSku . '" to "' . $targetSku . '" for ' . $totalChanged . ' profile(s).')
						);
					}
				}
			}
        } catch(LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch(\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while changing sku.'));
        }
		
		return $this;
    }
	
}
