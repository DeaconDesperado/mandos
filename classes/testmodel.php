<?php
class Testmodel extends Mandos{
    static $config=array(
        'collection_name'=>'Testmodels'    
    );

    public function getAllLionhearts(){
        return Lionheart::find(array('key'=>$this->lh_key));
    }
}
