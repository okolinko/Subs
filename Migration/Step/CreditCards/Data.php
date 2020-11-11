<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/12/16
 * Time: 4:55 PM
 */

namespace Toppik\Subscriptions\Migration\Step\CreditCards;


use Braintree\CreditCard;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Registry;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenRepository;
use Migration\App\Step\StageInterface;
use Migration\App\ProgressBar;
use Migration\RecordTransformerFactory;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\Reader\MapFactory;
use Migration\Logger\Logger;
use Migration\Logger\Manager as LogManager;
use Zend\Json\Json;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Framework\Encryption\EncryptorInterface;

class Data implements StageInterface
{

    const SOURCE_DOCUMENT = 'aw_sarp2_profile';

    /**
     * @var int
     */
    protected $pageSize = 1;

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
     * @var BraintreeAdapterFactory
     */
    private $braintreeAdapter;
    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;
    /**
     * @var PaymentTokenRepository
     */
    private $paymentTokenRepository;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     * @param DirectoryList $directoryList
     * @param EncryptorInterface $encryptor
     * @param Config $config
     * @param \Magento\Vault\Api\Data\PaymentTokenFactoryInterface $paymentTokenFactory
     * @param PaymentTokenRepository $paymentTokenRepository
     * @param BraintreeAdapterFactory $braintreeAdapter
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param Logger $logger
     * @param Helper $helper
     */
    public function __construct(
        Registry $registry,
        DirectoryList $directoryList,
        EncryptorInterface $encryptor,
        Config $config,
        \Magento\Vault\Api\Data\PaymentTokenFactoryInterface $paymentTokenFactory,
        PaymentTokenRepository $paymentTokenRepository,
        BraintreeAdapterFactory $braintreeAdapter,
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
        $this->braintreeAdapter = $braintreeAdapter->create();
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->directoryList = $directoryList;
        $this->registry = $registry;
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
        $references = [];
        while(! empty($bulk = $this->helper->getReferences($pageNumber ++))) {
            $references[] = implode(',', array_map(function($item) {
                return $item['reference_id'];
            }, $bulk));
            $this->progress->advance(LogManager::LOG_LEVEL_INFO);
        }
        $config = $this->registry->registry('config');
        $cmd = PHP_BINARY . ' ' . $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento migrate:cc';
        $exec = '(';
        foreach($references as $ref) {
            $exec .= "$cmd $ref $config & ";
        }
        $exec .= 'wait)';
        $this->logger->addInfo('Started to import references.');

        // Workaround!!
        $fname = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cc.sh';
        file_put_contents($fname, '#!/bin/bash' . "\n" . $exec);
        chmod($fname, 0777);
        $output = shell_exec($fname);
        unlink($fname);

        $output = array_filter(explode("\n", $output), function($item) {
            return !empty($item);
        });

        $this->logger->addInfo('Finished to import references: ' . count($output) . '.');

        $this->logger->addInfo('Started to save references');

        foreach($output as $refData) {
            $this->saveReference(Json::decode($refData, Json::TYPE_ARRAY));
        }

        $this->progress->finish(LogManager::LOG_LEVEL_INFO);

        return true;
    }

    /**
     * Saves reference in vault
     * @param string $referenceId
     */
    public function processReference($referenceId)
    {
        $ccCard = $this->braintreeAdapter->find($referenceId);
        if(is_null($ccCard)) {
            $this->logger->addWarning('Card with reference_id "' . $referenceId . '" not found in Braintree.');
            return false;
        }
        $customerId = $ccCard->customerId;
        /* @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken
            ->setCustomerId($customerId)
            ->setGatewayToken($referenceId)
            ->setPaymentMethodCode('braintree')
            ->setIsActive(true)
            ->setIsVisible(true)
            ->setExpiresAt($this->getExpirationDate($ccCard))
            ->setTokenDetails($this->convertDetailsToJSON([
                'type' => $this->getCreditCardType($ccCard->cardType),
                'maskedCC' => $ccCard->last4,
                'expirationDate' => $ccCard->expirationDate,
            ]));
        $paymentToken
            ->setPublicHash($this->generatePublicHash($paymentToken));
        try {
            $this->paymentTokenRepository->save($paymentToken);
            $this->logger->addInfo('Card with reference_id "' . $referenceId . '" saved.');
            return true;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if(preg_match('#Integrity constraint violation: 1452#', $msg)) {
                $msg = 'Unable to find customer ' . $customerId . ' for gateway_token ' . $referenceId;
            }
            $this->logger->addWarning($msg);
        }
        return false;
    }

    private function saveReference($ref)
    {
        /* @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken
            ->setCustomerId($ref['customer_id'])
            ->setGatewayToken($ref['gateway_token'])
            ->setPaymentMethodCode('braintree')
            ->setIsActive($ref['is_active'])
            ->setIsVisible($ref['is_visible'])
            ->setExpiresAt($ref['expires_at'])
            ->setTokenDetails($this->convertDetailsToJSON([
                'type' => $ref['type'],
                'maskedCC' => $ref['maskedCC'],
                'expirationDate' => $ref['expirationDate'],
            ]));
        $paymentToken
            ->setPublicHash($this->generatePublicHash($paymentToken));
        try {
            $this->paymentTokenRepository->save($paymentToken);
            $this->logger->addInfo('Card with reference_id "' . $ref['gateway_token'] . '" saved.');
            return true;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if(preg_match('#Integrity constraint violation: 1452#', $msg)) {
                $msg = 'Unable to find customer_id "' . $ref['customer_id'] . '"" for gateway_token "' . $ref['gateway_token'] . '"';
            }
            if(preg_match('#Integrity constraint violation: 1062#', $msg)) {
                $msg = 'Such credit card "' . $ref['gateway_token'] . '" is already saved';
            }
            $this->logger->addWarning($msg);
        }
        return false;
    }

    /**
     * Saves reference in vault
     * @param string $referenceId
     * @return array|bool
     */
    public function getReferenceData($referenceId)
    {
//        if(rand(0,1)) {
//            $this->logger->addWarning('Test warning');
//            return false;
//        } else {
//            return [
//                'test'=>$referenceId,
//            ];
//        }

        $ccCard = $this->braintreeAdapter->find($referenceId);
        if(is_null($ccCard)) {
            $this->logger->addWarning('Card with reference_id "' . $referenceId . '" not found.');
            return false;
        }
        $customerId = $ccCard->customerId;
        return [
            'customer_id' => $customerId,
            'gateway_token' => $referenceId,
            'payment_method_token' => 'braintree',
            'is_active' => true,
            'is_visible' => true,
            'expires_at' => $this->getExpirationDate($ccCard),
            'type' => $this->getCreditCardType($ccCard->cardType),
            'maskedCC' => $ccCard->last4,
            'expirationDate' => $ccCard->expirationDate,
        ];
    }

    /**
     * Generate vault payment public hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    private function generatePublicHash(PaymentTokenInterface $paymentToken)
    {
        $hashKey = $paymentToken->getGatewayToken();
//        if ($paymentToken->getCustomerId()) {
//            $hashKey = $paymentToken->getCustomerId();
//        }

        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();

        return $this->encryptor->getHash($hashKey);
    }

    /**
     * Get type of credit card mapped from Braintree
     *
     * @param string $type
     * @return array
     */
    private function getCreditCardType($type)
    {
        $replaced = str_replace(' ', '-', strtolower($type));
        $mapper = $this->config->getCctypesMapper();

        return $mapper[$replaced];
    }

    /**
     * @param CreditCard $ccCard
     * @return string
     */
    private function getExpirationDate(CreditCard $ccCard)
    {
        $expDate = new \DateTime(
            $ccCard->expirationYear
            . '-'
            . $ccCard->expirationMonth
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = Json::encode($details);
        return $json ? $json : '{}';
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

}