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



include_once($classespath_."class.TipologiaBus.php");


global $ModuloId;
$ModuloId=26;// modulo base mediazione




global $user;
global $tipologiabus_wizard,$funzione_edit,$abilita_modifica;

$funzione_edit=false;
$tipologiabus_wizard=null;

if(isset($_SESSION['TIPOLOGIABUS_WIZARD'])) {
$tipologiabus_wizard=unserialize($_SESSION['TIPOLOGIABUS_WIZARD']);
}










function show_list()
{
   
global $user,$HtmlCommon,$ModuloId, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['tipo_bus']['titolo_gestione'],1,"","");
$HtmlCommon->html_titolo_box($dizionario['tipo_bus']['titolo_gestione']);
$db= new Database();
$db->connect();



include_once("tipologiabus_validator.php");           
include_once("tipologiabus_datatable.php");

global $user,$HtmlCommon,$db,$ModuloId, $dizionario;    
 

$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_tipologiabus','tipologiabus.php?do=add',$dizionario['tipo_bus']['aggiungi']);

?>   
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
            
            	<tr class="brain_tabellaTr">
                    <th width="10%"><?=$dizionario['generale']['stato']?></th>
					<th width="%75"><?=$dizionario['tipo_bus']['tipo_bus']?></th>
					<th width="10%"><?=$dizionario['tipo_bus']['num_piani']?></th>
                    <th width="10%"><?=$dizionario['tipo_bus']['tot_posti']?></th>
                        
                    <th width="5%"><?=$dizionario['generale']['edita']?></th>
		</tr>
            
		<tr class="brain_tabellaFilter">
                        <th><input type="text" /></th> 
			<th><input type="text" /></th> 
			<th><input type="text" /></th> 
                      <th><input type="text" /></th> 
                    
			<th><input type="hidden" /></th> 
		</tr>
	</thead>
	<tbody>
         
		<tr>
			<td colspan="5" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
		</tr>
	</tbody>
	<tfoot> 
		<tr> 
			<td colspan="5"></td>
		</tr> 
	</tfoot> 
</table>
<?   
$db->close();

}


function edit($TipologiaBusId)
{
    global $tipologiabus_wizard,$db,$user;   
    
    if ($TipologiaBusId>0)
    {
        
         $tipologiabus_wizard=new TipologiaBus($TipologiaBusId);
  
    }
    
   
    
    $_SESSION['TIPOLOGIABUS_WIZARD']=serialize($tipologiabus_wizard);
    add(1);
}


function carica_menu_tipologiabus($step_corrente,$mod)
{
global $abilita_modifica,$tipologiabus_wizard,$db, $dizionario;
//$tipologiabus_wizard->conn=$db;
//$menu=$tipologiabus_wizard->getMenuWizard();

   
$menu=array(
    1=>$dizionario['tipo_bus']['tipo_bus'],
    2=>$dizionario['tipo_bus']['disposizione_posti']
  
   //3=>"Tariffe servizi aggiuntivi"
    );


    ?>
                <div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
			<ul>
                         <?
                         $contamenu=1;
                         while ($contamenu<=2)
                         {
                          $class1="";
                          $class2="";
                          
                          if ($contamenu==$step_corrente)
                          {
                              $class1="sel";
                              $class2="brain_firstspan sel";
                          }
                             
                          $StatoStep="";
                            
                          if ( ($contamenu<=2) or (($contamenu>2) and ($mod))) { ?>
                            
                            <li class="<?=$class1?>">
                                <span class="<?=$class2?>">
                                  <? //if (($contamenu<=$step_corrente) or ($mod==1))
                                  if (true)
                                  {
                                     
                                      
                                   ?>
                                             <a href="javascript:void(0);" onclick="loadMediazioneStep('rt_tipologiabus','tipologiabus.php?do=add&step=<?=$contamenu?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
                                    <?

                                  }
                                  else
                                    echo($menu[$contamenu]);
                                   
                              
                                
                           ?>     
                                </span>
                                
                                
                                
                                
                            
                            </li>
                            <?
                          }
                             
                             
                             $contamenu++;
                         }
                         
                         ?>
                         
                         
                                                     
				<!--<li class="sel"><span class="brain_firstspan sel"><a href="">Dati generali</a></span></li>
				<li class=""><span class="">Parte istante</span></li>
				<li class=""><span class="">Controparte</span></li>
				<li class=""><span class="">Oggetto e ragioni della pretesa</span></li>
				<li class=""><span class="">Documenti allegati</span></li>
				<li class=""><span class="">Pagamenti</span></li>
				<li class=""><span class="">Incontro di mediazione</span></li>-->
			</ul>
		</div>
 
 <?
    
    
    
}









function add_step_tipologiabus_posti()
{

$step_corrente=2;
 
global $tipologiabus_wizard,$user,$db, $dizionario;

$page=new Form();
 $dt=new DT();

$TipologiaBusId=$tipologiabus_wizard->Id;
$tipologiabus_wizard->conn=$db;
$tipologiabus_wizard->InizializzaDatiGenerali();

$NumeroColonne=$tipologiabus_wizard->DatiGenerali['Colonne'];
$NumeroRighe=$tipologiabus_wizard->DatiGenerali['Righe'];
$NumeroPiani=$tipologiabus_wizard->DatiGenerali['NumeroPiani'];


include_once("tipologiabus_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",0);
                           $page->create_textbox_hidden("action","create_dettaglio");
                        ?>
                            
                  
			<div class="brain_formModifica formGestoreEdita">
                             <h2><span class="brain_colorh2"><?=$dizionario['tipo_bus']['disposizione_posti_sedere']?></span></h2>
                         
                        
                           <? 
                           $npiani=1;
                           while ($npiani<=$NumeroPiani)
                           {
                               ?>
                             <div style="width:99%;" class="disposizionepiani">
                                 
                             
                             <h2><?=$dizionario['tipo_bus']['disposizione_posti_piano']?> <?=$npiani?></h2>    
                             
                             <table style="width:97%;" cellspacing="0" cellpadding="0" border="0" width="100%" id="gestoreElencoAule">
                                    <tbody>
                                    <tr class="rowIntestazione">
                                      <td></td>
                                 
                                 <?
                        
                        $i=0;
                        $alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
                          while ($i< $NumeroColonne)
                            {
                             ?>
                             <td><?=$alphabet[$i]?> </td>
                              
                            <?  
                              $i++;
                          }
                      ?>
                                    </tr>
                       <?
                     
                        $i=0;
                        
                          while ($i< $NumeroRighe)
                            {
                             
                               ?>
                                    
                                    
                             <tr>
                                 <td><?=$i+1?></td>
                             <?
                             $n=0;
                             while ($n< $NumeroColonne)
                             {
                                $BigliettoId=$ArrObjectTB[$n]['TipologiaBigliettoId'];   
                                $fisso="";
                                $percentuale="";
                                $rigacorrente=$i+1;
                                $colonnacorrente=$n+1;
                                $sql="Select * from RT_TipologiaBusDettaglioPosto where NumeroPiano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and TipologiaBusId=$TipologiaBusId and OdcIdRef=$user->OdcId";
                            
                                $row1 = $db->query_first($sql);
                                $NumeroPosto="";
                                $DescrizionePosto="";
                                if (!empty($row1['TipologiaBusId']))
                                {
                                   $NumeroPosto=$row1['NumeroPosto'];
                                   $DescrizionePosto=$row1['DescrizionePosto'];
                                }
                                
                                
                               
                            //  echo($sql);
                                 
                                 
                             ?>
                              <td>
                                         
                             
                              <table>
                                <tbody><tr>
                                    <td>
                                    N 
                                    </td>
                                    <td>
                                                      <input class="numberDE" type="text" name="TipologiaBusDettaglioPosto[<?=$npiani."_".$rigacorrente."_".$colonnacorrente?>]" value="<?=$NumeroPosto?>" SIZE="5" MAXLENGTH="5">

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                              D
                                  
                                    </td>
                                    <td>
                                        <input type="text" value="<?=$DescrizionePosto?>" size="22" value="" name="TipologiaBusDescrizionePosto[<?=$npiani."_".$rigacorrente."_".$colonnacorrente?>]">
                                    </td>

                                </tr>
                                    


                                      
                             </tbody></table>
                              
                              
                              
                              
                              
                              
                              </td>
                              
                             
                              <?
                                 
                                 $n++;
                             }
                             ?>
                             
                             
                             
                             </tr>
                            <?  
                              $i++;
                          }
                       
                       
                       ?>
                                    
                                    </tbody>
                         </table>
                             </div>
                         <?
                         $npiani++;
                           }
                           ?>
                             
                        </div>
                           <div class="divSubmit">
                                
        <?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>
   
   
				
</div>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}




function add_step_tipologiabus()
{

$step_corrente=1;
 
global $tipologiabus_wizard,$user,$db, $dizionario;

$page=new Form();
 $dt=new DT();

 $Nome="";
 $action="create";
if ((is_object($tipologiabus_wizard)) and ($tipologiabus_wizard->Id>0))
{
    
    $Nome=$tipologiabus_wizard->DatiGenerali['TipologiaBus'];
    $Righe=$tipologiabus_wizard->DatiGenerali['Righe'];
    $Colonne=$tipologiabus_wizard->DatiGenerali['Colonne'];
    $Posti1=$tipologiabus_wizard->DatiGenerali['PostiPrimoPiano'];
    $Posti2=$tipologiabus_wizard->DatiGenerali['PostiSecondoPiano'];
    $Stato=$tipologiabus_wizard->DatiGenerali['Stato'];
   // $IsDefault=$tipologiabus_wizard->DatiGenerali['IsDefault'];
    $action="update";
    
}
    
 
$tipologiabus_wizard=$tipologiabus_wizard->Id;
 $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

 $arr_sino[]= array("IsDefaultId" => '0',"IsDefault" => $dizionario['generale']['no']);
$arr_sino[]= array("IsDefaultId" => '1',"IsDefault" => $dizionario['generale']['si']);

include_once("tipologiabus_validator.php");
?>

			<form id="application_form" name="application_form"  method="post" action="#">
                        <?
                           $page->create_textbox_hidden("step_corrente",$step_corrente);
                           $page->create_textbox_hidden("step_successivo",$step_corrente+1);
                        ?>
                            
                  
			 <div class="brain_formModifica">
                                <div class="brain_data-content">    
                <?
                $page->create_textbox_hidden("action",$action);
                $page->create_textbox($dizionario['generale']['nome'],"TipologiaBus","TipologiaBus[TipologiaBus]",$Nome,1,"brain_campoForm campiformBig",array("class"=>"'required'"));       
                
                print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['tipo_bus']['posti1'],"Posti1","TipologiaBus[PostiPrimoPiano]",$Posti1,1,"brain_campoForm",array("class"=>"'required numberDE'"));       
               
                 
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['tipo_bus']['posti2'],"Posti2","TipologiaBus[PostiSecondoPiano]",$Posti2,1,"brain_campoForm",array("class"=>"'required numberDE'"));       
               
                print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['tipo_bus']['righe'],"Righe","TipologiaBus[Righe]",$Righe,1,"brain_campoForm",array("class"=>"'required numberDE'"));       
               
                 
                 print("<br style=\"clear:both;\"/>");
                 $page->create_textbox($dizionario['tipo_bus']['colonne'],"Colonne","TipologiaBus[Colonne]",$Colonne,1,"brain_campoForm",array("class"=>"'required numberDE'"));       
               
                  print("<br style=\"clear:both;\"/>");
              //    $page->create_select("Default","TipologiaBus[IsDefault]","IsDefaultId","brain_campoForm",$arr_sino,$IsDefault,"IsDefaultId","IsDefault",
               // array("class"=>"'required'"),1);
                
               // print("<br style=\"clear:both;\"/>");
               //  $page->create_textbox("Peso","Peso","TipologiaServizio[TipologiaServizioPeso]","",1,"brain_campoForm",array("class"=>"'required'"));       
               
                $page->create_select($dizionario['generale']['stato'],"TipologiaBus[Stato]","StatoId","brain_campoForm",$arr_stato,$Stato,"StatoId","Stato",
                array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
              
               
                ?>
                                      </div>
                         </div>
                            <? spara_pulsanti_wizard(0) ?>
                
	</form>
		
 
<?
    
    
 $db->Close();   
}


function add($step)
{
 include_once("tipologiabus_validator.php");  
 
 global $HtmlCommon,$db,$tipologiabus_wizard,$funzione_edit,$abilita_modifica, $dizionario;





if (!$step)
{
$tipologiabus_wizard=null;    
unset($tipologiabus_wizard);
$_SESSION['TIPOLOGIABUS_WIZARD']=null;
unset($_SESSION['TIPOLOGIABUS_WIZARD']);
$step=1;
}
$mod=0;

if (is_object($tipologiabus_wizard))
{
    
    
    $TipologiaBusId=$tipologiabus_wizard->Id;
    $tipologiabus_wizard->conn=$db;
    $tipologiabus_wizard->inizializzaDatiGenerali();
    $DatiGeneraliArr=$tipologiabus_wizard->DatiGenerali;
   $Nome=$DatiGeneraliArr['TipologiaBus'];
    $Stato=$DatiGeneraliArr['Stato'];
   
    
    
    $mod=1;
     $abilita_modifica=true;
     $HtmlCommon->html_titolo_pagina($dizionario['tipo_bus']['disp_posti']." ".$DatiGeneraliArr['TipologiaBus'],0,"rt_tipologiabus","tipologiabus.php");
} 
else
{        $mod=0;
        $abilita_modifica=false;
        $HtmlCommon->html_titolo_pagina($dizionario['tipo_bus']['tipo_bus'],1,"rt_tipologiabus","tipologiabus.php");
}

  
        
        carica_menu_tipologiabus($step,$mod);
 
 ?>

		<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">     
 <?       
 

if ($step==1)
	add_step_tipologiabus();
elseif ($step==2)
	add_step_tipologiabus_posti();




$db= new Database();
    $db->connect();

?>
                   
        
	</div>

<?php
    
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
    
global $funzione_edit, $dizionario;

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
    
         <a href="javascript:void(0);" onclick="loadMainContent('rt_tipologiabus','tipologiabus.php',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
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
    if (is_object($tipologiabus_wizard))
        $tipologiabus_wizard->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {    
			 $do=$_REQUEST['do'];
			if(!isset($do)) 
			$do='';
		
		
			switch($do) {
                            
                               

				case "add":
                                  
                                    
                                    
					$FunzioneId=2;
                                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                            add($_REQUEST['step']);
                                        else
                                           $errore->stampa_errore(2);
                                        
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                               
				case "edit":
				
                                 $FunzioneId=1;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                
                                        if (sizeof($permesso))
                                           edit($_REQUEST['TipologiaBusId']);
                                        else
                                            $errore->stampa_errore(2);
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                                case "cerca":
				
                                 $FunzioneId=1;
                                 $permesso=$user->ControllModuloFunzionePermesso(2,$FunzioneId);
                                
                                        if (sizeof($permesso))
                                          cerca_mediatore();
                                        else
                                            $errore->stampa_errore(2);
                                    
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
                                
                               


				default:
				$FunzioneId=1;
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
// se l'utente non ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¨ loggato
else {
header("Location: /logout.php");
}
?>