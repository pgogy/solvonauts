<?PHP

foreach (glob(__DIR__ . "/*.inc") as $filename){

    require_once($filename);

}