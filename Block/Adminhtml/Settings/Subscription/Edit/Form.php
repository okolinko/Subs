<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 9:07 PM
 */

namespace Toppik\Subscriptions\Block\Adminhtml\Settings\Subscription\Edit;



use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser;
use Magento\Framework\Data\Form as FormClass;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Toppik\Subscriptions\Model\Settings\Item\Source\PeriodId;
use Toppik\Subscriptions\Model\Settings\Subscription;
use Toppik\Subscriptions\Model\Settings\Item\Source\UseCouponCode;

class Form extends Generic
{

    /**
     * @var
     */
    protected $systemStore;
    /**
     * @var PeriodId
     */
    private $sourcePeriodId;
    /**
     * @var UseCouponCode
     */
    private $useCouponCode;

    /**
     * Form constructor.
     * @param UseCouponCode $useCouponCode
     * @param PeriodId $sourcePeriodId
     * @param Store $systemStore
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        UseCouponCode $useCouponCode,
        PeriodId $sourcePeriodId,
        Store $systemStore,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data)
    {
        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->sourcePeriodId = $sourcePeriodId;
        $this->useCouponCode = $useCouponCode;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('subscription_form');
        $this->setTitle(__('Subscription Information'));
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /* @var $model Subscription */
        $model = $this->_coreRegistry->registry('subscription');

        /* @var FormClass $form */
        $form = $this->_formFactory->create([
            'data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']
        ]);

        $form->setHtmlIdPrefix('');

        /** GENERAL **/

        $generalFieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if($model->getId()) {
            $generalFieldset->addField('subscription_id', 'hidden', ['name' => 'subscription_id']);
        }

        if($model->getId()) {
            $generalFieldset->addField(
                'product_name',
                'link',
                [
                    'label' => __('Product'),
                    'title' => __('Product'),
                    'href' => $this->getUrl('catalog/product/edit', array('id' => $model->getProduct()->getId())),
                    'target' => '_blank',
                ]
            );
        } else {
            $productId = $generalFieldset->addField(
                'product_id',
                'note',
                [
                    'name' => 'product_id',
                    'label' => __('Product'),
                    'title' => __('Product'),
                    'required' => true,
                    'value' => ($model->getProductId() ? 'product/' . $model->getProductId() : ''),
                ]
            );

            /** @var Chooser $productChooser */
            $productChooser = $this->_layout->createBlock('Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser');
            $productChooser->setFieldsetId($generalFieldset->getId());
            $productChooser->setConfig([
                'button' => [
                    'open' => __('Select Product...'),
                ],
            ]);
            $productChooser->prepareElementHtml($productId);
        }

        $generalFieldset->addField(
            'is_subscription_only',
            'select',
            [
                'label' => __('Is Subscription Only'),
                'title' => __('Is Subscription Only'),
                'name' => 'is_subscription_only',
                'required' => true,
                'options' => $model->getAvailableIsSubscriptionOnly(),
            ]
        );

        $generalFieldset->addField(
            'move_customer_to_group_id',
            'select',
            [
                'label' => __('Move Customer to Group ID'),
                'title' => __('Move Customer to Group ID'),
                'name' => 'move_customer_to_group_id',
                'required' => true,
                'options' => $model->getAvailableGroupIds(),
            ]
        );

        $generalFieldset->addField(
            'start_date_code',
            'select',
            [
                'label' => __('Start Date Code'),
                'title' => __('Start Date Code'),
                'name' => 'start_date_code',
                'required' => true,
                'options' => $model->getAvailableStartDateCodes(),
            ]
        );

        $generalFieldset->addField(
            'day_of_month',
            'text',
            [
                'label' => __('Day of Month'),
                'title' => __('Day of Month'),
                'name' => 'day_of_month',
                'required' => true,
            ]
        );
        
        $generalFieldset->addField(
            'store_id',
            'select',
            [
                'label' => __('Store'),
                'title' => __('Store'),
                'name' => 'store_id',
                'required' => false,
                'options' => $model->getAvailableStoreIds(),
            ]
        );

        $types = $generalFieldset->addField(
            'types',
            'hidden',
            [
                'name' => 'types',
            ]
        );

        $types->setData('after_element_js', '
<div class="admin__data-grid-outer-wrap" data-bind="scope: \'item_listing.item_listing\'">
    <div data-role="spinner" data-component="item_listing.item_listing.items" class="admin__data-grid-loading-mask">
        <div class="spinner">
            <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>
<!-- ko template: getTemplate() --><!-- /ko -->
</div>
<script type="text/x-magento-init">' . \Zend_Json::prettyPrint(\Zend_Json::encode($this->getItemInit())) .'</script>
        ');



        /** @var Dependence $depencies */
        $dependencies = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');

        $this->setChild(
            'form_after',
            $dependencies
                ->addFieldMap(
                    "{$form->getHtmlIdPrefix()}start_date_code",
                    'start_date_code'
                )
                ->addFieldMap(
                    "{$form->getHtmlIdPrefix()}day_of_month",
                    'day_of_month'
                )
                ->addFieldDependence(
                    'day_of_month',
                    'start_date_code',
                    Subscription::START_DATE_BY_EXACT_DAY_OF_MONTH
                )
        );

        $form->setValues(array_merge(
            $model->getData(),
            [
                'product_name' => $model->getProduct()->getName(),
            ]
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getItemInit()
    {
        return [
            '*' => [
                'Magento_Ui/js/core/app' => [
                    'types' => $this->getTypes(),
                    'components' => $this->getComponents(),
                ],
            ],
        ];
    }

    private function getTypes()
    {
        return [
            'fieldset' => [
                'component' => 'Magento_Ui/js/form/components/fieldset',
                'extends' => 'item_listing',
            ],
            'item_listing' => [
                'component' => 'Magento_Ui/js/form/form',
                'provider' => 'item_listing.item_listing_data_source',
                'deps' => 'item_listing.item_listing_data_source',
                'namespace' => 'item_listing',
            ],
            'dataSource' => [
                'component' => 'Magento_Ui/js/form/provider',
            ],
            'container' => [
                'extends '=> 'item_listing',
            ],
            'form.input' => [
                'extends' => 'input',
            ],
            'input' => [
                'extends' => 'item_listing',
            ],
            'form.select' => [
                'extends' => 'select',
            ],
            'select' => [
                'extends' => 'item_listing',
            ],
            'form.currency' => [
                'extends' => 'form.input',
            ],
        ];
    }

    private function getComponents()
    {
        return [
            'item_listing' => [
                'children' => [
                    'item_listing' => [
                        'type' => 'fieldset',
                        'name' => 'item_listing',
                        'dataScope' => 'data.item_listing',
                        'config' => [
                            'label' => __('Subscription Items'),
                            'componentType' => 'fieldset',
                            'collapsible' => false,
                            'sortOrder' => 10
                        ],
                        'children' => [
                            'container_header' => [
                                'type' => 'container',
                                'name' => 'container_header',
                                'config' => [
                                    'component' => 'uiComponent',
                                    'label' => null,
                                    'formElement' => 'container',
                                    'componentType' => 'container',
                                    'template' => 'ui/form/components/complex',
                                    'sortOrder' => 10,
                                    'content' => __('Items let customers choose different periods they want.'),
                                ],
                                'children' => [
                                    'button_add' => [
                                        'type' => 'container',
                                        'name' => 'button_add',
                                        'config' => [
                                            'component' => 'Magento_Ui/js/form/components/button',
                                            'title' => 'Add Item',
                                            'formElement' => 'container',
                                            'componentType' => 'container',
                                            'sortOrder' => 10,
                                            'actions' => [[
                                                'targetName' => 'ns = ${ $.ns }, index = items',
                                                'actionName' => 'processingAddChild',
                                            ]],
                                        ],
                                    ],
                                ],
                            ],
                            'items' => [
                                'type' => 'container',
                                'name' => 'items',
                                'config' => [
                                    'component' => 'Toppik_Subscriptions/js/components/dynamic-rows-import-items',
                                    'template' => 'ui/dynamic-rows/templates/collapsible',
                                    'addButtonLabel' => __('Add Option'),
                                    'componentType' => 'dynamicRows',
                                    'additionalClasses' => 'admin__field-wide',
                                    'deleteProperty' => 'is_deleted',
                                    'deleteValue' => '1',
                                    'addButton' => false,
                                    'renderDefaultRecord' => false,
                                    'columnsHeader' => false,
                                    'collapsibleHeader' => true,
                                    'sortOrder' => 20,
                                    'dataProvider' => 'item_listing.item_listing_data_source',
                                    'links' => [
                                        'insertData' => '${ $.provider }:${ $.dataScope }.${ $.index }',
                                    ],
                                ],
                                'children' => [
                                    'record' => [
                                        'type' => 'container',
                                        'name' => 'record',
                                        'config' => [
                                            'component' => 'Toppik_Subscriptions/js/components/dynamic-rows-record',
                                            'componentType' => 'container',
                                            'positionProvider' => 'container_option.sort_order',
                                            'isTemplate' => true,
                                            'is_collection' => true,
                                            'imports' => [],
                                            'links' => [
                                                'position' => '${ $.name }.${ $.positionProvider }:value',
                                                'data' => '${ $.provider }:${ $.dataScope }',
                                            ],
                                            'labelSource' => 'container_option.container_common.period_id'
                                        ],
                                        'children' => [
                                            'container_option' => [
                                                'type' => 'fieldset',
                                                'name' => 'container_option',
                                                'config' => [
                                                    'componentType' => 'fieldset',
                                                    'label' => null,
                                                    'sortOrder' => 10,
                                                    'opened' => true,
                                                ],
                                                'children' => [
                                                    'sort_order' => [
                                                        'type' => 'form.input',
                                                        'name' => 'sort_order',
                                                        'dataScope' => 'sort_order',
                                                        'config' => [
                                                            'component' => 'Magento_Ui/js/form/element/abstract',
                                                            'template' => 'ui/form/field',
                                                            'componentType' => 'field',
                                                            'formElement' => 'input',
                                                            'dataType' => 'number',
                                                            'visible' => false,
                                                            'sortOrder' => 40,
                                                        ],
                                                    ],
                                                    'container_common' => [
                                                        'type' => 'container',
                                                        'name' => 'container_common',
                                                        'config' => [
                                                            'component' => 'Magento_Ui/js/form/components/group',
                                                            'template' => 'subscriptions/group/group',
                                                            'componentType' => 'container',
                                                            'formElement' => 'container',
                                                            'breakLine' => false,
                                                            'showLabel' => false,
                                                            'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                                                            'sortOrder' => 10,
                                                        ],
                                                        'children' => [
                                                            'item_id' => [
                                                                'type' => 'form.input',
                                                                'name' => 'item_id',
                                                                'dataScope' => 'item_id',
                                                                'config' => [
                                                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                                                    'template' => 'ui/form/field',
                                                                    'formElement' => 'input',
                                                                    'componentType' => 'field',
                                                                    'sortOrder' => 10,
                                                                    'visible' => false,
                                                                ],
                                                            ],
                                                            'is_deleted' => [
                                                                'type' => 'form.input',
                                                                'name' => 'is_deleted',
                                                                'dataScope' => 'is_deleted',
                                                                'config' => [
                                                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                                                    'template' => 'ui/form/field',
                                                                    'formElement' => 'input',
                                                                    'componentType' => 'field',
                                                                    'sortOrder' => 15,
                                                                    'visible' => false,
                                                                ],
                                                            ],
                                                            'period_id' => [
                                                                'type' => 'form.select',
                                                                'name' => 'period_id',
                                                                'dataScope' => 'period_id',
                                                                'config' => [
                                                                    'component' => 'Toppik_Subscriptions/js/form/element/select',
                                                                    'elementTmpl' => 'subscriptions/form/element/select',
                                                                    'label' => __('Period'),
                                                                    'componentType' => 'field',
                                                                    'formElement' => 'select',
                                                                    'sortOrder' => 20,
                                                                    'options' => $this->sourcePeriodId->toOptionArray(),
                                                                    'disableButton' => 'item_listing.item_listing.container_header.button_add',
                                                                    'validation' => [
                                                                        'required-entry' => true,
                                                                    ],
                                                                ],
                                                            ],
                                                            'regular_price' => [
                                                                'type' => 'form.currency',
                                                                'name' => 'regular_price',
                                                                'dataScope' => 'regular_price',
                                                                'config' => [
                                                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                                                    'template' => 'ui/form/element/input',
                                                                    'label' => __('Price Per Iteration'),
                                                                    'componentType' => 'field',
                                                                    'formElement' => 'input',
                                                                    'sortOrder' => 30,
                                                                    'addbefore' => '$',
                                                                    'validation' => [
                                                                        'required-entry' => true,
                                                                        'not-negative-amount' => true,
                                                                    ],
                                                                ],
                                                            ],
                                                            'use_coupon_code' => [
                                                                'type' => 'form.select',
                                                                'name' => 'use_coupon_code',
                                                                'dataScope' => 'use_coupon_code',
                                                                'config' => [
                                                                    'component' => 'Magento_Ui/js/form/element/select',
                                                                    'template' => 'ui/form/element/select',
                                                                    'label' => __('Use Coupon Code'),
                                                                    'componentType' => 'field',
                                                                    'formElement' => 'select',
                                                                    'sortOrder' => 40,
                                                                    'options' => $this->useCouponCode->toOptionArray(),
                                                                    'validation' => [
                                                                        'required-entry' => true,
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'item_listing_data_source' => $this->getDataSource(),
                ],
            ],
        ];
    }

    private function getDataSource()
    {
        return [
            'type' => 'dataSource',
            'name' => 'item_listing_data_source',
            'dataScope' => 'item_listing',
            'config' => [
                'data' => [
                    'item_listing' => [
                        'items' => $this->getItemsData(),
                    ],
                ],
            ],
        ];
    }

    private function getItemsData()
    {
        /** @var Subscription $model */
        $model = $this->_coreRegistry->registry('subscription');
        $items = $model->getItemsCollection();
        $itemsData = [];
        foreach($items AS $item) {
            /* @var \Toppik\Subscriptions\Model\Settings\Item $item */
            $itemsData[] = $item->toArray();
        }
        return $itemsData;
    }

}
