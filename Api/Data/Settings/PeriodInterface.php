<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 5:45 PM
 */

namespace Toppik\Subscriptions\Api\Data\Settings;


interface PeriodInterface
{

    const PERIOD_ID = 'period_id';
    const ENGINE_CODE = 'engine_code';
    const TITLE = 'title';
    const IS_VISIBLE = 'is_visible';
    const STORE_IDS = 'store_ids';
    const LENGTH = 'length';
    const UNIT_ID = 'unit_id';
    const IS_INFINITE = 'is_infinite';
    const NUMBER_OF_OCCURRENCES = 'number_of_occurrences';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getEngineCode();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return boolean
     */
    public function getIsVisible();

    /**
     * @return array
     */
    public function getStoreIds();

    /**
     * @return integer
     */
    public function getLength();

    /**
     * @return integer
     */
    public function getUnitId();

    /**
     * @return boolean
     */
    public function getIsInfinite();

    /**
     * @return integer
     */
    public function getNumberOfOccurrences();

    /**
     * @param integer $id
     * @return PeriodInterface
     */
    public function setId($id);

    /**
     * @param string $engine_code
     * @return PeriodInterface
     */
    public function setEngineCode($engine_code);

    /**
     * @param string $title
     * @return PeriodInterface
     */
    public function setTitle($title);

    /**
     * @param boolean $is_visible
     * @return PeriodInterface
     */
    public function setIsVisible($is_visible);

    /**
     * @param string|array $store_ids
     * @return PeriodInterface
     */
    public function setStoreIds($store_ids);

    /**
     * @param integer $length
     * @return PeriodInterface
     */
    public function setLength($length);

    /**
     * @param integer $unit_id
     * @return PeriodInterface
     */
    public function setUnitId($unit_id);

    /**
     * @param boolean $is_finite
     * @return PeriodInterface
     */
    public function setIsInfinite($is_finite);

    /**
     * @param integer $number_of_occurrences
     * @return PeriodInterface
     */
    public function setNumberOfOccurrences($number_of_occurrences);

    /**
     * @return UnitInterface
     */
    public function getUnit();

}