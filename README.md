* No longer actively maintained *

# Mandos Mongo ODM

+ Requires php 5.3 and designed for Kohana 3+

Mandos is an intentionally lightweight Object Document Mapper for MongoDB and Kohana 3.

Mandos is designed to be as lightweight as possible and eschews some of the more complicated features
in traditional ORMs such as Validation and relationship manangement in favor of maintaining API congruence
with the original MongoDB Drivers.

Think of mandos as an interface that will allow you to attach instance methods directly to the objects you save in your
mongo collections, without having to relearn any new methods that differ from the Mongo driver.

## Getting started

In order to start attaching methods to your Mongo objects, all you need to do is define an application model that will contain
the methods and link it to a collection using the `$config` property.

The `$config` property of your new model should be an associative array defining the collection name 
as well as any indexes that will be ensured on the collection.  The keys are as follows.

+ `collection_name` Use this to set the name of the collection in MongoDB that will contain all your instances.  If you do not 
define this key, the interface will attempt to pluralize your classname and use this as the collection name.

+ `indicies` This key defines indicies to be ensured on your collection.  The definition input is the same as the core Mongo driver's 
`ensureIndex` function, and should be passed as one array (key name as the first element with any options as the following elements.)

An example model:

	class Place extends Mandos{
		static $config = array(
			'collection_name'=>'PlacesToVisit',
			'indicies'=>array(
				array('loc','geo','2d'),
				array('keywords')
			);
		)

Mandos intentionally preserves the basic approach of the 10gen driver methods wherever possible.  The main difference is that rather
than retriving associative arrays representing your data, you will instead get your models directly as output.  The original `find` and `findOne`
methods will return either a cursor containing your models or a single model respectively.

	$chicago = Place::findOne(array('city'=>'Chicago','state'=>'IL'));
	//Assuming we have some methods in the Place class, we can call them directly on this return
	$chicago->visit();

Note that find returns a cursor that is compatible with all the original MongoCursor calls

	//find me all the cities alphabetized by name, limit 30 results
	$places = Place::find()->sort('city'=>1)->limit(30);
 
Keys representing properties in your mongo objects can be fetched using either property accessor or key accessor syntax:

	//These two lines are equivalent
	$chicago->city;
	$chicago['city'];

Mandos models can be saved back to their collections by calling the `save()` instance method:

	$chicago->temperature = 55;
	$chicago->save();

Calling save will trigger an upsert, so new models can be created just by calling the model constructor and then calling `save` on the new instance.
If no `_id` property is assigned, the interface will preserve the default driver functionality and generate a new `MongoId` for the object.

Likewise, an object can be deleted from the collection by calling the `destroy()` method
	
	$chicago->destroy()

Simple relationships can be established by defining methods that will chain call `find` or `findOne`.  This maps nicely with the Mongo
philosophy of application defined relations between data models (rather than something more robust in the data tier like you'd find in an RDBMS)

	class Event extends Mandos{
		static $config=array(
			'collection_name'=>'Events'
		);
		
		public function __construct($inital_vals){
			parent::__construct($inital_vals);
			//Let's assume all the event objects have ids linking them to venues in MongoDB.  We
			//could do a construct-time fetch that will junction the venue data right in at fetch time.
			$this->Venue = Venue::findOne(array('_id'=>$this->VenueID));
		}
	}

So long as your model's constructor maintains chainability back to the parent methods (by taking inital mapping of values for upserts
and then calling the parent constructor with this mapping) your model will remain in the API.

In case you need direct access to any model's collection or database, both are available through the static methods `collection()` and `db()` respectively.

	//This will return the MongoCollection instance representing this Model's collection
	$collection = Event::collection();

Credit for the approach goes to <a href="https://github.com/slacy">Steve Lacey's</a> <a href="https://github.com/slacy/minimongo">minimongo</a>, which
provides similar functionality for Python.
