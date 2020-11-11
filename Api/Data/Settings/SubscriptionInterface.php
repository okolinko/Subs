<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/30/16
 * Time: 5:43 PM
 */

namespace Toppik\Subscriptions\Api\Data\Settings;


use Magento\Catalog\Model\Product;

interface SubscriptionInterface
{

    const SUBSCRIPTION_ID = 'subscription_id';
    const PRODUCT_ID = 'product_id';
    const IS_SUBSCRIPTION_ONLY = 'is_subscription_only';
    const MOVE_CUSTOMER_TO_GROUP_ID = 'move_customer_to_group_id';
    const START_DATE_CODE = 'start_date_code';
    const DAY_OF_MONTH = 'day_of_month';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return integer
     */
    public function getProductId();

    /**
     * @return integer
     */
    public function getIsSubscriptionOnly();

    /**
     * @return integer
     */
    public function getMoveCustomerToGroupId();

    /**
     * @return integer
     */
    public function getStartDateCode();

    /**
     * @return integer
     */
    public function getDayOfMonth();

    /**
     * @param integer $id
     * @return SubscriptionInterface
     */
    public function setId($id);

    /**
     * @param integer $product_id
     * @return SubscriptionInterface
     */
    public function setProductId($product_id);

    /**
     * @param integer $is_subscription_only
     * @return SubscriptionInterface
     */
    public function setIsSubscriptionOnly($is_subscription_only);

    /**
     * @param integer $move_customer_to_group_id
     * @return SubscriptionInterface
     */
    public function setMoveCustomerToGroupId($move_customer_to_group_id);

    /**
     * @param integer $start_date_code
     * @return SubscriptionInterface
     */
    public function setStartDateCode($start_date_code);

    /**
     * @param integer $day_of_month
     * @return SubscriptionInterface
     */
    public function setDayOfMonth($day_of_month);

    /**
     * @return Product
     */
    public function getProduct();

}