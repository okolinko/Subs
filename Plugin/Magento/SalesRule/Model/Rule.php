<?php
namespace Toppik\Subscriptions\Plugin\Magento\SalesRule\Model;

class Rule {
    
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;
    
    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;
    
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = '\Toppik\Subscriptions\Model\Rewrite\Magento\SalesRule\Model\Rule\Condition\Product\Combine';
    }
    
    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function aroundGetActionsInstance(\Magento\SalesRule\Model\Rule $original, callable $proceed) {
        return $this->create();
    }
    
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\SalesRule\Model\Rule
     */
    public function create(array $data = array()) {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
    
}
