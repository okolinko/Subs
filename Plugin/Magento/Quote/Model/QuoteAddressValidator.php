<?php
namespace Toppik\Subscriptions\Plugin\Magento\Quote\Model;

class QuoteAddressValidator {
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
	
    public function beforeValidate(\Magento\Quote\Model\QuoteAddressValidator $original, \Magento\Quote\Api\Data\AddressInterface $addressData) {
		try {
			if($addressData->getCustomerAddressId()) {
				$applicableAddressIds 	= array();
				$addresses 				= $this->customerRepository->getById($addressData->getCustomerId())->getAddresses();
				
				if(is_array($addresses)) {
					foreach($addresses as $_address) {
						$applicableAddressIds[] = $_address->getId();
					}
				}
				
				if(!in_array($addressData->getCustomerAddressId(), $applicableAddressIds)) {
					\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->addNotice(
						__(
							'QuoteAddressValidator -> Invalid customer address id %1 for customer %2',
							$address->getCustomerAddressId(),
							$address->getCustomerId()
						)
					);
					
					$addressData->setCustomerAddressId(null);
				}
			}
		} catch(\Exception $e) {
			\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->addNotice(
				__(
					'QuoteAddressValidator -> Error -> %1',
					$e->getMessage()
				)
			);
		}
		
		return [$addressData];
    }
	
}
