<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/6/16
 * Time: 2:49 PM
 */

namespace Toppik\Subscriptions\Model\Quote\Address\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Toppik\Subscriptions\Plugin\Magento\Quote\Model\Quote as QuotePlugin;

class ShowSubscriptionItems extends AbstractTotal
{

    /**
     * @var QuotePlugin
     */
    private $quotePlugin;

    public function __construct(QuotePlugin $quotePlugin)
    {
        $this->quotePlugin = $quotePlugin;
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    )
    {
//        $this->quotePlugin->showSubscriptionItems();
//        $shippingAssignment->setItems($quote->getItems());
        return parent::collect($quote, $shippingAssignment, $total);
    }

}