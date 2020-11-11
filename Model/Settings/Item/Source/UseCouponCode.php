<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/1/16
 * Time: 3:53 PM
 */

namespace Toppik\Subscriptions\Model\Settings\Item\Source;


use Magento\Framework\Data\OptionSourceInterface;
use Toppik\Subscriptions\Model\Settings\Item;

class UseCouponCode implements OptionSourceInterface
{

    /**
     * @var Item
     */
    private $item;

    /**
     * PeriodId constructor.
     * @param Item $item
     */
    public function __construct(
        Item $item
    )
    {
        $this->item = $item;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => '',];
        $availableOptions = $this->item->getAvailableUseCouponCode();
        foreach($availableOptions as $k => $v) {
            $options[] = [
                'label' => $v,
                'value' => $k,
            ];
        }
        return $options;
    }
}