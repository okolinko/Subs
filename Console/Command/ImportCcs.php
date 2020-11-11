<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/29/16
 * Time: 12:55 PM
 */

namespace Toppik\Subscriptions\Console\Command;


use Braintree\CreditCard as BraintreeCreditCard;
use Braintree\CustomerSearch;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Braintree\Customer;
use Magento\Framework\Encryption\EncryptorInterface;
use Zend\Json\Json;
use Magento\Vault\Model\PaymentTokenRepository;
use Magento\Braintree\Gateway\Config\Config;

class ImportCcs extends Command
{

    /**
     * @var BraintreeAdapterFactory
     */
    private $braintreeAdapter;
    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @var PaymentTokenRepository
     */
    private $paymentTokenRepository;
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        BraintreeAdapterFactory $braintreeAdapter,
        \Magento\Vault\Api\Data\PaymentTokenFactoryInterface $paymentTokenFactory,
        EncryptorInterface $encryptor,
        PaymentTokenRepository $paymentTokenRepository,
        Config $config
    )
    {
        $this->braintreeAdapter = $braintreeAdapter->create();
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->encryptor = $encryptor;
        $this->config = $config;
        $this->paymentTokenRepository = $paymentTokenRepository;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('braintree:import:ccs');
        $this->setDescription('Import Ccs');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $step = 10000;
        $max = 100;
        $i = 0;
        $j = 0;
        while($i < $max) {
            $startId = $step * $i + 1;
            $endId = $step * ($i + 1) - 1;
            $ids = [];
            for($id = $startId; $id <= $endId; $id ++) {
                $ids[] = $id;
            }
            $customers = Customer::search([
                CustomerSearch::ids()->in($ids),
            ]);
            foreach($customers as $customer) {
                $j ++;
                /* @var \Braintree\Customer $customer */
                foreach($customer->creditCards as $cc) {
                    /* @var BraintreeCreditCard $cc */
                    $output->write("\n{$j}. {$customer->id}. {$customer->firstName} {$customer->lastName}: ");
                    $output->write($cc->cardType . ' - ' . $cc->token . ' - ' . $cc->maskedNumber . ' - ' . $cc->expirationDate . ' : ');
                    $output->write($this->saveReference([
                        'customer_id' => $customer->id,
                        'gateway_token' => $cc->token,
                        'is_active' => 1,
                        'is_visible' => 1,
                        'expires_at' => $this->getExpirationDate($cc),
                        'type' => $this->getCreditCardType($cc->cardType),
                        'maskedCC' => $cc->last4,
                        'expirationDate' => $cc->expirationDate,
                    ]));
                }
            }
        }
        $output->write("\n");
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
            return 'Card with reference_id "' . $ref['gateway_token'] . '" saved.';
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if(preg_match('#Integrity constraint violation: 1452#', $msg)) {
                $msg = 'Unable to find customer_id "' . $ref['customer_id'] . '"" for gateway_token "' . $ref['gateway_token'] . '"';
            }
            if(preg_match('#Integrity constraint violation: 1062#', $msg)) {
                $msg = 'Such credit card "' . $ref['gateway_token'] . '" is already saved';
            }
            return $msg;
        }
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
     * @param BraintreeCreditCard $ccCard
     * @return string
     */
    private function getExpirationDate(BraintreeCreditCard $ccCard)
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
     * Generate vault payment public hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    private function generatePublicHash(PaymentTokenInterface $paymentToken)
    {
        $hashKey = $paymentToken->getGatewayToken();

        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();

        return $this->encryptor->getHash($hashKey);
    }


}