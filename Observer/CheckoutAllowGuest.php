<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/26/16
 * Time: 8:26 PM
 */

namespace Toppik\Subscriptions\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Toppik\Subscriptions\Helper\Quote as QuoteHelper;

class CheckoutAllowGuest implements ObserverInterface
{

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * CheckoutAllowGuest constructor.
     * @param QuoteHelper $quoteHelper
     */
    public function __construct(
        QuoteHelper $quoteHelper
    )
    {
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /* @var Quote $quote */
        $quote = $observer->getQuote();
        if($this->quoteHelper->quoteHasSubscription($quote)) {
            /* @var DataObject $result */
            $result = $observer->getResult();
            $result->setIsAllowed(false);
        }
    }
}