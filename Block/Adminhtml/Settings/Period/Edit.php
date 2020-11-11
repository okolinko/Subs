<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 8:55 PM
 */

namespace Toppik\Subscriptions\Block\Adminhtml\Settings\Period;


use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

class Edit extends Container
{

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data)
    {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'period_id';
        $this->_blockGroup = 'Toppik_Subscriptions';
        $this->_controller = 'adminhtml_settings_period';
        $this->buttonList->update('save', 'label', __('Save Period'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form', ],
                    ],
                ],
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Period'));
        parent::_construct();
    }

    /**
     * @return mixed
     */
    public function getHeaderText()
    {
        $period = $this->registry->registry('period');
        if($period->getId()) {
            return __('Edit Period "%1%', $this->escapeHtml($period->getTitle()));
        } else {
            return __('New Period');
        }
    }

}