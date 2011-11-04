# Amazon SNS PHP API v0.2.0 Documentation #

This API wrapper is a lightweight alternative to the official [Amazon aws-sdk-for-php](http://aws.amazon.com/sdkforphp) for access to Amazon SNS (Simple Notification Service) using PHP

## Examples ##

Create a connection to the API:

	$AmazonSNS = new AmazonSNS(AMAZON_ACCESS_KEY_ID, AMAZON_SECRET_ACCESS_KEY);

Create a Topic:

	$topicArn = $AmazonSNS->createTopic('My New SNS Topic');

Subscribe to this topic:

	$AmazonSNS->subscribe($topicArn, 'email', 'example@github.com');

And send a message to subscribers of this topic:

	$AmazonSNS->publish($topicArn, 'Hello, world!');
