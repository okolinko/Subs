<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 6:01 PM
 */

namespace Toppik\Subscriptions\Model\Settings;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Toppik\Subscriptions\Api\Data\Settings\PeriodInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Toppik\Subscriptions\Model\ResourceModel\Settings\Unit\Collection;

class Period extends AbstractModel implements PeriodInterface, IdentityInterface
{

    /**
     * @var Collection
     */
    private $unitCollection;
    /**
     * @var UnitFactory
     */
    private $unitFactory;

    /**
     * Period constructor.
     * @param UnitFactory $unitFactory
     * @param Context $context
     * @param Collection $unitCollection
     * @param Registry $registry
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @internal param CollectionFactory $unitCollectionFactory
     */
    public function __construct(
        UnitFactory $unitFactory,
        Context $context,
        Collection $unitCollection,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->unitCollection = $unitCollection;
        $this->unitFactory = $unitFactory;
    }

    /**
     * IS_VISIBLE
     */
    const VISIBLE = 1;
    const HIDDEN = 0;

    /**
     * IS_INFINITE
     */
    const FINITE = 0;
    const INFINITE = 1;

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'subscriptions_settings_period';

    /**
     * @var string
     */
    protected $_cacheTag = 'subscriptions_settings_period';

    /**
     * @var string
     */
    protected $_eventPrefix = 'subscriptions_settings_period';

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Settings\Period');
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
     * @return string
     */
    public function getEngineCode()
    {
        return $this->getData(self::ENGINE_CODE);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @return boolean
     */
    public function getIsVisible()
    {
        return (bool) $this->getData(self::IS_VISIBLE);
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        if(! $this->hasData('_' . self::STORE_IDS)) {
            $this->setData(
                '_' . self::STORE_IDS,
                is_array($this->getData(self::STORE_IDS)) ? $this->getData(self::STORE_IDS) : explode(',', $this->getData(self::STORE_IDS))
            );
        }
        return $this->getData('_' . self::STORE_IDS);
    }

    /**
     * @return integer
     */
    public function getLength()
    {
        return $this->getData(self::LENGTH);
    }

    /**
     * @return boolean
     */
    public function getIsInfinite()
    {
        return (bool) $this->getData(self::IS_INFINITE);
    }

    /**
     * @return integer
     */
    public function getNumberOfOccurrences()
    {
        return $this->getData(self::NUMBER_OF_OCCURRENCES);
    }

    /**
     * @param string $engine_code
     * @return PeriodInterface
     */
    public function setEngineCode($engine_code)
    {
        return $this->setData(self::ENGINE_CODE, $engine_code);
    }

    /**
     * @param string $title
     * @return PeriodInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @param boolean $is_visible
     * @return PeriodInterface
     */
    public function setIsVisible($is_visible)
    {
        return $this->setData(self::IS_VISIBLE, (int) $is_visible);
    }

    /**
     * @param string|array $store_ids
     * @return PeriodInterface
     */
    public function setStoreIds($store_ids)
    {
        $this->unsetData('_' . self::STORE_IDS);
        if(is_array($store_ids)) {
            $store_ids = implode(',', $store_ids);
        }
        return $this->setData(self::STORE_IDS, $store_ids);
    }

    /**
     * @param integer $length
     * @return PeriodInterface
     */
    public function setLength($length)
    {
        return $this->setData(self::LENGTH, $length);
    }

    /**
     * @param boolean $is_finite
     * @return PeriodInterface
     */
    public function setIsInfinite($is_finite)
    {
        return $this->setData(self::IS_INFINITE, (int) $is_finite);
    }

    /**
     * @param integer $number_of_occurrences
     * @return PeriodInterface
     */
    public function setNumberOfOccurrences($number_of_occurrences)
    {
        return $this->setData(self::NUMBER_OF_OCCURRENCES, $number_of_occurrences);
    }

    public function getAvailableEngineCodes() {
        return [
            'braintree' => __('Braintree'),
        ];
    }

    public function getAvailableVisibilityOptions() {
        return [
            self::VISIBLE => __('Visible'),
            self::HIDDEN => __('Hidden'),
        ];
    }

    public function getAvailableRepeatOptions() {
        return [
            self::INFINITE => __('Yes'),
            self::FINITE => __('No'),
        ];
    }

    public function getAvailablePeriodUnits() {
        return $this->unitCollection->getOptionArray();
    }

    /**
     * @return integer
     */
    public function getUnitId()
    {
        return $this->getData(self::UNIT_ID);
    }

    /**
     * @param integer $unit_id
     * @return PeriodInterface
     */
    public function setUnitId($unit_id)
    {
        return $this->setData(self::UNIT_ID, $unit_id);
    }

    /**
     * @return Unit
     */
    public function getUnit() {
        if(! $this->hasData('unit')) {
            /* @var Unit $unit */
            $unit = $this->unitFactory->create();
            $unit->load($this->getUnitId());
            $this->setData('unit', $unit);
        }
        return $this->getData('unit');
    }
}