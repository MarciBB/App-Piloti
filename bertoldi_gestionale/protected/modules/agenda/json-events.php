<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$ModuloId=5;


$OdcIdRef=$user->OdcId;
$OperatoreTipoId=$user->OperatoreTipoId;
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Gestore.php");
$gestore=new Gestore();
$gestore->conn=$db;
$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli=implode(",", $gestorefigli);
$dataoracorrente=date("Y-m-d");

$db= new Database();
$db->connect();

$sql = "SELECT * from RT_ViewOperativita where OdcIdRef=$user->OdcId";

$ArrObject1 = $db->fetch_array($sql);
$ArrObjectSize=count($ArrObject1);  
$i=0;


 while ($i< $ArrObjectSize)
 {
    $ArrObject[$i]['allDay']=false;
    $ArrObject[$i]['title']=$ArrObject1[$i]['CorsaNome'];  
    $ArrObject[$i]['start']=$ArrObject1[$i]['AppCalendarioData']." ".$ArrObject1[$i]['OrarioPartenza'];  
    
    
    $i++;
}
echo json_encode($ArrObject);
	/*$year = date('Y');
	$month = date('m');

	echo json_encode(array(
	
		array(
			'id' => 111,
                        'allDay'=> false,
			'title' => "Via Dentice Nocera Inferiore - Aula 1 - Esposito/Adinolfi",
			'start' => "$year-$month-21 10:00:00",
                        'end' => "$year-$month-21 12:00:00",
			'url' => "http://yahoo.com/"
		),
		
		array(
			'id' => 222,
                        'allDay'=> false,
			'title' => "Napoli Esposito/Adinolfi",
			'start' => "$year-$month-21 10:00:00",
			'end' => "$year-$month-21 12:00:00",
			'url' => "http://yahoo.com/"
		)
            ,array(
			'id' => 222,
                        'allDay'=> false,
			'title' => "Napoli - Esposito/Adinolfi",
			'start' => "$year-$month-21 10:00:00",
			'end' => "$year-$month-21 12:00:00",
			'url' => "http://yahoo.com/"
		)
            
            
	
	));
 
*/

?>
