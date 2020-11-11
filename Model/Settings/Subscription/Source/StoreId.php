<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 1:07 PM
 */

namespace Toppik\Subscriptions\Model\Settings\Subscription\Source;


use Magento\Framework\Data\OptionSourceInterface;
use Toppik\Subscriptions\Model\Settings\Subscription;

class StoreId implements OptionSourceInterface
{

    /**
     * @var Subscription
     */
    protected $subscription;

    public function __construct(Subscription $subscription) {
        $this->subscription = $subscription;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => '',];
        $availableOptions = $this->subscription->getAvailableStoreIds();
        foreach($availableOptions as $k => $v) {
            $options[] = [
                'label' => $v,
                'value' => $k,
            ];
        }
        return $options;
    }
}