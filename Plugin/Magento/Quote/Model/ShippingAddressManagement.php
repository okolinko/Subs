<?php
namespace Toppik\Subscriptions\Plugin\Magento\Quote\Model;

class ShippingAddressManagement {
	
    /**
     * Customer repository.
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
	
    /**
     * Constructs a quote shipping address validator service object.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository Customer repository.
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }
	
    public function beforeAssign(\Magento\Quote\Model\ShippingAddressManagement $original, $cartId, \Magento\Quote\Api\Data\AddressInterface $address) {
		try {
			if($address->getCustomerAddressId()) {
				$applicableAddressIds 	= array();
				$addresses 				= $this->customerRepository->getById($address->getCustomerId())->getAddresses();
				
				if(is_array($addresses)) {
					foreach($addresses as $_address) {
						$applicableAddressIds[] = $_address->getId();
					}
				}
				
				if(!in_array($address->getCustomerAddressId(), $applicableAddressIds)) {
					\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->addNotice(
						__(
							'QuoteAddressValidator -> ShippingAddressManagement -> Invalid customer address id %1 for customer %2',
							$address->getCustomerAddressId(),
							$address->getCustomerId()
						)
					);
					
					$address->setCustomerAddressId(null);
				}
			}
		} catch(\Exception $e) {
			\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->addNotice(
				__(
					'QuoteAddressValidator -> ShippingAddressManagement -> Error -> %1',
					$e->getMessage()
				)
			);
		}
		
		return [$cartId, $address];
    }
	
}
