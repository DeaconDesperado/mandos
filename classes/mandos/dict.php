<?
class Mandos_Dict implements ArrayAccess{

    protected $items = array();

    public function offsetExists($key){
        return isset($this->items[$key]);
    }

    public function offsetGet($key){
        if($this->offsetExists($key)){
            return $this->items[$key];
        }else{
            //throw new Exception('Instance has no attribute '.$key);
            return False;
        }
    }

    public function offsetSet($key,$value){
        $this->items[$key] = $value;
    }   

    public function offsetUnset($key){
        unset($this->items[$key]);
    }

    public function __get($key){
        return $this->offsetGet($key);
    }

    public function __set($key,$value){
        $this->offsetSet($key,$value);
    }

    public function __isset($key){
        return $this->offsetExists($key);
    }

    public function __unset($key){
        $this->offsetUnset($key);
    }
    
}
