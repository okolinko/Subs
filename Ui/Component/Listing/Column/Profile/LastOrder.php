<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/30/16
 * Time: 7:25 PM
 */

namespace Toppik\Subscriptions\Ui\Component\Listing\Column\Profile;

use Magento\Framework\Url;
use Toppik\Subscriptions\Model\ProfileFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class LastOrder extends AbstractColumn
{

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        \Magento\Backend\Model\Url $url,
        ProfileFactory $profileFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components,
        array $data)
    {
        $this->url = $url;
        parent::__construct($profileFactory, $context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if(empty($item['order_increment_id'])) {
                    $item[$name] = '';
                } else {
                    $item[$name] = '<a target="_blank" href="' . $this->url->getUrl('sales/order/view', ['order_id' => $item['last_order_id']]) . '">' . $item['order_increment_id'] . '</a>';
                }
            }
        }

        return $dataSource;
    }
}