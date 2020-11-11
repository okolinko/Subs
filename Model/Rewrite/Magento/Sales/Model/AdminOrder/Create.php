<?php
namespace Toppik\Subscriptions\Model\Rewrite\Magento\Sales\Model\AdminOrder;

class Create extends \Magento\Sales\Model\AdminOrder\Create {
    
    /**
     * Prepare options array for info buy request
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    protected function _prepareOptionsForRequest($item) {
        $newInfoOptions = [];
        $optionIds = $item->getOptionByCode('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $item->getProduct()->getOptionById($optionId);

                if(!$option) {
                    continue;
                }

                $optionValue = $item->getOptionByCode('option_' . $optionId)->getValue();

                $group = $this->_objectManager->get(
                    \Magento\Catalog\Model\Product\Option::class
                )->groupFactory(
                    $option->getType()
                )->setOption(
                    $option
                )->setQuoteItem(
                    $item
                );

                $newInfoOptions[$optionId] = $group->prepareOptionValueForRequest($optionValue);
            }
        }

        return $newInfoOptions;
    }

    /**
     * Prepare item otions
     *
     * @return $this
     */
    protected function _prepareQuoteItems()
    {

        foreach ($this->getQuote()->getAllItems() as $item) {
        /*
            $options = [];
            $productOptions = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
            if ($productOptions) {
                $productOptions['info_buyRequest']['options'] = $this->_prepareOptionsForRequest($item);
                $options = $productOptions;
            }
            $addOptions = $item->getOptionByCode('additional_options');
            if ($addOptions) {
                $options['additional_options'] = $this->serializer->unserialize($addOptions->getValue());
            }
            $item->setProductOrderOptions($options);
        */
        //    file_put_contents(BP . '/var/log/_data.log', date("[Y-m-d H:i:s] ") . print_r($item->getProduct()->getId(), true) . "\n", FILE_APPEND | LOCK_EX);
        }
        return $this;
    }
}
