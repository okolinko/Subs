<?php
namespace Toppik\Subscriptions\Logger;

class Logger extends \Monolog\Logger {
	
    public function __construct(
		array $handlers = [],
		array $processors = []
	) {
        parent::__construct('subscription', $handlers, $processors);
    }
	
}
