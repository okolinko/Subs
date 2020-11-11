<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/12/16
 * Time: 4:55 PM
 */

namespace Toppik\Subscriptions\Migration\Step\Orders;


use Migration\App\Step\AbstractIntegrity;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;
use Migration\ResourceModel;

class Integrity extends AbstractIntegrity
{

    /**
     * Integrity constructor.
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination)
    {
        $this->logger = $logger;
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * Returns number of iterations for integrity check
     *
     * @return mixed
     */
    protected function getIterationsCount()
    {
        return 0;
    }

    /**
     * Perform the stage
     *
     * @return bool
     */
    public function perform()
    {
        return true;
    }
}