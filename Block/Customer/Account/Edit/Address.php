<?php
namespace Toppik\Subscriptions\Block\Customer\Account\Edit;

use Magento\Framework\ObjectManagerInterface;

class Address extends \Magento\Customer\Block\Address\Edit
{
	
    /**
     * @var string
     */
    protected $_template = 'address/edit.phtml';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
	
    protected function getType() {
		return trim(strtolower($this->getRequest()->getParam('type')));
    }
	
    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
		$this->registry = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Registry');
		$this->_address = $this->addressDataFactory->create();
		$address = null;
		$region = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Api\Data\RegionInterfaceFactory')->create();
        switch($this->getType()) {
            case 'shipping':
				$address = $this->getProfile()->getShippingAddress();
				$region->setRegionId($address->getRegionId())->setRegion($address->getRegion());
				$address->setData('street', array($address->getStreet()));
				$address->setData('region', $region);
                break;
            case 'billing':
				$address = $this->getProfile()->getBillingAddress();
				$region->setRegionId($address->getRegionId())->setRegion($address->getRegion());
				$address->setData('street', array($address->getStreet()));
				$address->setData('region', $region);
                break;
        }
		
		if($address) {
			$this->dataObjectHelper->populateWithArray(
				$this->_address,
				$address->getData(),
				'\Magento\Customer\Api\Data\AddressInterface'
			);
		}
		
        $this->pageConfig->getTitle()->set($this->getTitle());
		
        if ($postedData = $this->_customerSession->getAddressFormData(true)) {
            $postedData['region'] = [
                'region_id' => $postedData['region_id'],
                'region' => $postedData['region'],
            ];
            $this->dataObjectHelper->populateWithArray(
                $this->_address,
                $postedData,
                '\Magento\Customer\Api\Data\AddressInterface'
            );
        }
		
        return $this;
    }
	
    public function getProfile()
    {
		return $this->registry->registry('current_profile');
    }
	
    public function getTitle()
    {
		$title = '';
		
        switch($this->getType()) {
            case 'shipping':
                $title = __('Edit Shipping Address');
                break;
            case 'billing':
                $title = __('Edit Billing Address');
                break;
        }
		
        return $title;
    }
	
    /**
     * Return the Url to go back.
     *
     * @return string
     */
    public function getBackUrl()
    {
		return $this->getUrl('subscriptions/customer/view', array('id' => $this->getProfile()->getId()));
    }
	
    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {       
		return $this->getUrl('subscriptions/customer/addressPost', array('_secure' => true, 'id' => $this->getProfile()->getId(), 'type' => $this->getType()));
    }
	
    /**
     * Determine if the address can be set as the default billing address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultBilling()
    {
		return false;
    }
	
    /**
     * Determine if the address can be set as the default shipping address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultShipping()
    {
		return false;
    }
	
    /**
     * Is the address the default billing address?
     *
     * @return bool
     */
    public function isDefaultBilling()
    {
		return false;
    }
	
    /**
     * Is the address the default shipping address?
     *
     * @return bool
     */
    public function isDefaultShipping()
    {
		return false;
    }
	
}
