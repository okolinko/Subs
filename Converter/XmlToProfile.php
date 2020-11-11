<?php
/**
 * Created by PhpStorm.
 * User: andreff
 * Date: 07.02.17
 * Time: 22:07
 */

namespace Toppik\Subscriptions\Converter;

use \Magento\Quote\Model\QuoteManagement;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Xml\Parser;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Vault\Model\PaymentTokenManagement;
use Toppik\Subscriptions\Helper\Quote as QuoteHelper;
use Toppik\Subscriptions\Model\Profile;
use Toppik\Subscriptions\Model\ProfileFactory;
use Toppik\Subscriptions\Model\Settings\ItemFactory;
use Toppik\Subscriptions\Model\Settings;
use Zend\Json\Json;
use Toppik\Subscriptions\Model\ResourceModel\Profile as ProfileResourceModel;


class XmlToProfile
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $_moduleDirReader;

    /**
     * @var \Magento\Framework\Xml\Parser
     */
    private $parser;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var ProfileFactory
     */
    private $profileFactory;
    /**
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;
    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var Settings\SubscriptionFactory
     */
    private $subscriptionFactory;
    /**
     * @var Settings\PeriodFactory
     */
    private $periodFactory;
    /**
     * @var Settings\UnitFactory
     */
    private $unitFactory;
    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;
    /**
     * @var ProfileResourceModel
     */
    private $profileResourceModel;

    private $directoryList;


    public function __construct(
        ProfileResourceModel $profileResourceModel,
        PaymentTokenManagement $paymentTokenManagement,
        Settings\UnitFactory $unitFactory,
        Settings\PeriodFactory $periodFactory,
        Settings\SubscriptionFactory $subscriptionFactory,
        ItemFactory $itemFactory,
        QuoteHelper $quoteHelper,
        ManagerInterface $eventManager,
        ProfileFactory $profileFactory,
        DateTime $dateTime,
        DirectoryList $directoryList,
        Parser $parser
    )
    {
        $this->eventManager = $eventManager;
        $this->profileFactory = $profileFactory;
        $this->dateTime = $dateTime;
        $this->quoteHelper = $quoteHelper;
        $this->itemFactory = $itemFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->periodFactory = $periodFactory;
        $this->unitFactory = $unitFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->profileResourceModel = $profileResourceModel;
        $this->directoryList = $directoryList;
        $this->parser = $parser;
    }


    protected function getDataFromXmlFile()
    {
        $data = $this->parser->load($this->getFilePath())->xmlToArray();

        return $data;
    }

    protected function createQuote()
    {

    }

    public function getFilePath()
    {
        return $this->directoryList->getPath('var') . '/import/order.xml';
    }


    public function process()
    {
        $this->getDataFromXmlFile();

        //$this->_quoteToProfile->process($quote, $result, $orderData);
    }

}