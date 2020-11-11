<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 6:52 PM
 */

namespace Toppik\Subscriptions\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if(version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->upgradeTo_1_0_1($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->upgradeTo_1_0_2($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->upgradeTo_1_0_4($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.5') < 0) {
            $this->upgradeTo_1_0_5($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.6') < 0) {
            $this->upgradeTo_1_0_6($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.7') < 0) {
            $this->upgradeTo_1_0_7($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.9') < 0) {
            $this->upgradeTo_1_0_9($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.15') < 0) {
            $this->upgradeTo_1_0_15($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.17') < 0) {
            $this->upgradeTo_1_0_17($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.18') < 0) {
            $this->upgradeTo_1_0_18($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.19') < 0) {
            $this->upgradeTo_1_0_19($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.20') < 0) {
            $this->upgradeTo_1_0_20($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.21') < 0) {
            $this->upgradeTo_1_0_21($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.22') < 0) {
            $this->upgradeTo_1_0_22($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.23') < 0) {
            $this->upgradeTo_1_0_23($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.24') < 0) {
            $this->upgradeTo_1_0_24($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.25') < 0) {
            $this->upgradeTo_1_0_25($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.26') < 0) {
            $this->upgradeTo_1_0_26($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.27') < 0) {
            $this->upgradeTo_1_0_27($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.29') < 0) {
            $this->upgradeTo_1_0_29($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.30') < 0) {
            $this->upgradeTo_1_0_30($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.31') < 0) {
            $this->upgradeTo_1_0_31($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.32') < 0) {
            $this->upgradeTo_1_0_32($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.33') < 0) {
            $this->upgradeTo_1_0_33($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.34') < 0) {
            $this->upgradeTo_1_0_34($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.35') < 0) {
            $this->upgradeTo_1_0_35($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.36') < 0) {
            $this->upgradeTo_1_0_36($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.37') < 0) {
            $this->upgradeTo_1_0_37($setup, $context);
        }

        if(version_compare($context->getVersion(), '1.0.39') < 0) {
            $this->upgradeTo_1_0_39($setup, $context);
        }
        
        if(version_compare($context->getVersion(), '1.0.40') < 0) {
            $this->upgradeTo_1_0_40($setup, $context);
        }

		if(version_compare($context->getVersion(), '1.0.42') < 0) {
			$this->upgradeTo_1_0_42($setup, $context);
		}

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    private function upgradeTo_1_0_1(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $periodsTable = $setup->getConnection()
            ->newTable($setup->getTable('subscriptions_periods'))
            ->addColumn(
                'period_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Period ID'
            )
            ->addColumn(
                'engine_code',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Engine Code'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Title'
            )
            ->addColumn(
                'is_visible',
                Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 1],
                'Is Visible'
            )
            ->addColumn(
                'store_ids',
                Table::TYPE_TEXT,
                255,
                [],
                'Store Ids'
            )
            ->addColumn(
                'length',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Length'
            )
            ->addColumn(
                'unit',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Unit'
            )
            ->addColumn(
                'is_infinite',
                Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 1],
                'Is Infinite'
            )
            ->addColumn(
                'number_of_occurrences',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Number Of Occurrences'
            )
            ->setComment('Subscriptions Periods');

        $setup->getConnection()->createTable($periodsTable);
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_2(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()->changeColumn(
            'subscriptions_periods',
            'is_visible',
            'is_visible',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Is Visible',
                'nullable' => false,
                'length' => 2,
                'unsigned' => true,
                'default' => 1
            ]
        );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_4(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()->changeColumn(
            'subscriptions_periods',
            'is_infinite',
            'is_infinite',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Is Infinite',
                'nullable' => false,
                'length' => 2,
                'unsigned' => true,
                'default' => 1
            ]
        );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_5(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()->addIndex(
            'subscriptions_periods',
            $setup->getIdxName('subscriptions_periods', 'title', AdapterInterface::INDEX_TYPE_INDEX),
            [
                'title',
                'engine_code',
            ],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_6(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $periodsTable = $setup->getConnection()
            ->newTable($setup->getTable('subscriptions_units'))
            ->addColumn(
                'unit_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Unit ID'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Title'
            )
            ->addColumn(
                'length',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Length'
            )
            ->setComment('Subscriptions Units');

        $setup->getConnection()->createTable($periodsTable);
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_7(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()
            ->dropColumn('subscriptions_periods', 'unit');

        $setup->getConnection()
            ->addColumn(
                'subscriptions_periods',
                'unit_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'comment' => 'Unit Id',
                    'nullable' => false,
                    'length' => 6,
                    'unsigned' => true
                ]
            );

        $setup->getConnection()
            ->addIndex(
                'subscriptions_periods',
                $setup->getIdxName('subscriptions_periods', 'unit_id', AdapterInterface::INDEX_TYPE_INDEX),
                'unit_id',
                AdapterInterface::INDEX_TYPE_INDEX
            );
        
        /*
        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    'subscriptions_periods',
                    'unit_id',
                    'subscriptions_units',
                    'unit_id'
                ),
                'subscriptions_periods',
                'unit_id',
                'subscriptions_units',
                'unit_id',
                AdapterInterface::FK_ACTION_CASCADE
            );
        */
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_9(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()->addIndex(
            'subscriptions_units',
            $setup->getIdxName('subscriptions_units', 'title', AdapterInterface::INDEX_TYPE_INDEX),
            [
                'title',
            ],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_15(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $subscriptionTable = $setup->getConnection()
            ->newTable($setup->getTable('subscriptions_subscriptions'))
            ->addColumn(
                'subscription_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'length' => 10, 'unsigned' => true, ],
                'Subscription ID'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true, ],
                'Product ID'
            )
            ->addColumn(
                'is_subscription_only',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 1, 'length' => 1, 'unsigned' => true, ],
                'Is Subscription Only'
            )
            ->addColumn(
                'move_customer_to_group_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => true, 'default' => 0, 'length' => 5, 'unsigned' => true, ],
                'Move Customer To Group ID'
            )
            ->addColumn(
                'start_date_code',
                Table::TYPE_SMALLINT,
                null,
                ['length' => 1, 'unsigned' => true, ],
                'Start Date Code'
            )
            ->addColumn(
                'day_of_month',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0, 'length' => 2, 'unsigned' => true, ],
                'Day of Month'
            )
            ->setComment('Subscriptions');

        $setup->getConnection()->createTable($subscriptionTable);

        $setup->getConnection()
            ->addIndex(
                'subscriptions_subscriptions',
                $setup->getIdxName('subscriptions_subscriptions', 'product_id', AdapterInterface::INDEX_TYPE_INDEX),
                'product_id',
                AdapterInterface::INDEX_TYPE_INDEX
            );

        $setup->getConnection()
            ->addIndex(
                'subscriptions_subscriptions',
                $setup->getIdxName('subscriptions_subscriptions', 'move_customer_to_group_id', AdapterInterface::INDEX_TYPE_INDEX),
                'move_customer_to_group_id',
                AdapterInterface::INDEX_TYPE_INDEX
            );

        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    'subscriptions_subscriptions',
                    'product_id',
                    'catalog_product_entity',
                    'row_id'
                ),
                'subscriptions_subscriptions',
                'product_id',
                'catalog_product_entity',
                'row_id',
                AdapterInterface::FK_ACTION_CASCADE
            );
        
        /*
        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    'subscriptions_subscriptions',
                    'move_customer_to_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'subscriptions_subscriptions',
                'move_customer_to_group_id',
                'customer_group',
                'customer_group_id',
                AdapterInterface::FK_ACTION_CASCADE
            );
        */
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_17(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()->changeColumn(
            'subscriptions_subscriptions',
            'move_customer_to_group_id',
            'move_customer_to_group_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Move Customer To Group ID',
                'nullable' => false,
                'length' => 5,
                'unsigned' => true
            ]
        );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_18(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        /*
        $setup->getConnection()
            ->dropForeignKey(
                'subscriptions_subscriptions',
                $setup->getFkName(
                    'subscriptions_subscriptions',
                    'move_customer_to_group_id',
                    'customer_group',
                    'customer_group_id'
                )
            );

        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    'subscriptions_subscriptions',
                    'move_customer_to_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'subscriptions_subscriptions',
                'move_customer_to_group_id',
                'customer_group',
                'customer_group_id',
                AdapterInterface::FK_ACTION_SET_NULL
            );
        */
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_19(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()
            ->addIndex(
                'subscriptions_subscriptions',
                $setup->getIdxName('subscriptions_subscriptions', 'product_id', AdapterInterface::INDEX_TYPE_UNIQUE),
                'product_id',
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_20(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $itemTable = $setup->getConnection()
            ->newTable($setup->getTable('subscriptions_items'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'length' => 10, 'unsigned' => true, ],
                'Item ID'
            )
            ->addColumn(
                'subscription_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true, ],
                'Subscription ID'
            )
            ->addColumn(
                'period_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'length' => 6, 'unsigned' => false, ],
                'Period ID'
            )
            ->addColumn(
                'regular_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000', ],
                'Regular Price'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                ['length' => 10, 'unsigned' => true, ],
                'Sort Order'
            )
            ->addColumn(
                'use_coupon_code',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 1, 'length' => 1, 'unsigned' => true, ],
                'Use Coupon Code'
            )
            ->setComment('Subscription Items');

        $setup->getConnection()->createTable($itemTable);

        $setup->getConnection()
            ->addIndex(
                'subscriptions_items',
                $setup->getIdxName('subscriptions_items', 'subscription_id', AdapterInterface::INDEX_TYPE_INDEX),
                'subscription_id',
                AdapterInterface::INDEX_TYPE_INDEX
            );

        $setup->getConnection()
            ->addIndex(
                'subscriptions_items',
                $setup->getIdxName('subscriptions_items', 'period_id', AdapterInterface::INDEX_TYPE_INDEX),
                'period_id',
                AdapterInterface::INDEX_TYPE_INDEX
            );

        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    'subscriptions_items',
                    'subscription_id',
                    'subscriptions_subscriptions',
                    'subscription_id'
                ),
                'subscriptions_items',
                'subscription_id',
                'subscriptions_subscriptions',
                'subscription_id',
                AdapterInterface::FK_ACTION_CASCADE
            );

        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    'subscriptions_items',
                    'period_id',
                    'subscriptions_periods',
                    'period_id'
                ),
                'subscriptions_items',
                'period_id',
                'subscriptions_periods',
                'period_id',
                AdapterInterface::FK_ACTION_CASCADE
            );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_21(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()
            ->addIndex(
                'subscriptions_items',
                $setup->getIdxName('subscriptions_items', ['subscription_id', 'period_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                ['subscription_id', 'period_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_22(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()
            ->addColumn(
                'quote_item',
                'linked_item_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Linked Item Id',
                    'nullable' => true,
                    'length' => 10,
                    'unsigned' => true,
                ]
            );
        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    'quote_item',
                    'linked_item_id',
                    'quote_item',
                    'item_id'
                ),
                'quote_item',
                'linked_item_id',
                'quote_item',
                'item_id',
                AdapterInterface::FK_ACTION_CASCADE
            );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_23(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $profileTable = $setup->getConnection()
            ->newTable($setup->getTable('subscriptions_profiles'))
            ->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'length' => 10, 'unsigned' => true, ],
                'Profile ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true, ],
                'Customer ID'
            )
            ->addColumn(
                'payment_token_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true, ],
                'Payment Token ID'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000', ],
                'Grand Total'
            )
            ->addColumn(
                'base_grand_total',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000', ],
                'Base Grand Total'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT, ],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE, ],
                'Updated At'
            )
            ->addColumn(
                'resume_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, ],
                'Resume At'
            )
            ->addColumn(
                'start_date',
                Table::TYPE_DATE,
                null,
                ['nullable' => false, ],
                'Start Date'
            )
            ->addColumn(
                'last_order_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'length' => 10, 'unsigned' => true, ],
                'Last Order ID'
            )
            ->addColumn(
                'last_order_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, ],
                'Last Order At'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'next_order_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, ],
                'Next Order At'
            )
            ->addColumn(
                'last_suspend_error',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, ],
                'Last Suspend Error'
            )
            ->addColumn(
                'billing_address_json',
                Table::TYPE_TEXT,
                65536,
                ['nullable' => true, ],
                'Billing Address JSON'
            )
            ->addColumn(
                'shipping_address_json',
                Table::TYPE_TEXT,
                65536,
                ['nullable' => true, ],
                'Shipping Address JSON'
            )
            ->addColumn(
                'items_json',
                Table::TYPE_TEXT,
                65536,
                ['nullable' => true, ],
                'Items JSON'
            )
            ->addColumn(
                'quote_json',
                Table::TYPE_TEXT,
                65536,
                ['nullable' => true, ],
                'Quote JSON'
            )
            ->addColumn(
                'customer_json',
                Table::TYPE_TEXT,
                65536,
                ['nullable' => true, ],
                'Customer JSON'
            )
            ->addColumn(
                'subscription_unit_json',
                Table::TYPE_TEXT,
                65536,
                ['nullable' => true, ],
                'Subscription Unit JSON'
            )
            ->addColumn(
                'subscription_period_json',
                Table::TYPE_TEXT,
                65536,
                ['nullable' => true, ],
                'Subscription Period JSON'
            )
            ->addColumn(
                'subscription_item_json',
                Table::TYPE_TEXT,
                65536,
                ['nullable' => true, ],
                'Subscription Item JSON'
            )
            ->addColumn(
                'subscription_json',
                Table::TYPE_TEXT,
                65536,
                ['nullable' => true, ],
                'Subscription JSON'
            )
            ->setComment('Subscription Profiles');

        $setup->getConnection()->createTable($profileTable);
    }

    private function upgradeTo_1_0_24(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()
            ->dropColumn('subscriptions_profiles', 'customer_json');
    }

    private function upgradeTo_1_0_25(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $profileOrderTable = $setup->getConnection()
            ->newTable($setup->getTable('subscriptions_profiles_orders'))
            ->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true, ],
                'Profile ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true, ],
                'Order ID'
            )
            ->setComment('Subscription Profiles Orders Relation');

        $setup->getConnection()->createTable($profileOrderTable);
    }

    private function upgradeTo_1_0_26(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()
            ->addIndex(
                'subscriptions_profiles_orders',
                $setup->getIdxName('subscriptions_profiles_orders', 'profile_id', AdapterInterface::INDEX_TYPE_INDEX),
                'profile_id',
                AdapterInterface::INDEX_TYPE_INDEX
            );
        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    'subscriptions_profiles_orders',
                    'profile_id',
                    'subscriptions_profiles',
                    'profile_id'
                ),
                'subscriptions_profiles_orders',
                'profile_id',
                'subscriptions_profiles',
                'profile_id',
                AdapterInterface::FK_ACTION_CASCADE
            );

        $setup->getConnection()
            ->addIndex(
                'subscriptions_profiles_orders',
                $setup->getIdxName('subscriptions_profiles_orders', 'order_id', AdapterInterface::INDEX_TYPE_INDEX),
                'order_id',
                AdapterInterface::INDEX_TYPE_INDEX
            );
        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    'subscriptions_profiles_orders',
                    'order_id',
                    'sales_order',
                    'entity_id'
                ),
                'subscriptions_profiles_orders',
                'order_id',
                'sales_order',
                'entity_id',
                AdapterInterface::FK_ACTION_CASCADE
            );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_27(SchemaSetupInterface $setup, ModuleContextInterface $context) {

        $setup->getConnection()
            ->addColumn(
                'subscriptions_profiles',
                'sku',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Sku',
                    'nullable' => false,
                    'length' => 255,
                ]
            );

        $setup->getConnection()
            ->addColumn(
                'subscriptions_profiles',
                'frequency_length',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Frequency Length',
                    'nullable' => false,
                    'length' => 10,
                ]
            );

        $setup->getConnection()
            ->addColumn(
                'subscriptions_profiles',
                'frequency_title',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Frequency Title',
                    'nullable' => false,
                    'length' => 255,
                ]
            );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_29(SchemaSetupInterface $setup, ModuleContextInterface $context) {

        $setup->getConnection()
            ->addColumn(
                'subscriptions_profiles',
                'first_order_cookies_json',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Cookie',
                    'nullable' => true
                ]
            );
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_30(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('subscriptions_sku_relations'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'sku',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Sku'
            )
            ->addColumn(
                'subscription_sku',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Subscription Sku'
            )
            ->addColumn(
                'subscription_length',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Subscription Length'
            );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_31(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'merchant_source',
                [
                    'type'              => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment'           => 'Merchant Source',
                ]);
    }

    /**
     * @param $setup
     * @param $context
     */
    private function upgradeTo_1_0_32(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'suspended_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'comment' => 'Suspended At',
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'cancelled_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'comment' => 'Cancelled At',
                ]
            );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    private function upgradeTo_1_0_33(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();

		$table = $setup->getConnection()
			->newTable($setup->getTable('subscription_report_daily'))
			->addColumn(
				'id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => false, 'auto_increment' => true, 'primary' => true]
			)
			->addColumn(
				'date',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
				null,
				['nullable' => false]
			)
			->addColumn(
				'active',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => false, 'default' => 0]
			)
			->addColumn(
				'active_ms',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => false, 'default' => 0]
			)
			->addColumn(
				'suspended',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => false, 'default' => 0]
			)
			->addColumn(
				'suspended_ms',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => false, 'default' => 0]
			)
			->addColumn(
				'cancelled',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => false, 'default' => 0]
			)
			->addColumn(
				'cancelled_ms',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => false, 'default' => 0]
			);

		$setup->getConnection()->createTable($table);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    private function upgradeTo_1_0_34(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'error_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'comment' => 'Error Code',
                    'nullable' => false,
                    'length' => 3,
                    'default' => 0,
                    'unsigned' => true
                ]
            );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    private function upgradeTo_1_0_35(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'admin_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Admin Id',
                    'nullable' => false,
                    'length' => 10,
                    'default' => 0
                ]
            );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    private function upgradeTo_1_0_36(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'currency_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Currency Code',
                    'nullable' => false,
                    'length' => 10,
                    'default' => '',
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'items_count',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Items Count',
                    'nullable' => false,
                    'length' => 11,
                    'default' => 0,
                    'unsigned' => true
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'items_qty',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Items Qty',
                    'nullable' => false,
                    'length' => 11,
                    'default' => 0,
                    'unsigned' => true
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'is_infinite',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'comment' => 'Is Infinite',
                    'nullable' => false,
                    'length' => 3,
                    'default' => 0,
                    'unsigned' => true
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'number_of_occurrences',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Number Of Occurrences',
                    'nullable' => false,
                    'length' => 11,
                    'default' => 0,
                    'unsigned' => true
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'engine_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Engine Code',
                    'length' => 50,
                ]
            );

        $table = $setup->getConnection()
            ->newTable($setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Backup::MAIN_TABLE))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'length' => 10, 'unsigned' => true],
                'ID'
            )
            ->addColumn(
                'profile_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true],
                'Profile ID'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true],
                'Customer ID'
            )
            ->addColumn(
                'admin_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true],
                'Admin ID'
            )
            ->addColumn(
                'payment_token_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'length' => 10, 'unsigned' => true],
                'Payment Token ID'
            )
            ->addColumn(
                'grand_total',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Grand Total'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'start_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => false],
                'Start Date'
            )
            ->addColumn(
                'last_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'length' => 10, 'unsigned' => true],
                'Last Order ID'
            )
            ->addColumn(
                'last_order_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Last Order At'
            )
			->addColumn(
				'source',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				null,
				['nullable' => false]
			)
			->addColumn(
				'merchant_source',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				null,
				['nullable' => false]
			)
            ->addColumn(
                'billing_address_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                65536,
                ['nullable' => true],
                'Billing Address JSON'
            )
            ->addColumn(
                'shipping_address_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                65536,
                ['nullable' => true],
                'Shipping Address JSON'
            )
            ->addColumn(
                'items_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                65536,
                ['nullable' => true],
                'Items JSON'
            )
            ->addColumn(
                'quote_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                65536,
                ['nullable' => true],
                'Quote JSON'
            )
            ->addColumn(
                'payment_token_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                65536,
                ['nullable' => true],
                'Payment Token JSON'
            )
            ->addColumn(
                'subscription_unit_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                65536,
                ['nullable' => true],
                'Subscription Unit JSON'
            )
            ->addColumn(
                'subscription_period_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                65536,
                ['nullable' => true],
                'Subscription Period JSON'
            )
            ->addColumn(
                'subscription_item_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                65536,
                ['nullable' => true],
                'Subscription Item JSON'
            )
            ->addColumn(
                'subscription_json',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                65536,
                ['nullable' => true],
                'Subscription JSON'
            )
            ->addIndex(
                $setup->getIdxName(\Toppik\Subscriptions\Model\ResourceModel\Profile\Backup::MAIN_TABLE, ['profile_id']),
                ['profile_id']
            );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Address::MAIN_TABLE)
        )
        ->addColumn(
            'address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Address Id'
        )
        ->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Profile ID'
        )
        ->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )
        ->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
            'Updated At'
        )
        ->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )
        ->addColumn(
            'address_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [],
            'Address Type'
        )
        ->addColumn(
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Email'
        )
        ->addColumn(
            'prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Prefix'
        )
        ->addColumn(
            'firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Firstname'
        )
        ->addColumn(
            'middlename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Middlename'
        )
        ->addColumn(
            'lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Lastname'
        )
        ->addColumn(
            'suffix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Suffix'
        )
        ->addColumn(
            'company',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Company'
        )
        ->addColumn(
            'street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Street'
        )
        ->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'City'
        )
        ->addColumn(
            'region',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Region'
        )
        ->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Region Id'
        )
        ->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Postcode'
        )
        ->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            [],
            'Country Id'
        )
        ->addColumn(
            'telephone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Phone Number'
        )
        ->addColumn(
            'fax',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'Fax'
        )
        ->addColumn(
            'shipping_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Shipping Method'
        )
        ->addColumn(
            'shipping_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Description'
        )
        ->addColumn(
            'payment_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            [],
            'Payment Method'
        )
        ->addColumn(
            'weight',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Weight'
        )
        ->addColumn(
            'subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Subtotal'
        )
        ->addColumn(
            'base_subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Subtotal'
        )
        ->addColumn(
            'subtotal_with_discount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Subtotal With Discount'
        )
        ->addColumn(
            'base_subtotal_with_discount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Subtotal With Discount'
        )
        ->addColumn(
            'tax_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Tax Amount'
        )
        ->addColumn(
            'base_tax_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Tax Amount'
        )
        ->addColumn(
            'shipping_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Shipping Amount'
        )
        ->addColumn(
            'base_shipping_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Shipping Amount'
        )
        ->addColumn(
            'shipping_tax_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Tax Amount'
        )
        ->addColumn(
            'base_shipping_tax_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Tax Amount'
        )
        ->addColumn(
            'discount_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Discount Amount'
        )
        ->addColumn(
            'base_discount_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Discount Amount'
        )
        ->addColumn(
            'grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Grand Total'
        )
        ->addColumn(
            'base_grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Grand Total'
        )
        ->addColumn(
            'customer_notes',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Customer Notes'
        )
        ->addColumn(
            'applied_taxes',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Applied Taxes'
        )
        ->addColumn(
            'discount_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Discount Description'
        )
        ->addColumn(
            'shipping_discount_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Discount Amount'
        )
        ->addColumn(
            'base_shipping_discount_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Discount Amount'
        )
        ->addColumn(
            'subtotal_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subtotal Incl Tax'
        )
        ->addColumn(
            'base_subtotal_total_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Subtotal Total Incl Tax'
        )
        ->addColumn(
            'discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Amount'
        )
        ->addColumn(
            'base_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Amount'
        )
        ->addColumn(
            'shipping_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Discount Tax Compensation Amount'
        )
        ->addColumn(
            'base_shipping_discount_tax_compensation_amnt',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Discount Tax Compensation Amount'
        )
        ->addColumn(
            'shipping_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Incl Tax'
        )
        ->addColumn(
            'base_shipping_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Incl Tax'
        )
        ->addIndex(
            $setup->getIdxName(\Toppik\Subscriptions\Model\ResourceModel\Profile\Address::MAIN_TABLE, ['profile_id']),
            ['profile_id']
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Item::MAIN_TABLE)
        )
        ->addColumn(
            'item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Item Id'
        )
        ->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Profile ID'
        )
        ->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )
        ->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
            'Updated At'
        )
        ->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Product Id'
        )
        ->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )
        ->addColumn(
            'parent_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Parent Item Id'
        )
        ->addColumn(
            'quote_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Quote Item Id'
        )
        ->addColumn(
            'product_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Product Type'
        )
        ->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Sku'
        )
        ->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )
        ->addColumn(
            'qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Qty'
        )
        ->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Price'
        )
        ->addColumn(
            'base_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Price'
        )
        ->addColumn(
            'custom_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Custom Price'
        )
        ->addColumn(
            'discount_percent',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Discount Percent'
        )
        ->addColumn(
            'discount_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Discount Amount'
        )
        ->addColumn(
            'base_discount_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Base Discount Amount'
        )
        ->addColumn(
            'tax_percent',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Tax Percent'
        )
        ->addColumn(
            'tax_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Tax Amount'
        )
        ->addColumn(
            'base_tax_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Base Tax Amount'
        )
        ->addColumn(
            'row_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Row Total'
        )
        ->addColumn(
            'base_row_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Base Row Total'
        )
        ->addColumn(
            'row_total_with_discount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Row Total With Discount'
        )
        ->addColumn(
            'row_weight',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0.0000'],
            'Row Weight'
        )
        ->addColumn(
            'base_tax_before_discount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Tax Before Discount'
        )
        ->addColumn(
            'tax_before_discount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Tax Before Discount'
        )
        ->addColumn(
            'original_custom_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Original Custom Price'
        )
        ->addColumn(
            'base_cost',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Cost'
        )
        ->addColumn(
            'price_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Price Incl Tax'
        )
        ->addColumn(
            'base_price_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Price Incl Tax'
        )
        ->addColumn(
            'row_total_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Row Total Incl Tax'
        )->addColumn(
            'base_row_total_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Row Total Incl Tax'
        )
        ->addColumn(
            'discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Amount'
        )
        ->addColumn(
            'base_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Amount'
        )
        ->addColumn(
            'item_options',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Item Options'
        )
        ->addIndex(
            $setup->getIdxName(\Toppik\Subscriptions\Model\ResourceModel\Profile\Item::MAIN_TABLE, ['profile_id']),
            ['profile_id']
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\History::MAIN_TABLE)
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )
        ->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Profile ID'
        )
        ->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )
        ->addColumn(
            'action_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Action Code'
        )
        ->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Customer ID'
        )
        ->addColumn(
            'admin_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Admin ID'
        )
        ->addColumn(
            'ip',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'IP'
        )
        ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Status'
        )
        ->addColumn(
            'cc',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'CC'
        )
        ->addColumn(
            'qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Qty'
        )
        ->addColumn(
            'frequency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Frequency'
        )
        ->addColumn(
            'next_order_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'next_order_at'
        )
        ->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Customer Email'
        )
        ->addColumn(
            'admin_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Admin Email'
        )
        ->addColumn(
            'message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Message'
        )
        ->addColumn(
            'note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Note'
        )
        ->addColumn(
            'last_suspend_error',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Last Suspend Error'
        )
        ->addColumn(
            'additional',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Additional'
        )
        ->addIndex(
            $setup->getIdxName(\Toppik\Subscriptions\Model\ResourceModel\Profile\History::MAIN_TABLE, ['profile_id']),
            ['profile_id']
        );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    private function upgradeTo_1_0_37(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'next_order_at_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'comment' => 'Next Order At Type',
                    'nullable' => false,
                    'length' => 3,
                    'default' => 1,
                    'unsigned' => true
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'next_order_at_original',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'comment' => 'Next Order At Original',
                    'nullable' => true
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('subscriptions_profiles'),
                'suspend_counter',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Suspend Counter',
                    'nullable' => false,
                    'length' => 10,
                    'default' => 0,
                    'unsigned' => true
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Item::MAIN_TABLE),
                'is_onetime_gift',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'comment' => 'Is Onetime Gift',
                    'nullable' => false,
                    'length' => 3,
                    'default' => 0,
                    'unsigned' => true
                ]
            );

        $table = $setup->getConnection()->newTable(
            $setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Points::MAIN_TABLE)
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )
        ->addColumn(
            'type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Type ID'
        )
        ->addColumn(
            'points',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Points'
        )
        ->addColumn(
            'rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Rule ID'
        )
        ->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Title'
        )
        ->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Description'
        )
        ->addColumn(
            'manager',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            'Manager'
        )
        ->addColumn(
            'position',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 1],
            'Position'
        )
        ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 1],
            'Status'
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Save::MAIN_TABLE)
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )
        ->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )
        ->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Profile ID'
        )
        ->addColumn(
            'option_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Option ID'
        )
        ->addColumn(
            'admin_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Admin ID'
        )
        ->addColumn(
            'used_points',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Used Points'
        )
        ->addColumn(
            'admin_points',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Admin Points'
        )
        ->addColumn(
            'subscription_points',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Subscription Points'
        )
        ->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Value'
        )
        ->addColumn(
            'ip',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'IP'
        )
        ->addColumn(
            'message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Message'
        )
        ->addIndex(
            $setup->getIdxName(\Toppik\Subscriptions\Model\ResourceModel\Profile\Save::MAIN_TABLE, ['profile_id']),
            ['profile_id']
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled::MAIN_TABLE)
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )
        ->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )
        ->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Profile ID'
        )
        ->addColumn(
            'option_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Option ID'
        )
        ->addColumn(
            'admin_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Admin ID'
        )
        ->addColumn(
            'ip',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'IP'
        )
        ->addColumn(
            'message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Message'
        )
        ->addIndex(
            $setup->getIdxName(\Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled::MAIN_TABLE, ['profile_id']),
            ['profile_id']
        );

        $setup->getConnection()->createTable($table);
    }

    private function upgradeTo_1_0_39(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $connection = $setup->getConnection();
        $connection->query('ALTER TABLE subscriptions_subscriptions add column `store_id` smallint(5) unsigned DEFAULT NULL COMMENT "Store Id" AFTER product_id;');
        $connection->query('ALTER TABLE subscriptions_profiles add column `store_id` smallint(5) unsigned DEFAULT NULL COMMENT "Store Id" AFTER admin_id;');
    }
    
    private function upgradeTo_1_0_40(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Add::MAIN_TABLE)
        )
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )
        ->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Sku'
        )
        ->addColumn(
            'qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 1],
            'Qty'
        )
        ->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Price'
        )
        ->addColumn(
            'public_hash',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Public Hash'
        )
        ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 1],
            'Status'
        );

        $setup->getConnection()->createTable($table);
    }

	private function upgradeTo_1_0_42(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$setup->startSetup();

		$setup->getConnection()->changeColumn(
			$setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Add::MAIN_TABLE),
			'public_hash',
			'public_hash',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'length' => 255,
				'nullable' => false,
				'comment' => 'Public Hash'
			]
		);


		$setup->getConnection()->addIndex(
			$setup->getTable(\Toppik\Subscriptions\Model\ResourceModel\Profile\Add::MAIN_TABLE),
			$setup->getIdxName(
				\Toppik\Subscriptions\Model\ResourceModel\Profile\Add::MAIN_TABLE,
				['public_hash'],
				AdapterInterface::INDEX_TYPE_UNIQUE
			),
			'public_hash',
			AdapterInterface::INDEX_TYPE_UNIQUE
		);

		$setup->endSetup();

	}

}
