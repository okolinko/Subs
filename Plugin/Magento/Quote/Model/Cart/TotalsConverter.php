<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/6/16
 * Time: 3:14 PM
 */

namespace Toppik\Subscriptions\Plugin\Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;

class TotalsConverter
{

    /**
     * @var TotalSegmentExtensionFactory
     */
    protected $totalSegmentExtensionFactory;

    /**
     * @param TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     */
    public function __construct(
        TotalSegmentExtensionFactory $totalSegmentExtensionFactory
    ) {
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
    }

    /**
     * @param \Magento\Quote\Model\Cart\TotalsConverter $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Address\Total[] $addressTotals
     * @return \Magento\Quote\Api\Data\TotalSegmentInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcess(
        \Magento\Quote\Model\Cart\TotalsConverter $subject,
        \Closure $proceed,
        array $addressTotals = []
    ) {
        /** @var \Magento\Quote\Api\Data\TotalSegmentInterface[] $totals */
        $totalSegments = $proceed($addressTotals);

        $totalSegments = $this->processSubscriptions($totalSegments, $addressTotals);

        return $totalSegments;
    }

    /**
     * @param \Magento\Quote\Api\Data\TotalSegmentInterface[] $totalSegments
     * @param \Magento\Quote\Model\Quote\Address\Total[] $addressTotals
     * @return \Magento\Quote\Api\Data\TotalSegmentInterface[]
     */
    private function processSubscriptions($totalSegments, array $addressTotals = [])
    {
        $code = 'subscription_subtotal';

        if (!isset($addressTotals[$code])) {
            return $totalSegments;
        }

        $total = $addressTotals[$code];
        /** @var \Magento\Quote\Api\Data\TotalSegmentExtensionInterface $totalSegmentExtension */
        $totalSegmentExtension = $this->totalSegmentExtensionFactory->create();
        $totalSegmentExtension->setItems($total->getItems());
        $totalSegmentExtension->setGrandTotal($total->getGrandTotal());

        $totalSegments[$code]->setExtensionAttributes($totalSegmentExtension);

        return $totalSegments;
    }

}