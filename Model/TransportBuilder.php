<?php
namespace Toppik\Subscriptions\Model;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder {
	
    /**
     * Reset object state
     *
     * @return $this
     */
    public function reset() {
        return parent::reset();
    }
    
    /**
     * @param string $filename
     * @param string $content
     * @return $this
     */
    public function attachFile($filename, $content) {
        /*if(!empty($filename) && !empty($content) && is_string($filename) && is_string($content)) {
            $this->message
                ->createAttachment(
                    $content,
                    \Zend_Mime::TYPE_OCTETSTREAM,
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    \Zend_Mime::ENCODING_BASE64,
                    $filename
                );
        }*/
		
        return $this;
    }
	
}
