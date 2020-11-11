<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 3:42 PM
 */

namespace Toppik\Subscriptions\Ui\Component\Listing\Column\Settings\Period;


use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class PostActions extends Column {

    const PERIOD_URL_PATH_EDIT = 'subscriptions/periods/edit';
    const PERIOD_URL_PATH_DELETE = 'subscriptions/periods/delete';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * PostActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components,
        array $data)
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                $item[$name]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(self::PERIOD_URL_PATH_EDIT, ['period_id' => $item['period_id']]),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];
                
                $item[$name]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(self::PERIOD_URL_PATH_DELETE, ['period_id' => $item['period_id']]),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete "${ $.$data.title }"'),
                        'message' => __('Are you sure you wan\'t to delete a "${ $.$data.title }" record?')
                    ],
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }

}