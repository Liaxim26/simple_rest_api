<?php
	/**
	 * 
	 */
	class RequestProcessingException extends Exception
	{
		protected $httpCode;
		protected $payload;
		
		function __construct($code, $message, $payload = [])
		{
			parent::__construct($message);
			$this->httpCode = $code;
			$this->payload = $payload;
		}

		function getHttpCode() {
			return $this->httpCode;
		}

		function getPayload() {
			return $this->payload;
		}
	}
?>