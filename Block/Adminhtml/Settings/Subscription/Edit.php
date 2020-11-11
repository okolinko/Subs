<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 8:55 PM
 */

namespace Toppik\Subscriptions\Block\Adminhtml\Settings\Subscription;


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
        $this->_objectId = 'subscription_id';
        $this->_blockGroup = 'Toppik_Subscriptions';
        $this->_controller = 'adminhtml_settings_subscription';
        $this->buttonList->update('save', 'label', __('Save Subscription'));
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
        $this->buttonList->update('delete', 'label', __('Delete Subscription'));
        $this->buttonList->remove('add');
        $this->_formScripts[] = '
require(["uiRegistry", "jquery"], function(registry, $) {
    var promise = registry.promise("item_listing.item_listing");
    promise.then(function(listing) {
        var validator = $("#edit_form").validate();
        var originalForm = validator.form;
        
        var d = new Date;
        var errorOccurred = false;
        var to;
        
        var el = {name:"item_listing"};
        
        
        validator.form = function() {
            var now = new Date;
            if(now - d > 500) {
                errorOccurred = false;
                listing.source.trigger(\'data.validate\');
                clearTimeout(to);
                validator.startRequest(el);
                d = now;
                to = setTimeout(function() {
                    validator.formSubmitted = true;
                    validator.stopRequest(el, true);
                }, 300);
                return false;
            } else {
                var ret = originalForm.apply(this, arguments);
                if(ret) {
                    ret = ! errorOccurred;
                }
                return ret;
            }
        };
        listing.on("error", function(e) {
            if(e) {
                errorOccurred = true;
            }
        });
    });
});
        ';
        parent::_construct();
    }

    /**
     * @return mixed
     */
    public function getHeaderText()
    {
        $subscription = $this->registry->registry('subscription');
        if($subscription->getId()) {
            return __('Edit Subscription');
        } else {
            return __('New Subscription');
        }
    }

}