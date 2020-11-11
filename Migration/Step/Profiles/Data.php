<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/12/16
 * Time: 4:55 PM
 */

namespace Toppik\Subscriptions\Migration\Step\Profiles;


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
        $this->defaultUnit->load(86400, Unit::LENGTH);
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

        while(! empty($bulk = $this->helper->getProfiles($pageNumber ++))) {
            foreach($bulk as $recordData) {
                $this->importProfile($recordData);
            }
            $this->progress->advance(LogManager::LOG_LEVEL_INFO);
        }

        $this->progress->finish(LogManager::LOG_LEVEL_INFO);

        return true;
    }

    /**
     * @param array $recordData
     * @return void
     */
    private function importProfile($recordData)
    {
        try {
            /* @var Profile $profile */
            $profile = $this->profileFactory->create();
            $profile->setCustomerId($recordData['customer_id']);
            $paymentToken = $this->helper->getPaymentTokenByGatewayToken($recordData['reference_id'], $recordData['subscription_engine_code'], $recordData['customer_id']);
            if(! $paymentToken) {
                $additionalMessage = 'Credit card for profile ' . $recordData['entity_id'] . ' with gateway_token "' . $recordData['reference_id'] . '" missing.';
                $paymentTokenId = null;
            } else {
                $additionalMessage = '';
                $paymentTokenId = $paymentToken->getId();
            }
            $initialDetails = unserialize($recordData['initial_details']);
            $initialDetails['shipping_address']['payment_method'] = 'braintree_cc_vault';

            $profile->setId($recordData['entity_id']);
            $profile->setPaymentTokenId($paymentTokenId);
            $profile->setGrandTotal($recordData['amount']);
            $profile->setBaseGrandTotal($recordData['amount']);
            $profile->setCreatedAt($recordData['created_at']);
            $profile->setUpdatedAt($recordData['updated_at']);
            $profile->setResumeAt($recordData['resume_at']);
            $profile->setStartDate($recordData['start_date']);
            $profile->setLastOrderId($this->helper->getOrderIdByIncrementId($recordData['increment_id']));
            $profile->setLastOrderAt($recordData['last_order_date']);
            $profile->setStatus($recordData['status']);
            $profile->setLastSuspendError($recordData['last_suspend_error']);
            $profile->setBillingAddressJson($this->_toJson($initialDetails['billing_address']));
            $profile->setShippingAddressJson($this->_toJson($initialDetails['shipping_address']));
            $profile->setItemsJson($this->_toJson($initialDetails['order_item_info']));
            $profile->setQuoteJson($this->_toJson($initialDetails['order_info']));
            $profile->setSubscriptionUnitJson($this->_toJson($this->defaultUnit->getData()));
            $profile->setSubscriptionPeriodJson($this->_toJson([
                'engine_code' => $initialDetails['subscription']['type']['engine_code'],
                'title' => $initialDetails['subscription']['type']['title'],
                'is_visible' => $initialDetails['subscription']['type']['is_visible'],
                'store_ids' => '0',
                'length' => $initialDetails['subscription']['type']['period_length'],
                'is_infinite' => $initialDetails['subscription']['type']['period_is_infinite'],
                'number_of_occurrences' => $initialDetails['subscription']['type']['period_number_of_occurrences'],
                'unit_id' => $this->defaultUnit->getId(),
            ]));
            $profile->setSubscriptionItemJson($this->_toJson([
                'regular_price' => $initialDetails['subscription']['item']['regular_price'],
                'sort_order' => $initialDetails['subscription']['item']['sort_order'],
                'use_coupon_code' => 1,
            ]));
            $profile->setSubscriptionJson($this->_toJson([
                'product_id' => $initialDetails['subscription']['general']['product_id'],
                'is_subscription_only' => $initialDetails['subscription']['general']['is_subscription_only'],
                'move_customer_to_group_id' => $initialDetails['subscription']['general']['move_customer_to_group_id'],
                'start_date_code' => $initialDetails['subscription']['general']['start_date_code'],
                'day_of_month' => $initialDetails['subscription']['general']['day_of_month'],
            ]));
            if(! empty($recordData['first_order_cookies'])) {
                $profile->setFirstOrderCookiesJson(
                    $this->_toJson(unserialize($recordData['first_order_cookies']))
                );
            }
            if(empty($recordData['last_order_date'])) {
                $profile->scheduleNextOrder();
            } else {
                $time = strtotime($recordData['last_order_date']);
                /* @var DataObject $period */
                $period = $profile->getSubscriptionPeriod();
                /* @var DataObject $unit */
                $unit = $profile->getSubscriptionUnit();
                if($period instanceof DataObject and $unit instanceof DataObject) {
                    $time += $period->getLength() * $unit->getLength();
                } else {
                    throw new \Exception('Unknown period data');
                }
                $profile->setNextOrderAt(date('Y-m-d H:i:s', $time));
            }

            $resource = $profile->getResource();
            $resource
                ->getConnection()
                ->insert($resource->getMainTable(), $resource->prepareDataForSave($profile));
            $this->logger->addInfo('Profile ' . $profile->getId() . ' with status ' . $profile->getStatus() . ' is saved. ' . $additionalMessage);
        } catch (\Exception $e) {
            $this->logger->addWarning($e->getMessage());
        }
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