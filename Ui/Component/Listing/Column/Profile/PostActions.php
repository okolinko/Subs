<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 3:42 PM
 */

namespace Toppik\Subscriptions\Ui\Component\Listing\Column\Profile;


use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class PostActions extends Column {

    const PROFILE_URL_PATH_VIEW = 'subscriptions/profiles/view';
    const PROFILE_URL_PATH_CREATE_ORDER = 'subscriptions/profiles/createOrder';

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
                    'href' => $this->urlBuilder->getUrl(self::PROFILE_URL_PATH_VIEW, ['profile_id' => $item['profile_id']]),
                    'label' => __('View'),
                    'hidden' => false,
                ];

                $item[$name]['order'] = [
                    'href' => $this->urlBuilder->getUrl(self::PROFILE_URL_PATH_CREATE_ORDER, ['profile_id' => $item['profile_id']]),
                    'label' => __('Create Order'),
                    'confirm' => [
                        'title' => __('New Order for Profile'),
                        'message' => __('Are you sure you wan\'t to create order for profile?')
                    ],
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }

}