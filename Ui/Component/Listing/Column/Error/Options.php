<?php
namespace Toppik\Subscriptions\Ui\Component\Listing\Column\Error;

class Options implements \Magento\Framework\Data\OptionSourceInterface {
	
    /**
     * @var array
     */
    protected $options;
	
    /**
     * @var \Toppik\Subscriptions\Processor\ProcessDrtvCs
     */
    private $model;
	
    /**
     * ProcessProfiles constructor.
     * @param \Toppik\Subscriptions\Processor\ProcessDrtvCs $model
     */
    public function __construct(
		\Toppik\Subscriptions\Model\Settings\Error $model
    ) {
        $this->model = $model;
    }
	
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray() {
        if($this->options === null) {
            $this->options = $this->model->toOptionArray();
        }
		
        return $this->options;
    }
	
}
