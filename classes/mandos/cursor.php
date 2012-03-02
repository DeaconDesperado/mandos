<?
class Mandos_Cursor implements Iterator{

    private $mongo_cursor; 
    private $empty = False;
    private $class_conversion;

    private $entities = array();
    private $position;

    public function __construct($mongo_cursor,$class_conversion){
        $this->mongo_cursor = $mongo_cursor;
        if($this->mongo_cursor->count() == 0){
            $this->empty = True;
        }
        $this->class_conversion = $class_conversion;
        $this->reindex();
        $this->position = 0;
    }

    private function reindex(){
        $this->entities = array();
        foreach($this->mongo_cursor as $entity){
            $this->entities[] = new $this->class_conversion($entity); 
        }
    }

    public function current(){
        return $this->entities[$this->position];
    }

    public function key(){
        return $this->position;
    }

    public function next(){
        ++$this->position;
    }

    public function rewind(){
        $this->position = 0;
    }

    public function valid(){
        return isset($this->entities[$this->position]);
    }

    public function __call($name, $arguments){
        $this->mongo_cursor->reset();
        $ret = call_user_func_array(array($this->mongo_cursor,$name), $arguments);
        $this->reindex();
        return $ret;
    }

}
