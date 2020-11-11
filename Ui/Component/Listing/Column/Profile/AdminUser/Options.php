<?php
namespace Toppik\Subscriptions\Ui\Component\Listing\Column\Profile\AdminUser;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    protected $_userResourceModel;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Grid\Collection $_orderGridCollection
     * @param array $data
     */
    public function __construct(\Magento\User\Model\ResourceModel\User $resourceModel )
    {
        $this->_userResourceModel = $resourceModel;
    }

    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $result = [];

            $_connection = $this->_userResourceModel->getConnection();
            $_select = $_connection->select()
                ->from($this->_userResourceModel->getMainTable(), array('user_id', 'firstname', 'lastname'));


            foreach($_connection->fetchAll($_select) as $_option) {
                $result[] = [
                                'value' => $_option['user_id'],
                                'label' => $_option['firstname'] . ' ' . $_option['lastname'],
                            ];
            }

            $this->options = $result;
        }
        return $this->options;
    }
}
