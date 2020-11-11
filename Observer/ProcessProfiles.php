<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/29/16
 * Time: 1:11 PM
 */

namespace Toppik\Subscriptions\Observer;


use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Toppik\Subscriptions\Processor;

class ProcessProfiles implements ObserverInterface
{

    /**
     * @var Processor\SuspendedProfiles
     */
    private $suspendedProfiles;
    /**
     * @var Processor\ActiveProfiles
     */
    private $activeProfiles;

    /**
     * ProcessProfiles constructor.
     * @param Processor\SuspendedProfiles $suspendedProfiles
     * @param Processor\ActiveProfiles $activeProfiles
     */
    public function __construct(
        Processor\SuspendedProfiles $suspendedProfiles,
        Processor\ActiveProfiles $activeProfiles
    )
    {
        $this->suspendedProfiles = $suspendedProfiles;
        $this->activeProfiles = $activeProfiles;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /* @var DataObject $result */
        $result = $observer->getResult();

        $this->suspendedProfiles->execute();

        $this->activeProfiles->execute();

    }
}