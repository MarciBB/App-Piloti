<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" type="text/css" href="/css/print_stat_mediazione.css" media="print" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
//include_once($classespath_."as_reportool/as_reportool.php");

$ModuloId=12;



function show_list()
{
  global $user, $dizionario;


 
$datacorrente=Date('d/m/Y');


$db= new Database();
$db->connect();
$page=new Form();
$gestore=new Gestore();
$gestore->conn=$db;
$ges=$user->GestoreId;
if (($user->GestoreId==1) or ($user->GestoreId==2))
{
    $ges=1;
}
$gestore->getGestoreAll($ges);
$array_tipo = array();
$array_tipo[] = ['TipoId' => 'invoice', 'Tipo' => $dizionario['biglietto']['invoice']];
$array_tipo[] = ['TipoId' => 'receipt', 'Tipo' => $dizionario['biglietto']['receipt']];
$array_tipo[] = ['TipoId' => 'credit_note', 'Tipo' => $dizionario['biglietto']['credit_note']];

$sede=new Sede();
$sede->conn=$db;
$tipocorrente=0;


include_once("fatturaincloud_biglietti_validator.php");

?>


<div>
    		
<!--inizio riga per i filtri--->		
			
			
<div class="brainFiltri">
	<form id="application_form" name="application_form"  method="post" action="#">
    	<?php
    	$page->create_select($dizionario['biglietto']['tipo'].":","Tipo","Tipo","rowForm",$array_tipo,$tipocorrente,"TipoId","Tipo",
        array(),1);
        ?>                               
    	<div class="rowForm">
    		<label for="Dal"><?=$dizionario['generale']['dal']?></label>
    		<input class="required" type="text" value="<?=$datacorrente?>" id="Dal" name="Dal" maxlength="255" size="10">
    		<label for="dataAl"><?=$dizionario['generale']['al']?></label>
    		<input class="required" type="text" value="<?=$datacorrente?>" id="Al" name="Al" maxlength="255" size="10">
        </div>

		<div class="rowForm">
			<input name="applica" type="submit" value="<?=$dizionario['generale']['genera']?>" />
		</div>
		<br style="clear:both;" />
	</form>
</div>
			
			
			
<!--fine riga per i filtri--->	

<div id="risultato_report">

</div>    

</div>
    
<?php
   
}



if(is_object($user)) {
   
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
	if(!isset($_REQUEST['do'])){
    	$do='';
    } else {
    	$do=$_REQUEST['do'];
    }
				
	switch($do) {
		default:
            $FunzioneId=1;
            $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
           
                if (sizeof($permesso))
                  show_list();    
		break;
	}
} 
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>