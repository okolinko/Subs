<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/26/16
 * Time: 3:08 PM
 */

namespace Toppik\Subscriptions\Api\Data;


interface ProfileInterface
{

    const PROFILE_ID = 'profile_id';
    const CUSTOMER_ID = 'customer_id';
    const PAYMENT_TOKEN_ID = 'payment_token_id';
    const GRAND_TOTAL = 'grand_total';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const RESUME_AT = 'resume_at';
    const START_DATE = 'start_date';
    const LAST_ORDER_ID = 'last_order_id';
    const LAST_ORDER_AT = 'last_order_at';
    const NEXT_ORDER_AT = 'next_order_at';
    const STATUS = 'status';
    const LAST_SUSPEND_ERROR = 'last_suspend_error';
    const BILLING_ADDRESS_JSON = 'billing_address_json';
    const SHIPPING_ADDRESS_JSON = 'shipping_address_json';
    const ITEMS_JSON = 'items_json';
    const QUOTE_JSON = 'quote_json';
    const SUBSCRIPTION_UNIT_JSON = 'subscription_unit_json';
    const SUBSCRIPTION_PERIOD_JSON = 'subscription_period_json';
    const SUBSCRIPTION_ITEM_JSON = 'subscription_item_json';
    const SUBSCRIPTION_JSON = 'subscription_json';
    CONST CURRENCY_CODE = 'currency_code';
    const SKU = 'sku';
    const FREQUENCY_LENGTH = 'frequency_length';
    const FREQUENCY_TITLE = 'frequency_title';
    CONST ITEMS_COUNT = 'items_count';
    CONST ITEMS_QTY = 'items_qty';
    CONST IS_INFINITE = 'is_infinite';
    CONST ENGINE_CODE = 'engine_code';
    CONST NUMBER_OF_OCCURRENCES = 'number_of_occurrences';
    const SUSPEND_COUNTER = 'suspend_counter';
    const NEXT_ORDER_AT_TYPE = 'next_order_at_type';
    const NEXT_ORDER_AT_ORIGINAL = 'next_order_at_original';
    
    const FIRST_ORDER_COOKIES_JSON = 'first_order_cookies_json';

    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_SUSPENDED_TEMPORARILY = 'suspended_temporarily';
    
    const ACTION_UPDATE = 'update';
    const ACTION_ACTIVATE = 'activate';
    const ACTION_SUSPEND = 'suspend';
    const ACTION_CANCEL = 'cancel';
    const ACTION_FREQUENCY = 'frequency';
    const ACTION_QTY = 'quantity';
    const ACTION_CC = 'cc';
    const ACTION_NEXTDATE = 'nextdate';
    
    const TYPE_NEXT_DATE_AUTOMATIC = 1;
    const TYPE_NEXT_DATE_MANUAL = 2;
    
    /**
     * @return integer|null
     */
    public function getCustomerId();

    /**
     * @return integer|null
     */
    public function getPaymentTokenId();

    /**
     * @return float|null
     */
    public function getGrandTotal();

    /**
     * @return float|null
     */
    public function getBaseGrandTotal();

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @return string|null
     */
    public function getResumeAt();

    /**
     * @return string|null
     */
    public function getStartDate();

    /**
     * @return integer|null
     */
    public function getLastOrderId();

    /**
     * @return string|null
     */
    public function getLastOrderAt();

    /**
     * @return string|null
     */
    public function getNextOrderAt();

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @return string|null
     */
    public function getLastSuspendError();
    
    /**
     * @return string|null
     */
    public function getSku();

    /**
     * @return int|null
     */
    public function getFrequencyLength();

    /**
     * @return string|null
     */
    public function getFrequencyTitle();

    /**
     * @return string|null
     */
    public function getFirstOrderCookiesJson();

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @param int $paymentTokenId
     * @return $this
     */
    public function setPaymentTokenId($paymentTokenId);

    /**
     * @param float $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal);

    /**
     * @param float $baseGrandTotal
     * @return $this
     */
    public function setBaseGrandTotal($baseGrandTotal);

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @param string $resumeAt
     * @return $this
     */
    public function setResumeAt($resumeAt);

    /**
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate);

    /**
     * @param int $lastOrderId
     * @return $this
     */
    public function setLastOrderId($lastOrderId);

    /**
     * @param string $lastOrderAt
     * @return $this
     */
    public function setLastOrderAt($lastOrderAt);

    /**
     * @param string $nextOrderAt
     * @return $this
     */
    public function setNextOrderAt($nextOrderAt);

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @param string $lastSuspendError
     * @return $this
     */
    public function setLastSuspendError($lastSuspendError);
    
    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * @param int $frequency_length
     * @return $this
     */
    public function setFrequencyLength($frequency_length);

    /**
     * @param string $frequency_title
     * @return $this
     */
    public function setFrequencyTitle($frequency_title);

    /**
     * @param string $first_order_cookies_json
     * @return $this
     */
    public function setFirstOrderCookiesJson($first_order_cookies_json);

}