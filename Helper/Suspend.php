<?php
namespace Toppik\Subscriptions\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Suspend extends AbstractHelper
{
	
    protected $_scopeConfig;
	
	protected $_options = null;
	
    /**
     * Data constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }
	
	public function getSuspendOptions() {
		
        if (is_null($this->_options)) {
        	$settings = $this->_scopeConfig->getValue('subscriptions_settings/general_options/suspend_periods');
        	$hours_array = explode(',', $settings); 
        	// Hours should be presented to the customer as days
			$this->_options = array(); 
			foreach ($hours_array as $hours) {
				$hours = (int)trim($hours);
				$days = floor($hours / 24);
				if ($hours % 24 != 0) {
					if ($hours < 24) {
				  		$label = "$hours hours";
					} else {
						$label = "$days days " . $hours % 24 . " hours";
					}
				} else {
					$label = $hours / 24 . " days";
				}
				$this->_options[] = array(
                    'label' => $label,
                    'value' => $hours
                );
			}
        }
        return $this->_options;
	}
	
}
