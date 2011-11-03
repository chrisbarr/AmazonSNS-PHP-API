<?php


/**
 * API interface with the Amazon Simple Notification Service
 * @author Chris Barr
 */
class AmazonSNS
{
	private $access_key = '';
	private $secret_key = '';
	
	private $endpoint = 'sns.us-east-1.amazonaws.com'; // sns.us-west-1.amazonaws.com sns-eu-west-1.amazonaws.com
	
	
	
	public function __construct($access_key = null, $secret_key = null)
	{
		if(!is_null($access_key)) $this->access_key = $access_key;
		if(!is_null($secret_key)) $this->secret_key = $secret_key;
		
		
		if(empty($this->access_key) || empty($this->secret_key))
		{
			throw new Exception('Must define Amazon access key and secret key');
		}
	}
	
	
	
	//
	// Public interface functions
	//
	
	
	/**
	 * Confirm a subscription to a topic
	 * @param string $topicArn
	 * @param string $token
	 * @param bool $authenticateOnUnsubscribe [optional]
	 * @return string - SubscriptionARN
	 */
	public function confirmSubscription($topicArn, $token, $authenticateOnUnsubscribe = null)
	{
		$params = array
			(
				'TopicArn' => $topicArn,
				'Token' => $token
			);
		
		if(!is_null($authenticateOnUnsubscribe)) $params['AuthenticateOnUnsubscribe'] = $authenticateOnUnsubscribe;
		
		$resultXml = $this->_request('ConfirmSubscription', $params);
		
		return strval($resultXml->ConfirmSubscriptionResult->SubscriptionArn);
	}
	
	
	/**
	 * Create an SNS topic
	 * @param string $name
	 * @return string - TopicARN
	 */
	public function createTopic($name)
	{
		$resultXml = $this->_request('CreateTopic', array('Name' => $name));
		
		return strval($resultXml->CreateTopicResult->TopicArn);
	}
	
	
	/**
	 * Delete an SNS topic
	 * @param string $topicArn
	 * @return bool
	 */
	public function deleteTopic($topicArn)
	{
		$resultXml = $this->_request('DeleteTopic', array('TopicArn' => $topicArn));
		
		return true;
	}
	
	
	public function getTopicAttributes($topicArn)
	{
		
	}
	
	
	/**
	 * List subscriptions that user is subscribed to
	 * @param string $nextToken [optional]
	 * @return array
	 */
	public function listSubscriptions($nextToken = null)
	{
		
	}
	
	
	/**
	 * List subscribers to a topic
	 * @param string $topicArn
	 * @return array
	 */
	public function listSubscriptionsByTopic($topicArn)
	{
		$resultXml = $this->_request('ListSubscriptionsByTopic', array('TopicArn' => $topicArn));
		
		return $resultXml->ListSubscriptionsByTopicResult->Subscriptions;
	}
	
	
	/**
	 * List SNS topics
	 * @param string $nextToken [optional]
	 * @return array
	 */
	public function listTopics($nextToken = null)
	{
		$resultXml = $this->_request('ListTopics');
		
		return $resultXml->ListTopicsResult->Topics;
	}
	
	
	/**
	 * Publish a message to a topic
	 * @param string $topicArn
	 * @param string $message
	 * @param string $subject [optional]
	 * @return bool
	 */
	public function publish($topicArn, $message, $subject = '')
	{
		$resultXml = $this->_request('Publish', array
			(
				'TopicArn' => $topicArn,
				'Message' => $message,
				'Subject' => $subject
			)
		);
		
		return true;
	}
	
	
	public function setTopicAttributes($topicArn, $attrName, $attrValue)
	{
		
	}
	
	
	/**
	 * Subscribe to a topic
	 * @param string $topicArn
	 * @param string $protocol - http/https/email/email-json/sqs
	 * @param string $endpoint
	 * @return bool
	 */
	public function subscribe($topicArn, $protocol, $endpoint)
	{
		$resultXml = $this->_request('Subscribe', array
			(
				'TopicArn' => $topicArn,
				'Protocol' => $protocol,
				'Endpoint' => $endpoint
			)
		);
		
		return true;
	}
	
	
	/**
	 * Unsubscribe a user from a topic
	 * @param string $subscriptionArn
	 * @return bool
	 */
	public function unsubscribe($subscriptionArn)
	{
		$resultXml = $this->_request('Unsubscribe', array('SubscriptionArn' => $subscriptionArn));
		
		return true;
	}
	
	
	
	//
	// Private functions
	//
	
	
	private function _request($action, $params = array())
	{
		// Add in required params
		$params['Action'] = $action;
		$params['AWSAccessKeyId'] = $this->access_key;
		$params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
		$params['SignatureVersion'] = 2;
		$params['SignatureMethod'] = 'HmacSHA256';
		
		// Sort and encode into string
		uksort($params, 'strnatcmp');
		$queryString = '';
		foreach ($params as $key => $val)
		{
			$queryString .= "&{$key}=".rawurlencode($val);
		}
		$queryString = substr($queryString, 1);
		
		// Form request string
		$requestString = "GET\n"
			. $this->endpoint."\n"
			. "/\n"
			. $queryString;
		
		// Create signature - Version 2
		$params['Signature'] = base64_encode(
			hash_hmac('sha256', $requestString, $this->secret_key, true)
		);
		
		// Finally create request
		$request = 'http://'.$this->endpoint.'/?' . http_build_query(
			$params
		);
		
		// Instantiate cUrl and perform request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		
		// Close cUrl
		curl_close($ch);
		
		
		// Load XML reponse
		$xmlResponse = simplexml_load_string($output);
		
		// Check request ok and no error
		if($info['http_code'] == 200 && !isset($xmlResponse->Error))
		{
			// And return
			return $xmlResponse;
		}
		else
		{
			// There was a problem
			throw new APIException('There was a problem with this request - '.$info['http_code'].' response returned - '.$xmlResponse->Error->Code.' given - '.$xmlResponse->Error->Message);
		}
	}
}

// Exception thrown if there's a problem with the API
class APIException extends Exception {}
