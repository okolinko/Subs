<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/29/16
 * Time: 8:16 PM
 */

namespace Toppik\Subscriptions\Cron;


use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;

class ProcessProfiles
{

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * ProcessProfiles constructor.
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ManagerInterface $eventManager
    )
    {
        $this->eventManager = $eventManager;
    }

    public function execute() {
        $result = new DataObject;
        $this->eventManager->dispatch('subscriptions_profiles_process', ['result' => $result, ]);
    }

}