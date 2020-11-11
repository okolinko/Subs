<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 9:07 PM
 */

namespace Toppik\Subscriptions\Block\Adminhtml\Settings\Period\Edit;



use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as FormClass;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Toppik\Subscriptions\Model\Settings\Period;

class Form extends Generic
{

    /**
     * @var
     */
    protected $systemStore;

    public function __construct(
        Store $systemStore,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data)
    {
        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('period_form');
        $this->setTitle(__('Period Information'));
    }

    protected function _prepareForm()
    {
        /* @var $model Period */
        $model = $this->_coreRegistry->registry('period');

        /* @var FormClass $form */
        $form = $this->_formFactory->create([
            'data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']
        ]);

        $form->setHtmlIdPrefix('post_');

        /** GENERAL **/

        $generalFieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if($model->getId()) {
            $generalFieldset->addField('period_id', 'hidden', ['name' => 'period_id']);
        }

        $generalFieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
            ]
        );

        if($model->getId()) {
            $generalFieldset->addField(
                'engine_code',
                'note',
                [
                    'label' => __('Engine Code'),
                    'title' => __('Engine Code'),
                    'text' => $model->getEngineCode(),
                ]
            );
        } else {
            $generalFieldset->addField(
                'engine_code',
                'select',
                [
                    'label' => __('Engine Code'),
                    'title' => __('Engine Code'),
                    'name' => 'engine_code',
                    'required' => true,
                    'options' => $model->getAvailableEngineCodes(),
                    'disabled' => !! $model->getId(),
                ]
            );
        }

        $generalFieldset->addField(
            'is_visible',
            'select',
            [
                'label' => __('Visibility'),
                'title' => __('Visibility'),
                'name' => 'is_visible',
                'required' => true,
                'options' => $model->getAvailableVisibilityOptions(),
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $generalFieldset->addField(
                'store_ids',
                'multiselect',
                [
                    'name' => 'store_ids',
                    'required' => true,
                    'label' => __('Stores'),
                    'values' => $this->systemStore->getStoreValuesForForm(false, true),
                    'value' => $model->getStoreIds()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        }

        /** SCHEDULE **/

        $scheduleFieldset = $form->addFieldset(
            'schedule_fieldset',
            ['legend' => __('Schedule'), 'class' => 'fieldset-wide']
        );

        $scheduleFieldset->addField(
            'unit_id',
            'select',
            [
                'label' => __('Period Unit'),
                'title' => __('Period Unit'),
                'name' => 'unit_id',
                'required' => true,
                'options' => $model->getAvailablePeriodUnits(),
            ]
        );

        $scheduleFieldset->addField(
            'length',
            'text',
            [
                'label' => __('Number of Units in Period'),
                'title' => __('Number of Units in Period'),
                'name' => 'length',
                'required' => true,
            ]
        );

        $scheduleFieldset->addField(
            'is_infinite',
            'select',
            [
                'label' => __('Is Infinite'),
                'title' => __('Is Infinite'),
                'name' => 'is_infinite',
                'required' => true,
                'options' => $model->getAvailableRepeatOptions(),
            ]
        );

        $scheduleFieldset->addField(
            'number_of_occurrences',
            'text',
            [
                'label' => __('Number of Occurrences'),
                'title' => __('Number of Occurrences'),
                'name' => 'number_of_occurrences',
                'required' => true,
            ]
        );

        /** @var Dependence $depencies */
        $dependencies = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');

        $this->setChild(
            'form_after',
            $dependencies
                ->addFieldMap(
                    "{$form->getHtmlIdPrefix()}is_infinite",
                    'is_infinite'
                )
                ->addFieldMap(
                    "{$form->getHtmlIdPrefix()}number_of_occurrences",
                    'number_of_occurrences'
                )
                ->addFieldDependence(
                    'number_of_occurrences',
                    'is_infinite',
                    Period::FINITE
                )
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}