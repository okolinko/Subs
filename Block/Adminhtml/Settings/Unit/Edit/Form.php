<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 9:07 PM
 */

namespace Toppik\Subscriptions\Block\Adminhtml\Settings\Unit\Edit;



use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as FormClass;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Toppik\Subscriptions\Model\Settings\Unit;

class Form extends Generic
{

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * Form constructor.
     * @param Store $systemStore
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
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
        $this->setId('unit_form');
        $this->setTitle(__('Unit Information'));
    }

    protected function _prepareForm()
    {
        /* @var $model Unit */
        $model = $this->_coreRegistry->registry('unit');

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
            $generalFieldset->addField('unit_id', 'hidden', ['name' => 'unit_id']);
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

        $generalFieldset->addField(
            'length',
            'text',
            [
                'label' => __('Duration, Seconds'),
                'title' => __('Duration, Seconds'),
                'name' => __('length'),
                'required' => true,
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}