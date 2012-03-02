<?php
class Mandos_Core extends Mandos_Dict{

    //This will be overridden in child classes to link to a collection
    protected static $collection_name = FALSE;

    //TODO: This can be overridden to determine the 'safe' behavior on inserts/updates
    protected static $safe = FALSE;

    //Don't let any child model utilize reserved names for new members
    private static $reserved_names = Array('save','destroy','create','init','items','get');

    public function __construct($initial_values=Array()){
        static::init();
        foreach($initial_values as $key=>$val){
            $this->$key = $val;
        }
    }

    public function save(){
        if(!$this->_id){
            $this->_id = new MongoId();
        }

        return static::$config['collection']->update(
                Array('_id'=>$this->_id),
                $this->items,
                Array(
                    'upsert'=>TRUE,
                    'safe'=>self::$safe
                    )
                );
    }

    public function destroy($justOne = False){
        if(!empty($this->items)){
            return static::$config['collection']->remove(Array('_id'=>$this->_id),Array('justOne'=>$justOne));            
        }else{
            throw new Exception('Cannot remove an uninstantiated model object from the mongo collection: no reference.');
        }
    }

    private final static function _remove($args=Array()){
        $criteria = (isset($args[0])) ? $args[0] : Array();
        $justOne = (isset($args[1])) ? $args[1] : Array();
        return static::$config['collection']->remove($criteria);
    }

    public static function init(){
        static::$config['connection'] = new Mongo(Kohana::$config->load('mandos.mongouri'));
        static::$config['db'] = static::$config['connection']->selectDB(Kohana::$config->load('mandos.db'));
        if(!array_key_exists('collection_name',static::$config)){
            $collection_name = get_called_class();
        }else{
            $collection_name = static::$config['collection_name'];
        }
        static::$config['collection'] = static::$config['db']->selectCollection($collection_name);

        if(array_key_exists('indicies', static::$config)){
            foreach(static::$config['indicies'] as $index){
                if(count($index)>1){
                    $opts = array_splice($index, 1); 
                }else{
                    $opts = Array();
                }
                static::$config['collection']->ensureIndex($index,$opts);
            }
        }

    }

    public static function __callStatic($name,$arguments){
        if(!array_key_exists('collection',static::$config)){
            static::init();
        }
        $name = '_'.$name;
        return static::$name($arguments);
    }

    private final static function _find($args=Array()){
        $criteria = (isset($args[0])) ? $args[0] : Array();
        $fields = (isset($args[1])) ? $args[1] : Array();

        $class = get_called_class();
        return new Mandos_Cursor(static::$config['connection'],
                                 static::$config['db']->__toString().'.'.static::$config['collection']->getName(),
                                 $class, 
                                 $criteria,
                                 $fields
                                 );
    }

    private final static function _collection(){
        return static::$config['collection'];
    }

    private final static function _db(){
        return static::$config['db'];
    }

    private final static function _find_one($args=Array()){
        $criteria = (isset($args[0])) ? $args[0] : Array();
        $fields = (isset($args[1])) ? $args[1] : Array();
        if(!array_key_exists('collection',static::$config)){
            static::init();
        }
        $object = static::$config['collection']->findOne($criteria,$fields);
        if(!$object){
            return False;
        }
        
        $class = get_called_class();

        $saved_object = new $class();
        foreach($object as $key=>$val){
            $saved_object->$key = $val;
        }
        return $saved_object;

    }

    final public function __set($key,$value){
        if(in_array($key,self::$reserved_names)){
            throw new Exception('Cannot assign instance property '.$key.' of '.get_class($this).': is a reserved word.');
        }
        parent::__set($key,$value);
    }

    final public function __get($key){
        if($key == 'collection'){
            return static::$config['collection'];
        }else if($key == 'db'){
            return static::$config['db'];
        }else{
            return parent::__get($key);
        }
    }

}
