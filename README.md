# Mandos Mongo ODM

Mandos is an intentionally lightweight Object Document Mapper for MongoDB and Kohana 3.

Mandos is designed to be as lightweight as possible and eschews some of the more complicated features
in traditional ORMs such as Validation and relationship manangement in favor of maintaining API congruence
with the original MongoDB Drivers.

Think of mandos as an interface that will allow you to attach instance methods directly to the objects you save in your
mongo collections.

## Getting started

In order to start attaching methods to your Mongo objects, all you need to do is define an application model that will contain
the methods and link it to a collection using the `$config` property.

The `$config` property of your new model should be an associative array that can define the collection name that will contain the
instances as well as any indexes that will be ensured on the collection.  The keys are as follows.

+ `$collection_name` Use this to set the name of the collection in MongoDB that will contain all your instances.  If you do not 
define this key, the interface will attempt to pluralize your classname and use this as the collection name.

+ `$indicies` This key defines indicies to be ensured on your collection.  The definition input is the same as the core Mongo driver's 
`ensureIndex` function, and should be passed as one array (key name as the first element with any options as the following elements.)

