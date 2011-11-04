# Amazon SNS PHP API v0.2.0 Documentation #
This API wrapper is a lightweight alternative to the official [Amazon aws-sdk-for-php](http://aws.amazon.com/sdkforphp) for access to Amazon SNS (Simple Notification Service) using PHP

Find out more about Amazon SNS here - http://aws.amazon.com/sns

## How To Use ##
Include the library on your page:

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

To set the API region (US-EAST-1, US-WEST-1, EU-WEST-1, AP-SE-1 or AP-NE-1):

* `setRegion($region)`

*The default API region is US-EAST-1*