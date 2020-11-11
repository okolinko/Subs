<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 3:42 PM
 */

namespace Toppik\Subscriptions\Ui\Component\Listing\Column\Profile;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Toppik\Subscriptions\Model\ProfileFactory;
use Zend\Json\Json;

abstract class AbstractColumn extends Column {

    /**
     * @var ProfileFactory
     */
    private $profileFactory;

    /**
     * PostActions constructor.
     * @param ProfileFactory $profileFactory
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ProfileFactory $profileFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components,
        array $data)
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->profileFactory = $profileFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        // @TODO: this should be refactored because it creates n x m number of models
        // where n is number of rows
        // m is number of columns in grid
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if(! isset($item['model'])) {
                    /* @var \Toppik\Subscriptions\Model\Profile $model */
                    $model = $this->profileFactory->create();
                    $model->setData($item);
                    $item['model'] = $model;
                }
            }
        }

        return $dataSource;
    }

}