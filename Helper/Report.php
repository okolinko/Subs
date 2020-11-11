<?php
namespace Toppik\Subscriptions\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Toppik\Subscriptions\Logger\FileHandlerFactory;
use Toppik\Subscriptions\Logger\LoggerFactory;
use Toppik\Subscriptions\Logger\Logger;

class Report {
	
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
	
    /**
     * @var Logger
     */
    private $logger;
	
    /**
     * @var DateTime
     */
    protected $dateTime;
	
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
	
    /**
     * @var \Toppik\Subscriptions\Model\TransportBuilderFactory
     */
    protected $transportBuilderFactory;
    
    protected $customerFactory;
    
    /**
     * ExportOrder constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerFactory $loggerFactory
     * @param FileHandlerFactory $fileHandlerFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerFactory $loggerFactory,
        FileHandlerFactory $fileHandlerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Toppik\Subscriptions\Model\TransportBuilder $transportBuilder,
        \Toppik\Subscriptions\Model\TransportBuilderFactory $transportBuilderFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $loggerFactory->create();
        $this->dateTime = $dateTime;
        $this->transportBuilder = $transportBuilder;
        $this->transportBuilderFactory = $transportBuilderFactory;
        $this->customerFactory = $customerFactory;
        
        /* @var FileHandler $fileHandler */
        $fileHandler = $fileHandlerFactory->create([
            'filename' => '/var/log/' . $this->getLogFile(),
        ]);
		
        $this->logger->pushHandler($fileHandler);
    }
	
    /**
     * @return bool
     */
    public function isDRTVEnabled() {
        return !! $this->scopeConfig->getValue('drtv_settings/general_options/enabled');
    }
	
    /**
     * @return string
     */
    public function getTransactionDeclineTimeout() {
        return (int) $this->scopeConfig->getValue('subscriptions_settings/general_options/transaction_decline_timeout');
    }
	
    /**
     * @return string
     */
    public function getMaxSuspendsAllowed() {
        return (int) $this->scopeConfig->getValue('subscriptions_settings/general_options/max_suspends_allowed');
    }
	
    /**
     * @return string
     */
    public function getSuspendedTemporarilyNotificationDays() {
        return (int) $this->scopeConfig->getValue('subscriptions_settings/general_options/suspended_temporarily_notification_days');
    }
	
    /**
     * @return string
     */
    public function getSuspendedTemporarilyNotificationEmailTemplate() {
        return $this->scopeConfig->getValue('subscriptions_settings/general_options/suspended_temporarily_notification_email_template');
    }
	
    /**
     * @return string[]
     */
    public function getEmails() {
        return explode(',', (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/notification_emails'));
    }
	
    /**
     * @return string
     */
    public function getSuspendAdminEmail() {
        return (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/suspend_admin_email');
    }
	
    /**
     * @return string
     */
    public function getEmailTemplateSuspendCustomer() {
        return (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/suspend_email_template_customer');
    }
	
    /**
     * @return string
     */
    public function getEmailTemplateSuspendAdmin() {
        return (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/suspend_email_template_admin');
    }
	
    /**
     * @return string
     */
    public function getEmailTemplateSuspendOosCustomer() {
        return (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/suspend_email_template_oos_customer');
    }
	
    /**
     * @return string
     */
    public function getEmailTemplateSuspendOosAdmin() {
        return (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/suspend_email_template_oos_admin');
    }
	
    /**
     * @return string
     */
    public function getEmailTemplateChangeNextDate($storeId) {
        return (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/next_order_date_change',
	        \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
	
    /**
     * @return string
     */
    public function getUpcomingOrderMinutes($storeId) {
        return (int) $this->scopeConfig->getValue('subscriptions_settings/general_options/upcoming_order_minutes',
	        \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
	
    /**
     * @return string
     */
    public function getUpcomingOrderEmailTemplate($storeId) {
        return (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/upcoming_order_email_template',
	        \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
	
    /**
     * @return string
     */
    public function getNewSubscriptionEmailTemplate($storeId) {
        return (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/new_subscription_email_template',
	        \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
	
    /**
     * @return bool
     */
    public function getIsSTSEnabled() {
        return (bool) $this->scopeConfig->getValue('subscriptions_settings/save_the_sale/enabled');
    }
	
    /**
     * @return bool
     */
    public function getIsSingleMode() {
        return (bool) $this->scopeConfig->getValue('subscriptions_settings/save_the_sale/single_mode');
    }
	
    /**
     * @return int
     */
    public function getMaxOnetimePoints() {
        return (int) $this->scopeConfig->getValue('subscriptions_settings/save_the_sale/max_onetime_points');
    }
	
    /**
     * @return int
     */
    public function getMaxLifetimePoints() {
        return (int) $this->scopeConfig->getValue('subscriptions_settings/save_the_sale/max_lifetime_points');
    }
    
    /**
     * @return int
     */
    public function getSaveRoleId() {
        return (int) $this->scopeConfig->getValue('subscriptions_settings/save_the_sale/role_id');
    }
    
    /**
     * @return string
     */
    public function getSaveCancelEmailTemplate() {
        return $this->scopeConfig->getValue('subscriptions_settings/save_the_sale/cancel_email_template');
    }
	
    /**
     * @return string
     */
    public function getSaveProductGiftEmailTemplate() {
        return $this->scopeConfig->getValue('subscriptions_settings/save_the_sale/product_gift_email_template');
    }
	
    /**
     * @return string
     */
    public function getSaveCouponGiftEmailTemplate() {
        return $this->scopeConfig->getValue('subscriptions_settings/save_the_sale/coupon_gift_email_template');
    }
	
    /**
     * @return string[]
     */
    public function getTritonAdminID() {
        return (int) $this->scopeConfig->getValue('microsite/triton/admin_id');
    }
	
    /**
     * @return bool
     */
    public function isLogEnabled() {
        return !! $this->scopeConfig->getValue('subscriptions_settings/general_options/log');
    }
	
    /**
     * @return string
     */
    public function getLogFile() {
        return (string) $this->scopeConfig->getValue('subscriptions_settings/general_options/log_file');
    }
	
    /**
     * @param string $message
     * @param array $context
     * @param int $level
     */
    public function log($message, array $context = [], $level = Logger::DEBUG) {
        if($this->isLogEnabled()) {
            $this->logger->addRecord($level, $message, $context);
        }
    }
	
    public function send($data, $message, $headers = null) {
		$headers 	= $headers ? $headers : array('Subscription ID', 'Next Order Date', 'First Name', 'Last Name', 'Email', 'Phone', 'Total', 'Error');
		$rows 		= $this->_generateRows($data, $headers);
		$csv 		= $this->_generateFile($rows, $headers);
		
		$this->transportBuilder->reset();
		
		$this->transportBuilder
			->setTemplateIdentifier('subscription_report')
			->setTemplateOptions([
				'area' => 'frontend',
				'store' => 0,
			])
			->setTemplateVars([
				'now' => $this->dateTime->gmtDate('Y-m-d H:i:s'),
				'subject' => 'Toppik Subscription Error',
				'message' => $message
			])
			->attachFile(
				'subscription_report' . $this->dateTime->gmtDate('Y-m-d') . '.csv',
				$csv
			)
			->setFrom([
				'email' => 'notification@toppik.com',
				'name' => 'Toppik System',
			])
			->addTo($this->getEmails(), 'Toppik System Support')
			->getTransport()
			->sendMessage();
		
		$this->transportBuilder->reset();
		
		$this->log('Sent email with report');
    }
	
    public function sendSuspendNotifications($profile, $message = null) {
        $product    = $profile->getSubscriptionProduct();
        $customer   = $this->customerFactory->create();
        
        $customer->load($profile->getCustomerId());
        
        $vars = array(
            'profile'   => $profile,
            'product'   => $product,
            'customer'  => $customer
        );
        
        if($profile->getErrorCode() == \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_STOCK) {
            $this->sendEmail($this->getEmailTemplateSuspendOosCustomer(), $customer->getEmail(), $profile->getStoreId(), $vars);
            $this->sendEmail($this->getEmailTemplateSuspendOosAdmin(), $this->getEmails(), $profile->getStoreId(), $vars);
        } else {
            $this->sendEmail($this->getEmailTemplateSuspendCustomer(), $customer->getEmail(), $profile->getStoreId(), $vars);
            $this->sendEmail($this->getEmailTemplateSuspendAdmin(), $this->getEmails(), $profile->getStoreId(), $vars);
        }
    }
	
    public function sendUpcomingEmail($profile) {
        $product    = $profile->getSubscriptionProduct();
        $customer   = $this->customerFactory->create();
        
        $customer->load($profile->getCustomerId());
        
        $vars = array(
            'profile'       => $profile,
            'product'       => $product,
            'customer'      => $customer,
            'profile_id'    => $profile->getId(),
            'customer_name' => $customer->getName(),
            'next_order_at' => date('F j, Y, g:i a', strtotime($profile->getNextOrderAt()))
        );
        
        $this->sendEmail($this->getUpcomingOrderEmailTemplate($profile->getStoreId()), $customer->getEmail(), $profile->getStoreId(), $vars);
        $this->log(sprintf('Sent upcoming email to "%s" for profile ID %s', $customer->getEmail(), $profile->getId()));
    }
	
    public function sendNewSubscriptionEmail($profile, $order) {
        $product    = $profile->getSubscriptionProduct();
        $customer   = $this->customerFactory->create();
        
        $customer->load($profile->getCustomerId());
        
        $vars = array(
            'profile'       => $profile,
            'product'       => $product,
            'customer'      => $customer,
            'order_total'   => strip_tags($order->formatPrice($order->getGrandTotal(), false)),
            'profile_id'    => $profile->getId(),
            'customer_name' => $customer->getName(),
            'product_name'  => $product->getName(),
            'items_qty'     => (int) $profile->getItemsQty(),
            'frequency'     => $profile->getFrequencyTitle(),
            'next_order_at' => date('F j, Y, g:i a', strtotime($profile->getNextOrderAt()))
        );
        
        $this->sendEmail($this->getNewSubscriptionEmailTemplate($profile->getStoreId()), $customer->getEmail(), $profile->getStoreId(), $vars);
        $this->log(sprintf('Sent new subscription email to "%s" for profile ID %s', $customer->getEmail(), $profile->getId()));
    }
	
    public function sendEmail($template, $email, $storeId, $vars) {
        $this->transportBuilderFactory->create()
			->setTemplateIdentifier($template)
			->setTemplateOptions([
				'area' => 'frontend',
				'store' => $storeId,
			])
			->setTemplateVars($vars)
			->setFrom([
				'email' => 'service@waterpik-upgrade-235.huntersconsult.com',
				'name' => 'Water Pik Inc.',
			])
			->addTo($email)
			->getTransport()
			->sendMessage();
    }
	
	protected function _generateRows($collection, $headers) {
		$rows = array();
		
        foreach($collection as $_item) {
			$values = array();
			
			foreach($headers as $_header) {
				$values[$_header] = isset($_item[$_header]) ? $_item[$_header] : '';
			}
			
			$rows[] = $values;
        }
		
		return $rows;
	}
	
    protected function _generateFile($rows, $headers) {
        $fd = fopen('php://temp/maxmemory:'.(1024 * 1024 * 10)/*10MB*/, 'w');
		
        fputcsv($fd, $headers);
		
        foreach($rows as $row) {
			$values = array();
			
			foreach($headers as $_header) {
				$values[] = isset($row[$_header]) ? $row[$_header] : '';
			}
			
            fputcsv($fd, $values);
        }
		
        rewind($fd);
		
        $csv = stream_get_contents($fd);
		
        fclose($fd);
		
        return $csv;
    }
	
}
