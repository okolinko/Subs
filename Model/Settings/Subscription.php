<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/30/16
 * Time: 6:22 PM
 */

namespace Toppik\Subscriptions\Model\Settings;


use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Toppik\Subscriptions\Api\Data\Settings\SubscriptionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Toppik\Subscriptions\Model\ResourceModel\Settings\Item\Collection;
use Toppik\Subscriptions\Model\ResourceModel\Settings\Item\CollectionFactory;
use Toppik\Subscriptions\Model\Settings\Item;


class Subscription extends AbstractModel implements SubscriptionInterface, IdentityInterface {

    /**
     * START_DATE_CODE
     */
    const START_DATE_BY_CUSTOMER = 1;
    const START_DATE_BY_PURCHASE = 2;
    const START_DATE_BY_LAST_DATE_OF_CURRENT_MOHTH = 3;
    const START_DATE_BY_EXACT_DAY_OF_MONTH = 4;

    /**
     * IS_SUBSCRIPTION_ONLY
     */
    const ONLY_SUBSCRIPTION = 1;
    const NOT_ONLY_SUBSCRIPTION = 0;

    /**
     * @var Product
     */
    private $product;
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * Subscription constructor.
     * @param CollectionFactory $itemCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param Product $product
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @internal param GroupRepositoryInterface $groupRepositoryInterface
     */
    public function __construct(
        CollectionFactory $itemCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $groupRepository,
        Product $product,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->product = $product;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        $this->setProductId($this->getData(self::PRODUCT_ID));
        $this->setMoveCustomerToGroupId($this->getData(self::MOVE_CUSTOMER_TO_GROUP_ID));
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'subscriptions_settings_subscription';

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
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Settings\Subscription');
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
    public function getProductId()
    {
        $product_id = $this->getData(self::PRODUCT_ID);
        if(is_string($product_id) and preg_match('#^product/(\d+)#', $product_id, $m)) {
            $product_id = $m[1];
        }
        if(is_numeric($product_id)) {
            return (int) $product_id;
        } else {
            return $product_id;
        }
    }

    /**
     * @return integer
     */
    public function getIsSubscriptionOnly()
    {
        return $this->getData(self::IS_SUBSCRIPTION_ONLY);
    }

    /**
     * @return integer
     */
    public function getMoveCustomerToGroupId()
    {
        return $this->getData(self::MOVE_CUSTOMER_TO_GROUP_ID);
    }

    /**
     * @return integer
     */
    public function getStartDateCode()
    {
        return $this->getData(self::START_DATE_CODE);
    }

    /**
     * @return integer
     */
    public function getDayOfMonth()
    {
        return $this->getData(self::DAY_OF_MONTH);
    }

    /**
     * @param integer $product_id
     * @return SubscriptionInterface
     */
    public function setProductId($product_id)
    {
        if(is_string($product_id) and preg_match('#^product/(\d+)#', $product_id, $m)) {
            $product_id = $m[1];
        }
        return $this->setData(self::PRODUCT_ID, $product_id);
    }

    /**
     * @param integer $is_subscription_only
     * @return SubscriptionInterface
     */
    public function setIsSubscriptionOnly($is_subscription_only)
    {
        return $this->setData(self::IS_SUBSCRIPTION_ONLY, $is_subscription_only);
    }

    /**
     * @param integer $move_customer_to_group_id
     * @return SubscriptionInterface
     */
    public function setMoveCustomerToGroupId($move_customer_to_group_id)
    {
        return $this->setData(self::MOVE_CUSTOMER_TO_GROUP_ID, $move_customer_to_group_id);
    }

    /**
     * @param integer $start_date_code
     * @return SubscriptionInterface
     */
    public function setStartDateCode($start_date_code)
    {
        return $this->setData(self::START_DATE_CODE, $start_date_code);
    }

    /**
     * @param integer $day_of_month
     * @return SubscriptionInterface
     */
    public function setDayOfMonth($day_of_month)
    {
        return $this->setData(self::DAY_OF_MONTH, $day_of_month);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if(! $this->product->getId()) {
            $this->product->load($this->getProductId());
        }
        return $this->product;
    }

    public function getAvailableIsSubscriptionOnly() {
        return [
            self::ONLY_SUBSCRIPTION => 'Yes',
            self::NOT_ONLY_SUBSCRIPTION => 'No',
        ];
    }

    public function getAvailableGroupIds() {
        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $customerGroups = [];
        foreach($groups as $group) {
            $customerGroups[$group->getId()] = $group->getCode();
        }
        return $customerGroups;
    }

    public function getAvailableStartDateCodes() {
        return [
//            self::START_DATE_BY_CUSTOMER => __('Defined by Customer'),
            self::START_DATE_BY_PURCHASE => __('Moment of Purchase'),
//            self::START_DATE_BY_LAST_DATE_OF_CURRENT_MOHTH => __('Last Day of Current Month'),
//            self::START_DATE_BY_EXACT_DAY_OF_MONTH => __('Exact Day of Month'),
        ];
    }

    /**
     * @return Collection
     */
    public function getItemsCollection() {
        if(! $this->hasData('items_collection')) {
            /** @var Collection $itemsCollection */
            $itemsCollection = $this->itemCollectionFactory->create();
            $itemsCollection->addFieldToFilter('subscription_id', $this->getId());
            $itemsCollection->addOrder('sort_order', Collection::SORT_ORDER_ASC);
            $this->setData('items_collection', $itemsCollection);
        }
        return $this->getData('items_collection');
    }

    /**
     * @return $this
     */
    public function addPeriodFilterToItemsCollection($length) {
        $collection = $this->getItemsCollection();

        $collection->getSelect()->join(
            ['p' => 'subscriptions_periods'],
            'main_table.period_id = p.period_id'
        )->where(
            'p.length=?',
            $length
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function saveItems() {
        foreach($this->getItemsCollection() as $item) {
            /* @var Item $item */
            if(! $item->getSubscriptionId()) {
                $item->setSubscriptionId($this->getId());
            }
            $item->save();
        }
        return $this;
    }

    public function getSubscriptionByProductId($id){
        return $this->getCollection()->addFieldToFilter(self::PRODUCT_ID, (int)$id)->getFirstItem();

    }

    public function getAvailableStoreIds() {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
        $storeManagerDataList = $storeManager->getStores();
        $options = [];

        foreach ($storeManagerDataList as $key => $value) {
            $options[$key] = $value['name'];
        }

        return $options;
    }
}
