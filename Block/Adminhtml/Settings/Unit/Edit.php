<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 8:55 PM
 */

namespace Toppik\Subscriptions\Block\Adminhtml\Settings\Unit;


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
        $this->_objectId = 'unit_id';
        $this->_blockGroup = 'Toppik_Subscriptions';
        $this->_controller = 'adminhtml_settings_unit';
        $this->buttonList->update('save', 'label', __('Save Unit'));
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
        $this->buttonList->update('delete', 'label', __('Delete Unit'));
        parent::_construct();
    }

    /**
     * @return mixed
     */
    public function getHeaderText()
    {
        $unit = $this->registry->registry('unit');
        if($unit->getId()) {
            return __('Edit Unit "%1%', $this->escapeHtml($unit->getTitle()));
        } else {
            return __('New Unit');
        }
    }

}