<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
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
include_once($classespath_."class.AnagraficaParte.php");
include_once($classespath_."class.AnagraficaEst.php");
include_once($classespath_."class.AnagraficaTipo.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.MediazioneStato.php");
include_once($classespath_."class.MediazioneTipoIstanza.php");
include_once($classespath_."class.MediazioneTipoRichiesta.php");
include_once($classespath_."class.Materia.php");
include_once($classespath_."class.MediazioneModPre.php");
include_once($classespath_."class.Mediazione.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Lingua.php");
include_once($classespath_."class.Aula.php");
include_once($classespath_."class.MediazioneEsitoNegativo.php");
//include_once($classespath_."as_reportool/as_reportool.php");

$ModuloId=6;



function show_list()
{
  global $user;


 



$db= new Database();
$db->connect();
$page=new Form();
$gestore=new Gestore();
$gestore->conn=$db;

$gestore->getGestoreAll($user->GestoreId);
$arr_gestore=$gestore->ArrGestore;

$sede=new Sede();
$sede->conn=$db;
$gestorecorrente=$user->GestoreId;
$arr_sedi=$sede->getSediByGestori($user->GestoreId);

include_once("stat_estratto_conto_validator.php");  

?>


<div>
    
		
			
			
<!--inizio riga per i filtri--->		
			
			
			<div class="brainFiltri">
				<form id="application_form" name="application_form"  method="post" action="#">
<?
    $page->create_select("Gestore","GestoreId","GestoreId","rowForm",$arr_gestore,$gestorecorrente,"GestoreId","RagioneSociale",
    array("onChange"=>"'javascript:getSediByGestore(this);'"
                                                   ),1);
 
 
    $page->create_select("Sede","SedeId","SedeId","rowForm",$arr_sedi,$user->SedeId,"SedeId","Sede",
                                             null,1);
    
    
    
    ?>
                                    
                                    <div class="rowForm">
						<label for="tipo_report">tipo di report:</label>
						<select name="tipo_report">
                                                      <option value="1" selected >giornaliero</option>
                                                      <option value="2">mensile</option>
                                                      <option value="3">annuale</option>
						  
			                        
						</select>
				    </div>
                                    
                                    
                                    <?
                                     
                          
                                    
                                    ?>
                                    
                                    <div class="rowForm">
						<label for="Dal">dal</label>
						<input class="required" type="text" value="" id="Dal" name="Dal" maxlength="255" size="10">
						<label for="dataAl">al</label>
						<input class="required" type="text" value="" id="Al" name="Al" maxlength="255" size="10">
				    </div>
                                    
                                   <!-- <br style="clear:both;">
                                    
                                    
					
                                   -->
					<div class="rowForm">
						<input name="applica" type="submit" value="genera" />
					</div>
					<br style="clear:both;" />
				</form>
			</div>
			
			
			
<!--fine riga per i filtri--->	

<div id="risultato_report">
    
    
</div>    





</div>
    
    

    
    


           

<?
   
}



if(is_object($user)) {
   
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
			 $do=$_REQUEST['do'];
			if(!isset($do)) 
			$do='';
		
		
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