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
        self::init();
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

    public function remove($criteria=Array(),$justOne=False){
        return self::$collection->remove($criteria);
    }

    public static function init(){
        self::$connection = new Mongo(Kohana::$config->load('mandos.mongouri'));
        self::$db = self::$connection->selectDB(Kohana::$config->load('mandos.db'));
        $late_name = get_called_class();
                if(!$late_name::$collection_name){
                    self::$collection_name = get_called_class();
                }else{
                    self::$collection_name = $late_name::$collection_name;
                }
        self::$collection = self::$db->selectCollection(self::$collection_name);

        foreach($late_name::$indicies as $index){
            if(count($index)>1){
                $opts = array_splice($index, 1); 
            }else{
                $opts = Array();
            }
            self::$collection->ensureIndex($index,$opts);
        }

    }

    public static function find($criteria=Array(),$fields=Array()){
        self::init();
        $saved_items = self::$collection->find($criteria,$fields);
        if($saved_items->count()==0){
            return Array();
        }

        $class = get_called_class();
        $output_items = Array();
        foreach($saved_items as $item){
            $output = new $class();
            foreach($item as $key=>$val){
                $output->$key = $val;
            }
            $output_items[] = $output;
        }
        return $output_items;
    }

    public static function find_one($criteria=Array(),$fields=Array()){
        self::init();
        $object = self::$collection->findOne($criteria,$fields);
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
