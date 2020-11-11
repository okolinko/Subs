<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/29/16
 * Time: 2:22 PM
 */

namespace Toppik\Subscriptions\Processor;

use Magento\Framework\ObjectManagerInterface;
use Toppik\Subscriptions\Model\Profile;
use Toppik\Subscriptions\Model\ResourceModel\Profile\Collection;
use Magento\Framework\Stdlib\DateTime\DateTime;

class SuspendedProfiles
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        ObjectManagerInterface $objectManager,
        DateTime $dateTime
    )
    {
        $this->objectManager = $objectManager;
        $this->dateTime = $dateTime;
    }

    public function execute() {
        /* @var Collection $profileCollection */
        $profileCollection = $this->objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
        $profileCollection
            ->addFieldToFilter(Profile::STATUS, Profile::STATUS_SUSPENDED)
            ->addFieldToFilter(Profile::RESUME_AT, ['notnull' => true, ])
            ->addFieldToFilter(Profile::RESUME_AT, ['lt' => $this->dateTime->gmtDate('Y-m-d H:i:s'), ]);
        foreach($profileCollection as $profile) {
            /* @var Profile $profile */
            $profile->changeStatusToActive(__('Status changed by SuspendedProfiles processor'));
        }
    }

}