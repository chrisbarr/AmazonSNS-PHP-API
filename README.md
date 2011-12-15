# Amazon SNS PHP API v0.5.1 Documentation #
This API wrapper is a lightweight alternative to the official [Amazon aws-sdk-for-php](http://aws.amazon.com/sdkforphp) for access to Amazon SNS (Simple Notification Service) using PHP

Find out more about Amazon SNS here - http://aws.amazon.com/sns

To use this wrapper you must be using PHP5 with cURL, and have an [Amazon AWS account](http://aws.amazon.com)

## Basic Use ##
Download the latest version: https://github.com/chrisbarr/AmazonSNS-PHP-API/tarball/master

Include the class on your page:

	include('lib/amazonsns.class.php');

Create a connection to the API:

	$AmazonSNS = new AmazonSNS(AMAZON_ACCESS_KEY_ID, AMAZON_SECRET_ACCESS_KEY);

Create a Topic:

	$topicArn = $AmazonSNS->createTopic('My New SNS Topic');

Subscribe to this topic:

	$AmazonSNS->subscribe($topicArn, 'email', 'example@github.com');

And send a message to subscribers of this topic:

	$AmazonSNS->publish($topicArn, 'Hello, world!');

## API Methods ##
Available methods:

* `addPermission($topicArn, $label, $permissions)`
* `confirmSubscription($topicArn, $token)`
* `createTopic($name)`
* `deleteTopic($topicArn)`
* `getTopicAttributes($topicArn)`
* `listSubscriptions()`
* `listSubscriptionsByTopic($topicArn)`
* `listTopics()`
* `publish($topicArn, $message)`
* `removePermission($topicArn, $label)`
* `setTopicAttributes($topicArn, $attrName, $attrValue)`
* `subscribe($topicArn, $protocol, $endpoint)`
* `unsubscribe($subscriptionArn)`

To set the API region (US-EAST-1, US-WEST-1, EU-WEST-1, AP-SE-1, AP-NE-1 or SA-EAST-1):

* `setRegion($region)`

*The default API region is US-EAST-1*

## Advanced Use ##
A more complex example demonstrating catching Exceptions:

	<?php
	include('lib/amazonsns.class.php');
	$AmazonSNS = new AmazonSNS(AMAZON_ACCESS_KEY_ID, AMAZON_SECRET_ACCESS_KEY);
	$AmazonSNS->setRegion('EU-WEST-1');
	
	try
	{
		$topics = $AmazonSNS->listTopics();
	}
	catch(SNSException $e)
	{
		// Amazon SNS returned an error
		echo 'SNS returned the error "' . $e->getMessage() . '" and code ' . $e->getCode();
	}
	catch(APIException $e)
	{
		// Problem with the API
		echo 'There was an unknown problem with the API, returned code ' . $e->getCode();
	}
