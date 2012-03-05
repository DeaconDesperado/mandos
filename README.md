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
