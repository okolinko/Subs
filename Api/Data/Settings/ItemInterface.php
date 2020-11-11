<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/1/16
 * Time: 12:37 PM
 */

namespace Toppik\Subscriptions\Api\Data\Settings;


interface ItemInterface
{

    const ITEM_ID = 'item_id';
    const SUBSCRIPTION_ID = 'subscription_id';
    const PERIOD_ID = 'period_id';
    const REGULAR_PRICE = 'regular_price';
    const SORT_ORDER = 'sort_order';
    const USE_COUPON_CODE = 'use_coupon_code';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return integer
     */
    public function getSubscriptionId();

    /**
     * @return integer
     */
    public function getPeriodId();

    /**
     * @return double
     */

    public function getRegularPrice();

    /**
     * @return integer
     */
    public function getSortOrder();

    /**
     * @return boolean
     */
    public function getUseCouponCode();

    /**
     * @param integer $id
     * @return ItemInterface
     */
    public function setId($id);

    /**
     * @param integer $subscription_id
     * @return ItemInterface
     */
    public function setSubscriptionId($subscription_id);

    /**
     * @param integer $period_id
     * @return ItemInterface
     */
    public function setPeriodId($period_id);

    /**
     * @param double $regular_price
     * @return ItemInterface
     */
    public function setRegularPrice($regular_price);

    /**
     * @param integer $sort_order
     * @return ItemInterface
     */
    public function setSortOrder($sort_order);

    /**
     * @param boolean $coupon_code
     * @return ItemInterface
     */
    public function setUseCouponCode($coupon_code);

    /**
     * @return PeriodInterface
     */
    public function getPeriod();

}