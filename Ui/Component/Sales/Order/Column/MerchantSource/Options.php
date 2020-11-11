<?php
namespace Toppik\Subscriptions\Ui\Component\Sales\Order\Column\MerchantSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface {
	
    protected $_merchantSource;
	
    /**
     * @var array
     */
    protected $options;
	
    /**
     * @param \Toppik\OrderSource\Model\Merchant\Source $merchantSource
     */
    public function __construct(\Toppik\OrderSource\Model\Merchant\Source $merchantSource) {
        $this->_merchantSource = $merchantSource;
    }
	
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray() {
        if($this->options === null) {
            $result = [];
			
            foreach($this->_merchantSource->getAllOptions() as $_source) {
				if(isset($_source['value']) && isset($_source['label'])) {
					$result[] = [
									'value' => $_source['value'],
									'label' => $_source['label']
								];
				}
            }
			
            $this->options = $result;
        }
		
        return $this->options;
    }
	
}
