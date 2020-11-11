<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 1:07 PM
 */

namespace Toppik\Subscriptions\Model\Profile\Source;


use Magento\Framework\Data\OptionSourceInterface;
use Toppik\Subscriptions\Model\Profile;

class Status implements OptionSourceInterface
{

    /**
     * @var Profile
     */
    protected $profile;

    public function __construct(Profile $profile) {
        $this->profile = $profile;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => '',];
        $availableOptions = $this->profile->getAvailableStatuses();
        foreach($availableOptions as $k => $v) {
            $options[] = [
                'label' => $v,
                'value' => $k,
            ];
        }
        return $options;
    }
}