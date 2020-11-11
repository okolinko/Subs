<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/1/16
 * Time: 12:45 PM
 */

namespace Toppik\Subscriptions\Model\Settings;


use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Toppik\Subscriptions\Api\Data\Settings\ItemInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Toppik\Subscriptions\Api\Data\Settings\PeriodInterface;
use Toppik\Subscriptions\Model\ResourceModel\Settings\Period\Collection as PeriodCollection;
use Magento\Framework\ObjectManagerInterface;

class Item extends AbstractModel implements ItemInterface, IdentityInterface {
    
    const USE_COUPON_CODE_YES = 1;
    const USE_COUPON_CODE_NO = 0;

    /**
     * @var CollectionFactory
     */
    private $periodCollection;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        PeriodCollection $periodCollection,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->periodCollection = $periodCollection;
        $this->objectManager = $objectManager;
    }

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'subscriptions_settings_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'subscriptions_settings_item';

    /**
     * @var string
     */
    protected $_eventPrefix = 'subscriptions_settings_item';

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Settings\Item');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return integer
     */
    public function getSubscriptionId()
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * @return integer
     */
    public function getPeriodId()
    {
        return $this->getData(self::PERIOD_ID);
    }

    /**
     * @return double
     */
    public function getRegularPrice()
    {
        return $this->getData(self::REGULAR_PRICE);
    }

    /**
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @return boolean
     */
    public function getUseCouponCode()
    {
        return $this->getData(self::USE_COUPON_CODE);
    }

    /**
     * @param integer $subscription_id
     * @return ItemInterface
     */
    public function setSubscriptionId($subscription_id)
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscription_id);
    }

    /**
     * @param integer $period_id
     * @return ItemInterface
     */
    public function setPeriodId($period_id)
    {
        $this->unsetData('period');
        return $this->setData(self::PERIOD_ID, $period_id);
    }

    /**
     * @param double $regular_price
     * @return ItemInterface
     */
    public function setRegularPrice($regular_price)
    {
        return $this->setData(self::REGULAR_PRICE, $regular_price);
    }

    /**
     * @param integer $sort_order
     * @return ItemInterface
     */
    public function setSortOrder($sort_order)
    {
        return $this->setData(self::SORT_ORDER, $sort_order);
    }

    /**
     * @param boolean $coupon_code
     * @return ItemInterface
     */
    public function setUseCouponCode($coupon_code)
    {
        return $this->setData(self::USE_COUPON_CODE, $coupon_code);
    }

    public function getAvailablePeriods() {
        return $this->periodCollection->getOptionArray();
    }

    public function getPeriodByLength($length) {
        return $this->periodCollection->getPeriodByLength((int)$length);
    }

    public function getAvailableUseCouponCode() {
        return [
            self::USE_COUPON_CODE_YES => 'Yes',
            self::USE_COUPON_CODE_NO => 'No',
        ];
    }
    
    /**
     * @return PeriodInterface
     */
    public function getPeriod() {
        if(!$this->hasData('period')) {
            $period = $this->objectManager->create('Toppik\Subscriptions\Model\Settings\Period');
            $period->load($this->getPeriodId());
            $this->setData('period', $period);
        }
        
        return $this->getData('period');
    }
    
    /**
     * @return UnitInterface
     */
    public function getUnit() {
        if(!$this->hasData('unit')) {
            $unit = $this->objectManager->create('Toppik\Subscriptions\Model\Settings\Unit');
            $unit->load($this->getPeriod()->getUnitId());
            $this->setData('unit', $unit);
        }
        
        return $this->getData('unit');
    }
    
}
