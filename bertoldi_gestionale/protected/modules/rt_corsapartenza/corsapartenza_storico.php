<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");


include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.TipologiaBus.php");


global $ModuloId;
$ModuloId=28;// modulo base mediazione
global $user;
global $corsapartenza_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$corsapartenza_wizard=null;

if(isset($_SESSION['CORSAPARTENZA_WIZARD'])) {
$corsapartenza_wizard=unserialize($_SESSION['CORSAPARTENZA_WIZARD']);
}





function show_list()
{
global $user,$HtmlCommon, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['partenza']['titolo_gestione_partenza']." Storico");
$HtmlCommon->html_titolo_box($dizionario['partenza']['titolo_gestione_partenza']." Storico");
$db= new Database();
$db->connect();
include_once("corsapartenza_validator_storico.php");
include_once("corsapartenza_datatable_storico.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="5%"><?=$dizionario['partenza']['stato_web']?></th>
            <th width="5%"><?=$dizionario['generale']['stato']?></th>
           <!-- <th width="10%"><?=$dizionario['partenza']['dt_ultima_preparazione']?></th>
            <th width="10%"><?=$dizionario['partenza']['dt_consolidamento']?></th>-->
            <th width="10%"><?=$dizionario['generale']['corsa']?></th>
            <th width="15%"><?=$dizionario['generale']['linea']?>linea</th>
           <!-- <th width="12%">percorso</th> -->
            <th width="6%"><?=$dizionario['generale']['data_partenza']?></th>
          <!--  <th width="5%">giorno</th> -->
            <th width="6%"><?=$dizionario['generale']['ora_partenza']?></th>
            <th width="3%"><?=$dizionario['partenza']['pt']?></th>
            <th width="3%"><?=$dizionario['partenza']['pp']?></th>
            <th width="3%"><?=$dizionario['partenza']['pd']?></th>
            <th width="3%"><?=$dizionario['partenza']['prp']?></th>
            <th width="3%"><?=$dizionario['partenza']['prd']?></th>
            <th width="3%"><?=$dizionario['partenza']['pmax']?></th>
            <th width="3%"><?=$dizionario['partenza']['sp']?></th>
            <th width="3%"><?=$dizionario['generale']['pax']?></th> 
            <th width="3%"><?=$dizionario['partenza']['blocca']?></th>
            <th width="3%"><?=$dizionario['partenza']['blocca_web']?></th>
            <th width="3%"><?=$dizionario['partenza']['pre']?></th>
            <!--<th width="3%"><?=$dizionario['partenza']['post']?></th>-->
            
             
        </tr>
        <tr class="brain_tabellaFilter">
             <th><input type="hidden" /></th>
             <th><input type="hidden" /></th>
              <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input  type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th>
           <th><input type="text" /></th> 
            <th><input type="text" /></th> 
            <th><input type="text" /></th> 
             <th><input type="text" /></th> 
             <th><input type="hidden" /></th>
             <th><input type="hidden" /></th>
            <th><input type="hidden" /></th>
            <th><input type="hidden" /></th>
           <!-- <th><input type="hidden" /></th>-->
          
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="20" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
        </tr>
    </tbody>
    <tfoot> 
        <tr>
            <td colspan="20" ></td>
        </tr> 
    </tfoot> 
</table>

<?
   
}

function AddPax()
{
    global $HtmlCommon,$user,$db, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  


$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['DataPartenza'];
    
$corsa=new Corsa($CorsaId);
$corsa->conn=$db;
$corsa->inizializzaDatiGenerali();
$arr_corsa=$corsa->DatiGenerali;
$CorsaNome=$arr_corsa['CorsaNome'];
$BusDefault=$arr_corsa['TipologiaBusDefaultId'];

$tipologiabus=new TipologiaBus($BusDefault);
$tipologiabus->conn=$db;
$tipologiabus->inizializzaDatiGenerali();
$arr_tipologiabus=$tipologiabus->DatiGenerali;
$posti_default=$arr_tipologiabus['TotalePosti'];





$sql="Select PostiDisponibili from RT_ViewOperativitaBlocchi where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";


$row = $db->query_first($sql);

$posti_aggiunti=0;
if (!empty($row['PostiAggiunti']))
    $posti_aggiunti=$row['PostiAggiunti'];

$sql="Select PostiDisponibili from RT_ViewOperativitaBlocchi where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and AppCalendarioData='$DataPartenza'";


$row = $db->query_first($sql);

$posti_disponibili=0;
if (!empty($row['PostiDisponibili']))
    $posti_disponibili=$row['PostiDisponibili'];




//$posti_disponibili=$posti_default+$posti_aggiunti-$posti_prenotati;

 $HtmlCommon->html_titolo_pagina($dizionario['partenza']['titolo_aggiungi_posti'],0,"","");
$HtmlCommon->html_titolo_box ($dizionario['partenza']['titolo_aggiungi_posti2']." ".$CorsaNome." ".$dizionario['biglietto']['del']." ".$DataPartenza);
include_once("corsapartenza_validator_storico.php");
?>

<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
                   <form id="application_form" name="application_form" method="post" action="#">
                      
                                <div class="brain_data-content">    
                <?
                $page->create_textbox_hidden("action","AggiungiPax");
                 $page->create_textbox_hidden("CorsaPax[CorsaId]",$CorsaId);
                  $page->create_textbox_hidden("CorsaPax[DataPartenza]",$DataPartenza);
                  
                 
      
                
                $page->create_textbox($dizionario['partenza']['num_pax'],"Pax","CorsaPax[NumeroPax]","",1,"brain_campoForm",array(
       "onChange"=>"'javascript:VerificaPosti($posti_disponibili,this);'",
       "class"=>"'required number'"));
               
               
                ?>
                                      </div>
                        
                             <div class="divSubmit">
                                    <?
                                  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
                                  
                                    ?>
                                          

                            </div>     
                             
                             
                        </form>
                    </div>   
		</div>

<?


exit();

}

function GestionePax()
{
    
    global $HtmlCommon,$user,$db, $dizionario;  

$db= new Database();
$db->connect();  
$page=new Form();  

$CorsaId=$_REQUEST['CorsaId'];
$DataPartenza=$_REQUEST['DataPartenza'];

$page=new Form();  

$corsa=new Corsa($CorsaId);
$corsa->conn=$db;
$corsa->inizializzaDatiGenerali();
$arr_corsa=$corsa->DatiGenerali;
$CorsaNome=$arr_corsa['CorsaNome'];
$BusDefault=$arr_corsa['TipologiaBusDefaultId'];

$tipologiabus=new TipologiaBus($BusDefault);
$tipologiabus->conn=$db;
$tipologiabus->inizializzaDatiGenerali();
$arr_tipologiabus=$tipologiabus->DatiGenerali;
$posti_default=$arr_tipologiabus['TotalePosti'];


 $HtmlCommon->html_titolo_pagina($dizionario['partenza']['titolo_gestione_pax'],0,"","");
$HtmlCommon->html_titolo_box ($dizionario['partenza']['titolo_gestione_pax2']." ".$CorsaNome." ".$dizionario['biglietto']['del']." ".$DataPartenza);



$sql="Select PostiAggiunti from RT_ViewSingolaCorsaPostiAggiunti where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";


$row = $db->query_first($sql);

$posti_aggiunti=0;
if (!empty($row['PostiAggiunti']))
    $posti_aggiunti=$row['PostiAggiunti'];

$sql="Select PostiAggiunti from RT_ViewSingolaCorsaPostiPrenotati where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza'";


$row = $db->query_first($sql);

$posti_prenotati=0;
if (!empty($row['TotalePaxPrenotati']))
    $posti_aggiunti=$row['TotalePaxPrenotati'];




$posti_disponibili=$posti_default+$posti_aggiunti-$posti_prenotati;


?>
<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     

                        <?
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",0);
                           $page->create_textbox_hidden("action","create");
                        ?>
                            
                  <h2><?=$dizionario['partenza']['pax_defauld']?> <?=$posti_default?>; <?=$dizionario['partenza']['pax_gestiti']?> <?=$posti_aggiunti?>; <?=$dizionario['partenza']['pax_prenotati']?> = <?=$posti_prenotati?>; <?=$dizionario['partenza']['pax_prenotabili']?> = <?=$posti_disponibili?></h2> 
			<div class="brain_formModifica formGestoreEdita">
                            
                            
                        <div class="GestoreSedeAdd">
                             <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_corsapartenza','corsapartenza.php?do=addpax&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>');" title="aggiungi / rimuovi pax"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['partenza']['aggiungi_pax']?></a>
                        </div>
                            
                              <br />
                                <table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
                                    <tbody><tr class="rowIntestazione">
                                    <td><?=$dizionario['partenza']['data_inserimento']?></td>
                                    <td><?=$dizionario['partenza']['operatore']?></td>
                                    <td><?=$dizionario['partenza']['num_posti']?></td>
                                   
                                    </tr>

                         <?
                        $sql="Select * from RT_ViewElencoPostiAggiunti where OdcIdRef=$user->OdcId and CorsaId=$CorsaId and DataPartenza='$DataPartenza' order by DataIns asc";

                        
                        $ArrObject = $db->fetch_array($sql);
                        $i=0;
                          while ($i< sizeof($ArrObject))
                            {
                              $CorsaPaxId=$ArrObject[$i]['CorsaPaxId'];
                              $NumeroPax=$ArrObject[$i]['NumeroPax'];
                              $Operatore=$ArrObject[$i]['Cognome']." ".$ArrObject[$i]['Nome'];
                              $DataIns=$ArrObject[$i]['DataIns'];
                              
                            ?>
                             <!-- QUI L'ELENCO DELLE FERMATE -->
                                <tr class="rowBianca">
                                    <td><span><?=$DataIns?></span></td>
                                    
                                     <td><span><?=$Operatore?></span></td>
                                     <td><span><?=$NumeroPax?></span></td>
                                     
                                    
                                    
                                </tr>
                           
                             
                             <?
                             
                              
                              $i++;
                          }
                        
                        
                        
                        
                        ?>
                              </tbody>
                            </table>
                             <!-- FINE -->
                             <br />
                       <div class="GestoreSedeAdd">
                             <a class="brain_add" href="#" onclick="javascript:ExternalLoad('rt_corsapartenza','corsapartenza.php?do=addpax&CorsaId=<?=$CorsaId?>&DataPartenza=<?=$DataPartenza?>');" title="aggiungi / rimuovi pax"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['partenza']['aggiungi_pax']?></a>
                        </div>
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        </div>
                       
                
	

</div>
<?


}



















function spara_pulsanti_wizard_box()
{
	global $dizionario;
$page=new Form();
    
?>
<div class="divSubmit">
                                
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
   
   
    
         <a href="javascript:void(0);" onclick="javascript:ChiudiBox();" title="chiudi" class="brain_annulla"><?=$dizionario['generale']['chiudi']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>
    <?

}


function spara_pulsanti_wizard($steptogo)
{
    
global $funzione_edit;

if ($funzione_edit)
    spara_pulsanti_edit($steptogo);
else
{
if (!$funzione_edit)
$page=new Form();
    
?>
<div class="divSubmit">
                                
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['avanti'],"brain_salva","submit"); ?>
    <?  
    if ($steptogo>0)
    $page->create_button("indietro","indietro",$dizionario['generale']['indietro'],"brain_back","button"); ?>
    
         <a href="javascript:void(0);" onclick="loadMainContent('mediatore','mediatore.php',this);" title="Home" class="brain_annulla"><?=$dizionario['convenzione']['annulla']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>
    <?
    
    
}
}

function spara_pulsanti_edit($steptogo)
{
   
global $abilita_modifica, $dizionario;
    
    $page=new Form();
    
    ?>
<div class="divSubmit">
                         
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
       
         <a href="javascript:void(0);" onclick="loadMainContent('mediazione','mediazione.php?step=2',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
         <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
	
				
</div>  
    <?
}



if(is_object($user)) {
    
/*      ID - FUNZIONE
1	Lista
2	Aggiunta
3	Cancellazione
4	Modifica
5	Esportazione
6	Importazione
7	Stampa
 */ 
 
    
    
    
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    if (is_object($corsapartenza_wizard))
        $corsapartenza_wizard->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {    
	    if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }
		
		
			switch($do) {
                            
                               

				
                                case "addpax":
				
                                 $FunzioneId=2;
                                 $permesso=$user->ControllModuloFunzionePermesso(2,$FunzioneId);
                                
                                        if (sizeof($permesso))
                                          AddPax();
                                        else
                                            $errore->stampa_errore(2);
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                               
				
                                case "GestionePax":
				
                                 $FunzioneId=2;
                                 $permesso=$user->ControllModuloFunzionePermesso(2,$FunzioneId);
                                
                                        if (sizeof($permesso))
                                          GestionePax();
                                        else
                                            $errore->stampa_errore(2);
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                               


				default:
				$FunzioneId=2;
                                    $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           show_list();    
                                        else
                                            $errore->stampa_errore(2);  
                               
                         		// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
			}
		
	} // end verifica permessi
	else {
           $errore->stampa_errore(1);
            
        }

} 

else {
header("Location: /logout.php");
}
?>