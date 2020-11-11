<?php
namespace Toppik\Subscriptions\Ui\Component\Listing\Column\Profile;

class Customer extends \Magento\Ui\Component\Listing\Columns\Column {
    
    /**
     * @var Url
     */
    private $url;
    
    public function __construct(
        \Magento\Backend\Model\Url $url,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components,
        array $data
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->url = $url;
    }
    
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                
                if(empty($item['customer_id']) || empty($item['customer_name'])) {
                    $item[$name] = '';
                } else {
                    $item[$name] = '<a target="_blank" href="' . $this->url->getUrl('customer/index/edit', ['id' => $item['customer_id']]) . '">' . $item['customer_name'] . '</a>';
                }
            }
        }
        
        return $dataSource;
    }
    
}
