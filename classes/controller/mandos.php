<?
class Controller_Mandos extends Controller{

    public function action_test(){
        $tm = new Testmodel(array('lh_key'=>'boo'));
        $tm->save();

        $lh = new Lionheart(array('key'=>'boo'));
        $lh->save();

        print_r($tm->getAllLionhearts());
        
    }

    public function action_prop(){
       echo 'foo';
       print_r(Testmodel::collection());
    }

    public function action_cursor(){
        foreach(Testmodel::find()->sort(array('lh_key'=>1)) as $val){
            echo $val->lh_key;
        }
    }

}
