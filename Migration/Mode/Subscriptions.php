<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/12/16
 * Time: 5:56 PM
 */

namespace Toppik\Subscriptions\Migration\Mode;


use Migration\App\Mode\ModeInterface;
use Migration\App\Mode\StepListFactory;
use Migration\Config;
use Migration\Mode\AbstractMode;
use Migration\App\Progress;
use Migration\Logger\Logger;
use Migration\App\SetupDeltaLog;
use Migration\App\Mode\StepList;
use Migration\Exception;
use Migration\App\Step\RollbackInterface;

class Subscriptions extends AbstractMode implements ModeInterface
{
    /**
     * @inheritdoc
     */
    protected $mode = 'subscriptions';

    /**
     * @var SetupDeltaLog
     */
    protected $setupDeltaLog;

    /**
     * @var Config
     */
    protected $configReader;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param StepListFactory $stepListFactory
     * @param SetupDeltaLog $setupDeltaLog
     * @param Config $configReader
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        StepListFactory $stepListFactory,
        SetupDeltaLog $setupDeltaLog,
        Config $configReader
    ) {
        parent::__construct($progress, $logger, $stepListFactory);
        $this->setupDeltaLog = $setupDeltaLog;
        $this->configReader = $configReader;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        /** @var StepList $steps */
        $steps = $this->stepListFactory->create(['mode' => $this->mode]);
        $this->runIntegrity($steps);
        $this->setupDeltalog();

        foreach ($steps->getSteps() as $stepName => $step) {
            if (empty($step['data'])) {
                continue;
            }
            $this->runData($step, $stepName);
            if (!empty($step['volume'])) {
                $this->runVolume($step, $stepName);
            }
        }

        $this->logger->info('Migration completed');
        return true;
    }

    /**
     * @param StepList $steps
     * @throws Exception
     * @return void
     */
    protected function runIntegrity(StepList $steps)
    {
        $result = true;
        foreach ($steps->getSteps() as $stepName => $step) {
            if (!empty($step['integrity'])) {
                $result = $this->runStage($step['integrity'], $stepName, 'integrity check') && $result;
            }
        }
        if (!$result) {
            throw new Exception('Integrity Check failed');
        }
    }

    /**
     * Setup triggers
     * @throws Exception
     * @return void
     */
    protected function setupDeltalog()
    {
        if (!$this->runStage($this->setupDeltaLog, 'Stage', 'setup triggers')) {
            throw new Exception('Setup triggers failed');
        }
    }

    /**
     * @param array $step
     * @param string $stepName
     * @throws Exception
     * @return void
     */
    protected function runData(array $step, $stepName)
    {
        if (!$this->runStage($step['data'], $stepName, 'data migration')) {
            $this->rollback($step['data'], $stepName);
            throw new Exception('Data Migration failed');
        }
    }

    /**
     * @param array $step
     * @param string $stepName
     * @throws Exception
     * @return void
     */
    protected function runVolume(array $step, $stepName)
    {
        if (!$this->runStage($step['volume'], $stepName, 'volume check')) {
            $this->rollback($step['data'], $stepName);
            if ($this->configReader->getStep('delta', $stepName)) {
                $this->logger->warning('Volume Check failed');
            } else {
                throw new Exception('Volume Check failed');
            }
        }
    }

    /**
     * @param RollbackInterface $stage
     * @param string $stepName
     * @return void
     */
    protected function rollback($stage, $stepName)
    {
        if ($stage instanceof RollbackInterface) {
            $this->logger->info('Error occurred. Rollback.');
            $this->logger->info(sprintf('%s: rollback', $stepName));
            try {
                $stage->rollback();
            } catch (\Migration\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            $this->progress->reset($stage);
            $this->logger->info('Please fix errors and run Migration Tool again');
        }
    }
}