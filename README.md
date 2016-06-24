# Amazon SNS PHP API

This API wrapper is a lightweight alternative to the official [Amazon aws-sdk-for-php](http://aws.amazon.com/sdkforphp) for access to Amazon SNS (Simple Notification Service) using PHP

Find out more about Amazon SNS here - http://aws.amazon.com/sns

To use this wrapper you must be using PHP5 with cURL, and have an [Amazon AWS account](http://aws.amazon.com)

## Basic Use
Install using [Composer](https://getcomposer.org/) on the command line:
```
$ composer require chrisbarr/amazon-sns-php-api
```

Or add it to your composer.json file:

```
{
	...
	"require": {
		"chrisbarr/amazon-sns-php-api": "~1.0"
	}
}
```

Example usage:

```php
<?php
require 'vendor/autoload.php';

// Create an instance
$AmazonSNS = new AmazonSNS(AMAZON_ACCESS_KEY_ID, AMAZON_SECRET_ACCESS_KEY);

// Create a Topic
$topicArn = $AmazonSNS->createTopic('My New SNS Topic');

// Set the Topic's Display Name (required)
$AmazonSNS->setTopicAttributes($topicArn, 'DisplayName', 'My SNS Topic Display Name');

// Subscribe to this topic
$AmazonSNS->subscribe($topicArn, 'email', 'example@github.com');

// And send a message to subscribers of this topic
$AmazonSNS->publish($topicArn, 'Hello, world!');
```

## API Methods
Available methods:

* `addPermission($topicArn, $label, $permissions)`
* `confirmSubscription($topicArn, $token)`
* `createTopic($name)`
* `deleteTopic($topicArn)`
* `getTopicAttributes($topicArn)`
* `listSubscriptions()`
* `listSubscriptionsByTopic($topicArn)`
* `listTopics()`
* `publish($topicArn, $message, $subject, $messageStructure)`
* `removePermission($topicArn, $label)`
* `setTopicAttributes($topicArn, $attrName, $attrValue)`
* `subscribe($topicArn, $protocol, $endpoint)`
* `unsubscribe($subscriptionArn)`
* `createPlatformEndpoint($platformApplicationArn, $token, $userData)`
* `deleteEndpoint($deviceArn)`
* `publishToEndpoint($deviceArn,$message)`

To set the API region (us-east-1, us-west-2, us-west-1, eu-west-1, etc):

* `setRegion($region)`

*The default API region is us-east-1*

## Further Example
Make sure to catch Exceptions where necessary:

```php
<?php
require 'vendor/autoload.php';

$AmazonSNS = new AmazonSNS(AMAZON_ACCESS_KEY_ID, AMAZON_SECRET_ACCESS_KEY);
$AmazonSNS->setRegion('eu-west-1');

try {
	$topics = $AmazonSNS->listTopics();
}
catch(SNSException $e) {
	// Amazon SNS returned an error
	echo 'SNS returned the error "' . $e->getMessage() . '" and code ' . $e->getCode();
}
catch(APIException $e) {
	// Problem with the API
	echo 'There was an unknown problem with the API, returned code ' . $e->getCode();
}
```
