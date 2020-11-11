<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/16/16
 * Time: 6:24 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\Catalog\Helper\Product;

use Magento\Framework\View\Result\Page as ResultPage;
use Toppik\Subscriptions\Helper\Data;

class View
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * Product constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    )
    {
        $this->helper = $helper;
    }

    public function beforeInitProductLayout(\Magento\Catalog\Helper\Product\View $viewHelper, ResultPage $resultPage, $product, $params = null) {
        if($this->helper->productHasSubscription($product)) {
            $resultPage->getLayout()->getUpdate()->addHandle('catalog_product_view_subscription');
        }
        return [
            $resultPage,
            $product,
            $params
        ];
    }

}