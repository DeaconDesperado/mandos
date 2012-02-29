<?
class Controller_Mandos extends Controller{

    public function action_test(){
        $tm = new Testmodel(array('lh_key'=>'boo'));
        $tm->save();

        $lh = new Lionheart(array('key'=>'boo'));
        $lh->save();

        print_r($tm->getAllLionhearts());
        
    }

}
