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
include_once($classespath_."class.Mediazione.php");
include_once($classespath_."class.Odc.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");


$ModuloId=30;



function show_list()
{
  global $user;


 



$db= new Database();
$db->connect();
$page=new Form();
$odc_1=new Odc();
$odc_1->conn=$db;
$arr_odc=$odc_1->getOdcAll();

include_once("stat_provvigioni_maturate_validator.php");  

?>


<div>
    
		
			
			
<!--inizio riga per i filtri--->		
			
			
			<div class="brainFiltri">
				<form id="application_form" name="application_form"  method="post" action="#">
<?
    $page->create_select("Organismo","OdcId","OdcId","rowForm",$arr_odc,0,"OdcId","Odc",null,1);
 
 
    
    
    
    ?>
                                    
                                    <div class="rowForm">
						<label for="tipo_report">tipo di report:</label>
						<select name="tipo_report">
                                                      <option value="1" selected >dettagliato</option>
                                                    
						  
			                        
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



if ( (is_object($user)) and ($user->OdcId==1)) {
   
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