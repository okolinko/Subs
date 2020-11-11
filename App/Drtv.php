<?php
namespace Toppik\Subscriptions\App;

class Drtv implements \Magento\Framework\AppInterface {
	
    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;
	
    /**
     * @var \Magento\Framework\App\Console\Request
     */
    protected $_request;
	
    /**
     * @var \Magento\Framework\App\Console\Response
     */
    protected $_response;
	
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
	
    /**
     * Inject dependencies
     *
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\App\Console\Request $request
     * @param \Magento\Framework\App\Console\Response $response
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $parameters
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Console\Request $request,
        \Magento\Framework\App\Console\Response $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $parameters = []
    ) {
        $this->_state = $state;
        $this->_request = $request;
        $this->_request->setParams($parameters);
        $this->_response = $response;
        $this->objectManager = $objectManager;
    }
	
    /**
     * Run application
     *
     * @return ResponseInterface
     */
    public function launch() {
        $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        $configLoader = $this->objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
        $this->objectManager->configure($configLoader->load(\Magento\Framework\App\Area::AREA_CRONTAB));
		
		$cron = $this->objectManager->get('Toppik\Subscriptions\Cron\ProcessDrtvCs');
		$cron->execute();
		
        $this->_response->setCode(0);
        return $this->_response;
    }
	
    /**
     * {@inheritdoc}
     */
    public function catchException(\Magento\Framework\App\Bootstrap $bootstrap, \Exception $exception) {
        return false;
    }
	
}
