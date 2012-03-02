<?
class Mandos_Cursor extends MongoCursor{

    private $class_linkage = '';

    public function __construct($connection, $ns, $class, $query=array(), $fields = array()){
        parent::__construct($connection,$ns,$query,$fields);
        $this->class_linkage = $class;
    }

    public function current(){
        $current = parent::current();
        if($current == NULL){ 
            return NULL; 
        }else{
            return new $this->class_linkage($current);
        }
    }
    


}
