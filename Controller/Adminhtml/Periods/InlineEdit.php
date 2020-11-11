<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 7:34 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Periods;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends Action
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $periods = $this->getRequest()->getParam('items', []);

        if (!($this->getRequest()->getParam('isAjax') && count($periods))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($periods) as $periodId) {
            try {
                $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Settings\Period');
                $model->load($periodId);
                $model->addData($periods[$periodId]);
                $model->save();
            } catch(\Magento\Framework\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch(\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the period.'));
            }
        }

        $messages = [];

        foreach($this->messageManager->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => (bool)$this->getMessageManager()->getMessages(true)->getCount(),
        ]);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_settings_periods');
    }
}