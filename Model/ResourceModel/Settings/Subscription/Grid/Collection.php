<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Settings\Subscription\Grid;

use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult {
    
    protected $_fieldMap = [
        'subscription_id' => 'main_table.subscription_id'
    ];
    
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param AttributeRepositoryInterface $attributeRepository
     * @param StoreManagerInterface $storeManager
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        AttributeRepositoryInterface $attributeRepository,
        StoreManagerInterface $storeManager,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'subscriptions_subscriptions',
        $resourceModel = 'Toppik\Subscriptions\Model\ResourceModel\Settings\Subscription'
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    
    /**
     * @return $this
     */
    protected function _initSelect() {
        parent::_initSelect();
        $attributeId = $this->attributeRepository->get(Product::ENTITY, 'name')->getAttributeId();
        $storeId = 0;
        $this->getSelect()
            ->joinLeft(
                ['ce' => $this->getTable('catalog_product_entity')],
                'main_table.product_id = ce.entity_id',
                ['ce.sku AS product_sku']
            )
            ->joinLeft(
                ['catalog_product_entity_varchar' => $this->getTable('catalog_product_entity_varchar'), ],
                'ce.row_id = catalog_product_entity_varchar.row_id and catalog_product_entity_varchar.store_id=' . $storeId . ' and catalog_product_entity_varchar.attribute_id=' . $attributeId,
                ['catalog_product_entity_varchar.value as product_name']
            );
        $this->getSelect()
            ->joinLeft(
                ['subscriptions_items' => $this->getTable('subscriptions_items'), ],
                'main_table.subscription_id = subscriptions_items.subscription_id',
                []
            );
        $this->getSelect()
            ->joinLeft(
                ['subscriptions_periods' => $this->getTable('subscriptions_periods'), ],
                'subscriptions_items.period_id = subscriptions_periods.period_id',
                ['group_concat(subscriptions_periods.title separator ", ") as periods']
            );
        $this->getSelect()
            ->group('main_table.subscription_id');
        return $this;
    }
    
    public function addFieldToFilter($field, $condition = null) {
        if(isset($this->_fieldMap[$field])) {
            $field = $this->_fieldMap[$field];
        }

        return parent::addFieldToFilter($field, $condition);
    }
    
}
