<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 1:07 PM
 */

namespace Toppik\Subscriptions\Model\Settings\Period\Source;


use Magento\Framework\Data\OptionSourceInterface;
use Toppik\Subscriptions\Model\Settings\Period;

class EngineCode implements OptionSourceInterface
{

    /**
     * @var Period
     */
    protected $period;

    public function __construct(Period $period) {
        $this->period = $period;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => '',];
        $availableOptions = $this->period->getAvailableEngineCodes();
        foreach($availableOptions as $k => $v) {
            $options[] = [
                'label' => $v,
                'value' => $k,
            ];
        }
        return $options;
    }
}