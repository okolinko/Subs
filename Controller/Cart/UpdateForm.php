<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11/1/16
 * Time: 4:29 PM
 */

namespace Toppik\Subscriptions\Controller\Cart;

use Magento\Framework\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

class UpdateForm extends Action\Action
{

    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    public function __construct(
        PageFactory $resultPageFactory,
        JsonFactory $jsonFactory,
        Action\Context $context
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        // Render page
        $json = $this->jsonFactory->create();
        try {
            $page = $this->resultPageFactory->create();
            $page->initLayout();
            $content = $page->getLayout()->renderNonCachedElement('content');
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $json->setData([
                    'content' => $content,
                ]);
            } else {
                return $page;
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $json->setData([
                'success' => false,
                'message' => __('Unknown error occurred'),
            ]);
        }
        return $json;
    }
}