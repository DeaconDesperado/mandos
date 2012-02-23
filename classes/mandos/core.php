<?php
class Mandos_Core extends Mandos_Dict{

    private static $connection;
    private static $db;
    protected static $collection;

    protected static $collection_name = FALSE;
    protected static $safe = FALSE;

    private static $reserved_names = Array('save','destroy','create','init','items','get');

    protected static $indicies = Array();


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

        return self::$collection->update(
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
            return self::$collection->remove(Array('_id'=>$this->_id),Array('justOne'=>$justOne));            
        }else{
            throw new Exception('Cannot remove an uninstantiated model object from the mongo collection: no reference.');
        }
    }

    public static function _remove($args=Array()){
        $criteria = (isset($args[0])) ? $args[0] : Array();
        $justOne = (isset($args[1])) ? $args[1] : Array();
        return self::$collection->remove($criteria);
    }

    public static function init(){
        static::$connection = new Mongo(Kohana::$config->load('mandos.mongouri'));
        static::$db = static::$connection->selectDB(Kohana::$config->load('mandos.db'));
        if(!static::$collection_name){
            static::$collection_name = static::who();
        }else{
            static::$collection_name = static::$collection_name;
        }
        static::$collection = static::$db->selectCollection(static::$collection_name);

        foreach(static::$indicies as $index){
            if(count($index)>1){
                $opts = array_splice($index, 1); 
            }else{
                $opts = Array();
            }
            static::$collection->ensureIndex($index,$opts);
        }

    }

    public static function __callStatic($name,$arguments){
        static::init();
        $name = '_'.$name;
        return static::$name($arguments);
    }

    public static function _find($args=Array()){
        $criteria = (isset($args[0])) ? $args[0] : Array();
        $fields = (isset($args[1])) ? $args[1] : Array();
        static::init();
        $saved_items = static::$collection->find($criteria,$fields);
        if($saved_items->count()==0){
            return Array();
        }

        $class = get_called_class();
        $output_items = Array();
        foreach($saved_items as $item){
            $output = new $class($item);
            $output_items[] = $output;
        }
        return $output_items;
    }

    public static function _find_one($args=Array()){
        $criteria = (isset($args[0])) ? $args[0] : Array();
        $fields = (isset($args[1])) ? $args[1] : Array();

        static::init();
        $object = static::$collection->findOne($criteria,$fields);
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

}
