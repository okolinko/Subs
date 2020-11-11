<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/10/16
 * Time: 7:53 PM
 */

namespace Toppik\Subscriptions\Block\Adminhtml\Management\Sku;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{

    protected function _construct()
    {
        $this->_blockGroup = 'Toppik_Subscriptions';
        $this->_controller = 'adminhtml_management_sku';

        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');

        $this->buttonList->update('save', 'label', 'Proceed');
    }

}