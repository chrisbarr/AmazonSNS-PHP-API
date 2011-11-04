<?php


/**
 * Lightweight API interface with the Amazon Simple Notification Service
 * 
 * @author Chris Barr
 * @link http://aws.amazon.com/sns/
 * @version 0.2.0
 */
class AmazonSNS
{
	private $access_key = '';
	private $secret_key = '';
	
	private $protocol = 'https://'; // http is allowed
	private $endpoint = ''; // Defaults to US-EAST-1
	
	private $endpoints = array(
				'US-EAST-1' => 'sns.us-east-1.amazonaws.com',
				'US-WEST-1' => 'sns.us-west-1.amazonaws.com',
				'EU-WEST-1' => 'sns.eu-west-1.amazonaws.com',
				'AP-SE-1' => 'sns.ap-southeast-1.amazonaws.com',
				'AP-NE-1' => 'sns.ap-northeast-1.amazonaws.com'
			);
	
	
	/**
	 * Instantiate the object - set access_key and secret_key and set default region
	 * @param string $access_key [optional]
	 * @param string $secret_key [optional]
	 * @return void
	 */
	public function __construct($access_key = null, $secret_key = null)
	{
		if(!is_null($access_key)) $this->access_key = $access_key;
		if(!is_null($secret_key)) $this->secret_key = $secret_key;
		
		
		if(empty($this->access_key) || empty($this->secret_key))
		{
			throw new InvalidArgumentException('Must define Amazon access key and secret key');
		}
		
		$this->setRegion('US-EAST-1');
	}
	
	
	/**
	 * Set the SNS endpoint/region
	 * @param string $region Available regions - US-EAST-1, US-WEST-1, EU-WEST-1, AP-SE-1, AP-NE-1
	 * @return string
	 */
	public function setRegion($region)
	{
		if(!in_array($region, $this->endpoints))
		{
			throw new InvalidArgumentException('Region unrecognised');
		}
		
		return $this->endpoint = $this->endpoints[$region];
	}
	
	
	//
	// Public interface functions
	//
	
	
	/**
	 * Add permissions to a topic
	 * 
	 * Example:
	 * 	$AmazonSNS->addPermission('topic:arn:123', 'New Permission', array('987654321000' => 'Publish', '876543210000' => array('Publish', 'SetTopicAttributes')));
	 * 
	 * @param string $topicArn
	 * @param string $label Unique name of permissions
	 * @param array $permissions [optional] Array of permissions - member ID as keys, actions as values
	 * @return bool
	 */
	public function addPermission($topicArn, $label, $permissions = array())
	{
		// Add standard params as normal
		$params = array();
		$params['TopicArn'] = $topicArn;
		$params['Label'] = $label;
		
		
		// Compile permissions into separate sequential arrays
		$memberFlatArray = array();
		$permissionFlatArray = array();
		
		foreach($permissions as $member => $permission)
		{
			if(is_array($permission))
			{
				// Array of permissions
				foreach($permission as $singlePermission)
				{
					$memberFlatArray[] = $member;
					$permissionFlatArray[] = $singlePermission;
				}
			}
			else
			{
				// Just a single permission
				$memberFlatArray[] = $member;
				$permissionFlatArray[] = $permission;
			}
		}
		
		// Dummy check
		if(count($memberFlatArray) !== count($permissionFlatArray))
		{
			// Something went wrong
			throw new InvalidArgumentException('Mismatch of permissions to users');
		}
		
		// Finally add to params
		for($x = 1; $x <= count($memberFlatArray); $x++)
		{
			$params['ActionName.member.' . $x] = $permissionFlatArray[$x];
			$params['AWSAccountID.member.' . $x] = $memberFlatArray[$x];
		}
		
		// Finally send request
		$resultXml = $this->_request('AddPermission', $params);
		
		return true;
	}
	
	
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
		
		if(!is_null($authenticateOnUnsubscribe))
			$params['AuthenticateOnUnsubscribe'] = $authenticateOnUnsubscribe;
		
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
	
	
	/**
	 * Get the attributes of a topic like owner, ACL, display name
	 * @param string $topicArn
	 * @return array
	 */
	public function getTopicAttributes($topicArn)
	{
		$resultXml = $this->_request('GetTopicAttributes', array('TopicArn' => $topicArn));
		
		return $resultXml->GetTopicAttributeResult->Attributes;
	}
	
	
	/**
	 * List subscriptions that user is subscribed to
	 * @param string $nextToken [optional] Token to retrieve next page of results
	 * @return array
	 */
	public function listSubscriptions($nextToken = null)
	{
		$params = array();
		
		if(!is_null($nextToken))
			$params['NextToken'] = $nextToken;
		
		$resultXml = $this->_request('ListSubscriptions', $params);
		
		return $resultXml->ListSubscriptionsResult->Subscriptions;
	}
	
	
	/**
	 * List subscribers to a topic
	 * @param string $topicArn
	 * @param string $nextToken [optional] Token to retrieve next page of results
	 * @return array
	 */
	public function listSubscriptionsByTopic($topicArn, $nextToken = null)
	{
		$params = array('TopicArn' => $topicArn);
		
		if(!is_null($nextToken))
			$params['NextToken'] = $nextToken;
		
		$resultXml = $this->_request('ListSubscriptionsByTopic', $params);
		
		return $resultXml->ListSubscriptionsByTopicResult->Subscriptions;
	}
	
	
	/**
	 * List SNS topics
	 * @param string $nextToken [optional] Token to retrieve next page of results
	 * @return array
	 */
	public function listTopics($nextToken = null)
	{
		$params = array();
		
		if(!is_null($nextToken))
			$params['NextToken'] = $nextToken;
		
		$resultXml = $this->_request('ListTopics', $params);
		
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
	
	
	/**
	 * Remove a set of permissions indentified by topic and label that was used when creating permissions
	 * @param string $topicArn
	 * @param string $label
	 * @return bool
	 */
	public function removePermission($topicArn, $label)
	{
		$resultXml = $this->_request('RemovePermission', array
			(
				'Label' => $label
			)
		);
		
		return true;
	}
	
	
	/**
	 * Set a single attribute on a topic
	 * @param string $topicArn
	 * @param string $attrName
	 * @param mixed $attrValue
	 * @return bool
	 */
	public function setTopicAttributes($topicArn, $attrName, $attrValue)
	{
		$resultXml = $this->_request('SetTopicAttributes', array
			(
				'AttributeName' => $attrName,
				'AttributeValue' => $attrValue
			)
		);
		
		return true;
	}
	
	
	/**
	 * Subscribe to a topic
	 * @param string $topicArn
	 * @param string $protocol - http/https/email/email-json/sms/sqs
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
		$request = $this->protocol . $this->endpoint . '/?' . http_build_query(
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

// Exception thrown if Amazon returns an error
class SNSException extends Exception {}