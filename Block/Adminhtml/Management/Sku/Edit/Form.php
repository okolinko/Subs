<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/10/16
 * Time: 7:54 PM
 */

namespace Toppik\Subscriptions\Block\Adminhtml\Management\Sku\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as FormClass;

class Form extends Generic
{

    protected function _prepareForm()
    {

        /* @var FormClass $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/skuchange'),
                'method' => 'post'
            ],
        ]);

        $form->setHtmlIdPrefix('post_');

        $generalFieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('You will not be able to revert changes!'), 'class' => 'fieldset-wide']
        );

        $generalFieldset->addField(
            'source_sku',
            'text',
            [
                'label' => __('Source SKU'),
                'title' => __('Source SKU'),
                'name' => 'source_sku',
                'required' => true,
            ]
        );

        $generalFieldset->addField(
            'target_sku',
            'text',
            [
                'label' => __('Target SKU'),
                'title' => __('Target SKU'),
                'name' => 'target_sku',
                'required' => true,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}