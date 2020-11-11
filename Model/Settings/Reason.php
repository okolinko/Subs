<?php
namespace Toppik\Subscriptions\Model\Settings;

class Reason extends \Magento\Framework\Model\AbstractModel {

    /**
     * @var array
     */
    protected $_options;

    const OPTION_1 = 1;
    const OPTION_2 = 2;
    const OPTION_3 = 3;
    const OPTION_4 = 4;
    const OPTION_5 = 5;
    const OPTION_6 = 6;
    const OPTION_7 = 7;
    const OPTION_8 = 8;
    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions() {
        if($this->_options === null) {
            $this->_options = [
                ['label' => __('Changed mind'), 'value' => self::OPTION_1],
                ['label' => __('Not aware of subscription'), 'value' => self::OPTION_2],
                ['label' => __('Too expensive'), 'value' => self::OPTION_3],
                ['label' => __('Too much product'), 'value' => self::OPTION_4],
                ['label' => __('Order placed in error'), 'value' => self::OPTION_5],
                ['label' => __('Duplicate Order'), 'value' => self::OPTION_6],
                ['label' => __('Prefer to purchase elsewhere'), 'value' => self::OPTION_7],
                ['label' => __('Wrong item purchased'), 'value' => self::OPTION_8]

            ];
        }

        return $this->_options;
    }

    public function toOptionArray() {
        $options = array();
        $options[] = ['label' => '', 'value' => ''];

        foreach($this->getAllOptions() as $option) {
            $options[] = ['label' => (string) $option['label'], 'value' => $option['value']];
        }

        return $options;
    }

}
