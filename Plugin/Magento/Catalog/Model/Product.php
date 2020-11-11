<?php
namespace Toppik\Subscriptions\Plugin\Magento\Catalog\Model;

class Product {
    
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }
    
    public function aroundGetOptions(
        \Magento\Catalog\Model\Product $original,
        callable $proceed
    ) {
		try {
            $this->eventManager->dispatch(
                'catalog_product_init_custom_options_subscriptions',
                ['product' => $original]
            );
		} catch(\Exception $e) {}
        
        $result = $proceed();
        
        return $result;
    }
    
}
