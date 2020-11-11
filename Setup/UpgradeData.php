<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/30/16
 * Time: 3:33 PM
 */

namespace Toppik\Subscriptions\Setup;


use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Toppik\Subscriptions\Model\Settings\Unit;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

	private $_eavSetupFactory;

    /**
     * UpgradeData constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
		EavSetupFactory $eavSetupFactory
    )
    {
        $this->objectManager = $objectManager;
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if(version_compare($context->getVersion(), '1.0.12') < 0) {
            $this->upgradeTo_1_0_12($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.27') < 0) {
            $this->upgradeTo_1_0_27($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.28') < 0) {
            $this->upgradeTo_1_0_28($setup, $context);
        }

		if(version_compare($context->getVersion(), '1.0.41') < 0) {
			$this->upgradeTo_1_0_41($setup, $context);
		}

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    private function upgradeTo_1_0_12(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $units = [
            ['title' => 'minute', 'length' => 60, ],
            ['title' => 'hour', 'length' => 60 * 60, ],
            ['title' => 'day', 'length' => 60 * 60 * 24, ],
            ['title' => 'week', 'length' => 60 * 60 * 24 * 7, ],
            ['title' => 'month', 'length' => 60 * 60 * 24 * 30, ],
            ['title' => 'year', 'length' => 60 * 60 * 24 * 365, ],
        ];
        foreach($units as $unit) {
            /** @var Unit $unitModel */
            $unitModel = $this->objectManager->create('Toppik\Subscriptions\Model\Settings\Unit');
            $unitModel->setData($unit)->save();
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    private function upgradeTo_1_0_27(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        /* @var \Toppik\Subscriptions\Model\ResourceModel\Profile\Collection $profiles */
        $profiles = $this->objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
        foreach($profiles as $profile) {
            /* @var \Toppik\Subscriptions\Model\Profile $profile */
            $profile->save();
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    private function upgradeTo_1_0_28(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        /* @var \Toppik\Subscriptions\Model\ResourceModel\Profile\Collection $profiles */
        $profiles = $this->objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
        foreach($profiles as $profile) {
            /* @var \Toppik\Subscriptions\Model\Profile $profile */
            $items = $profile->getItems();
            $profile->setGrandTotal($items->getRowTotal());
            $profile->setBaseGrandTotal($items->getBaseRowTotal());
            $profile->save();
        }
    }

	/**
	 * @param ModuleDataSetupInterface $setup
	 * @param ModuleContextInterface $context
	 */
	private function upgradeTo_1_0_41(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
		$eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'subscription_product_add_hash',
			[
				'group'                     =>  'Subscription Product Add Hash',
				'input'			            =>	'textarea',
				'type'                      =>  'text',
				'backend'                   =>  '',
				'frontend'                  =>  '',
				'label'                     =>  'Subscription Product Add Hash',
				'class'                     =>  '',
				'source'                    =>  '',
				'global'                    =>  \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
				'visible'                   =>  true,
				'required'                  =>  false,
				'user_defined'              =>  true,
				'default'                   =>  '',
				'searchable'                =>  false,
				'filterable'                =>  false,
				'comparable'                =>  false,
				'visible_on_front'          =>  false,
				'used_in_product_listing'   =>  true,
				'unique'                    =>  false,
			]
		);

	}
}
