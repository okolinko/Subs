<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/12/16
 * Time: 4:55 PM
 */

namespace Toppik\Subscriptions\Migration\Step\Orders;


use Magento\Framework\DataObject;
use Migration\App\Step\StageInterface;
use Migration\App\ProgressBar;
use Migration\RecordTransformerFactory;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\Reader\MapFactory;
use Migration\Logger\Logger;
use Magento\Braintree\Gateway\Config\Config;
use Migration\Logger\Manager as LogManager;
use Toppik\Subscriptions\Model\Profile;
use Toppik\Subscriptions\Model\ProfileFactory;
use Toppik\Subscriptions\Model\Settings\Unit;
use Zend\Json\Json;

class Data implements StageInterface
{

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progress;
    /**
     * @var ResourceModel\Source
     */
    private $source;
    /**
     * @var ResourceModel\Destination
     */
    private $destination;
    /**
     * @var ResourceModel\RecordFactory
     */
    private $recordFactory;
    /**
     * @var RecordTransformerFactory
     */
    private $recordTransformerFactory;
    /**
     * @var MapFactory
     */
    private $mapFactory;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var ProfileFactory
     */
    private $profileFactory;
    /**
     * @var Unit
     */
    private $defaultUnit;

    /**
     * @param Unit $defaultUnit
     * @param ProfileFactory $profileFactory
     * @param Config $config
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param Logger $logger
     * @param Helper $helper
     * @internal param EncryptorInterface $encryptor
     * @internal param PaymentTokenRepository $paymentTokenRepository
     */
    public function __construct(
        Unit $defaultUnit,
        ProfileFactory $profileFactory,
        Config $config,
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory,
        Logger $logger,
        Helper $helper
    ) {
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->mapFactory = $mapFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->config = $config;
        $this->profileFactory = $profileFactory;
        $this->defaultUnit = $defaultUnit;
        $this->defaultUnit->load(8);
    }

    /**
     * Entry point. Run migration of SalesOrder structure.
     * @return bool
     */
    public function perform()
    {

        $totalPages = $this->helper->getTotalPages();
        $pageNumber = 1;
        $this->progress->start($totalPages, LogManager::LOG_LEVEL_INFO);

        while(! empty($bulk = $this->helper->getLinks($pageNumber ++))) {
            $incrementIds = $this->helper->getIncrementIds(array_map(function($item) {
                return $item['increment_id'];
            }, $bulk));
            $data = [];
            foreach($bulk as $recordData) {
                if(isset($incrementIds[$recordData['increment_id']])) {
                    $data[] = [
                        'profile_id' => $recordData['profile_id'],
                        'order_id' => $incrementIds[$recordData['increment_id']],
                    ];
                } else {
                    $this->logger->addWarning('Unable to find order with increment_id ' . $recordData['increment_id'] . ' for profile ' . $recordData['profile_id']);
                }
            }
            $this->helper->insertLinks($data);
            $this->progress->advance(LogManager::LOG_LEVEL_INFO);
        }

        $this->progress->finish(LogManager::LOG_LEVEL_INFO);

        return true;
    }

    private function _toJson($array) {
        $this->_cleanupArray($array);
        return Json::prettyPrint(Json::encode($array, true));
    }

    /**
     * Recursively cleanup array from objects
     * @param array &$array
     * @return void
     */
    private function _cleanupArray(&$array)
    {
        if (!$array or ! is_array($array)) {
            return;
        }
        foreach ($array as $key => $value) {
            if(is_array($value)) {
                $this->_cleanupArray($array[$key]);
            } elseif(! is_scalar($value)) {
                unset($array[$key]);
            }
        }
    }

}