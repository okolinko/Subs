<?php
namespace Toppik\Subscriptions\Ui\Component\Listing\Column\Profile;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class SuspendedActions extends Column {

    const PROFILE_URL_PATH_VIEW = 'subscriptions/profiles/view';
    const PROFILE_URL_PATH_ACTIVATE = 'subscriptions/profiles/activate';
    
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
        array $data
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                
                $item[$name]['view'] = [
                    'href' => $this->urlBuilder->getUrl(self::PROFILE_URL_PATH_VIEW, ['profile_id' => $item['profile_id']]),
                    'label' => __('View'),
                    'hidden' => false,
                    'target' => '_blank'
                ];
                
                $item[$name]['activate'] = [
                    'href' => $this->urlBuilder->getUrl(self::PROFILE_URL_PATH_ACTIVATE, ['profile_id' => $item['profile_id']]),
                    'label' => __('Activate'),
                    'hidden' => false,
                    'target' => '_blank'
                ];
                
                $item[$name]['loggin'] = [
                    'href' => $this->urlBuilder->getUrl('loginascustomer/login/login', ['customer_id' => $item['customer_id']]),
                    'label' => __('Loggin As Customer'),
                    'hidden' => false,
                    'target' => '_blank'
                ];
                
                $item[$name]['loggin_edit'] = [
                    'href' => $this->urlBuilder->getUrl('loginascustomer/login/login', ['customer_id' => $item['customer_id'], 'page' => 'profile', 'id' => $item['profile_id']]),
                    'label' => __('Loggin As Customer And Edit'),
                    'hidden' => false,
                    'target' => '_blank'
                ];
            }
        }
        
        return $dataSource;
    }
    
}
