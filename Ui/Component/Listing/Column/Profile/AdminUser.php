<?php
namespace Toppik\Subscriptions\Ui\Component\Listing\Column\Profile;

use Magento\Backend\Model\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class AdminUser extends Column
{
    protected $_resource;
    protected $_scopeConfig;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $userFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\User\Model\UserFactory $userFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->userFactory = $userFactory;
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
                if (isset($item[$this->getData('name')]) && (int) $item[$this->getData('name')] > 0) {
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'user_id';

                    $_userId = $item[$this->getData('name')];

                    $_user = $this->userFactory->create()->load($_userId);

                    if($_user && $_user->getId()){
                        $item[$this->getData('name')] = '<a href=" ' . $this->urlBuilder->getUrl(
                                    $viewUrlPath,
                                    [
                                        $urlEntityParamName => $item[$this->getData('name')]
                                    ]
                                ) . '" target="_blank">' . $_user->getFirstName() . ' ' . $_user->getLastName() . '</a>';
                    } else {
                        $item[$this->getData('name')] = __('User was removed');
                    }
                } else {
                    $item[$this->getData('name')] = __('No');
                }
            }
        }
        return $dataSource;
    }
}