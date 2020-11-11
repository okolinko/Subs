<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled;

/**
 * Class CollectionFactory
 */
class CollectionFactory implements CollectionFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled\Collection::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        /** @var \Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled\Collection $collection */
        $collection = $this->objectManager->create($this->instanceName);

        return $collection;
    }
}
