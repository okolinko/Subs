<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11/1/16
 * Time: 6:41 PM
 */

namespace Toppik\Subscriptions\Controller\Cart;

use Magento\Framework\App\Action;
use Magento\Framework\App\ResponseInterface;
use Toppik\Subscriptions\Model\Preferences;
use Magento\Framework\Controller\Result\JsonFactory;

class Update extends Action\Action
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    
    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 101.1.0
     */
    protected $serializer;
    
    public function __construct(
        JsonFactory $jsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        Action\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    )
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->jsonFactory = $jsonFactory;
        $this->serializer = $serializer;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $code 		= sprintf('option_%s', Preferences::SUBSCRIPTION_OPTION_ID);
        $json = $this->jsonFactory->create();
        $newIds = [];
        if($cartData = $this->getRequest()->getParam('cart')) {
            if(empty($cartData)) {
                return;
            }
            try {
                if(is_array($cartData)) {
                    foreach($cartData as $index => $data) {
                        /* Update product attributes */
                        $item = $this->checkoutSession->getQuote()->getItemById($index);

                        if(!$item) continue;

                        if(!isset($data['option']) || empty($data['option'])) continue;

                        $updatedSubscription = FALSE;

                        foreach($item->getOptions() as $option) {
                            if($option->getCode() == 'info_buyRequest') {
                                $unserialized = $this->serializer->unserialize($option->getValue());

                                foreach($data['option'] as $id => $value) {
                                    if(isset($unserialized['options'][$id])) {
                                        $unserialized['options'][$id] = $value;
                                    }
                                }

                                $option->setValue($this->serializer->serialize($unserialized));
                            } else if($code == $option->getCode()) {
                                foreach($data['option'] as $id => $value) {
                                    if($id == Preferences::SUBSCRIPTION_OPTION_ID AND $value != $option->getValue()) {
                                        $option->setValue($value);
                                        $item->setDiscountAmount(0);
                                        $item->setBaseDiscountAmount(0);
                                        $item->setDiscountPercent(0);
                                        $updatedSubscription = TRUE;
                                    }
                                }
                            }
                        }
                        if($updatedSubscription) {
                            $oldId = $item->getId();
                            $buyRequest = $item->getOptionByCode('info_buyRequest');
                            $brequestData = $this->serializer->unserialize($buyRequest->getValue());
                            $brequestData['qty'] = $item->getQty();
                            $this->cart->removeItem($item->getId());
                            $product = $this->productRepository->getById($item->getProductId(), false, null, true);
                            $this->cart->addProduct($product, $brequestData);
                            $quoteItem = $product->getJustCreatedQuoteItem();
                            $newIds[$oldId] = $quoteItem;
                        } else {
                            $item->save();
                        }
                    }

                    if(!$this->cart->getCustomerSession()->getCustomer()->getId() && $this->cart->getQuote()->getCustomerId()) {
                        $this->cart->getQuote()->setCustomerId(null);
                    }

                    $cartData = $this->cart->suggestItemsQty($cartData);
                    $this->cart->updateItems($cartData)
                        ->save();
                }
            } catch (\Exception $e) {

            }
        }
        $convert = [];
        foreach($newIds as $oldId => $quoteItem) {
            $convert[$oldId] = $quoteItem->getId();
        }
        $json->setData([
            'convert' => $convert,
        ]);
        return $json;
    }
}