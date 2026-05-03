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
include_once($classespath_."class.Odc.php");
$ModuloId=14;

function carica_menu_ruolo($step_corrente,$mod,$idRuolo)
{
global $abilita_modifica,$mediazione_wizard,$db, $dizionario;
$mediazione_wizard->conn=$db;
//$menu=$mediazione_wizard->getMenuWizard();

   
$menu=array(
    1=>$dizionario['convenzione']['dati_generali'],
    2=>$dizionario['operatore']['permessi']    
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
                           ?>
                           <li class="<?=$class1?>">
                                <span class="<?=$class2?>">
                                    <?
                                     if((!$mod)){
                                    ?>
                                    <a href="javascript:void(0);" onclick="loadMediazioneStep('ruolo','ruolo.php?do=step&step=<?=$contamenu?>&ruoloId=<?=$idRuolo?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
                                <?}
                                else
                                    echo($menu[$contamenu]);
                                    
                                ?>
                                </span>
                           </li>    
                   <?        
                         
                            $contamenu++;
                         }
                         
                         ?>                         
			</ul>
		</div>
 
 <?  
}

function add()
{
include_once("ruolo_validator.php");      
global $HtmlCommon,$user,$db, $dizionario;  
/*
$db= new Database();
$db->connect();  */
$step=1;
$mod=1;

$page=new Form();
$odc=new Odc();
$odc->conn=$db;

$GestoreId=$ruolo->GestoreId;
$HtmlCommon->html_titolo_pagina($dizionario['ruolo']['titolo_crea']." - ".$Nome,1,"ruolo","ruolo.php");
$HtmlCommon->html_titolo_box ($dizionario['ruolo']['titolo_crea']." - ".$Nome);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
carica_menu_ruolo($step,$mod,$ruoloId);
?>
<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter">    
    <form id="application_form" name="application_form" method="post" action="#">
        <div id="elencosoggetti" class="brain_formModifica">
            <h2><span class="brain_colorh2">Creazione nuovo ruolo</span></h2>
            <div class="brain_data-content" id="brain_data-content">
                <?

                $page->create_textbox_hidden("action","create");
                                        
                $page->create_textbox($dizionario['generale']['nome'],"Nome","Ruolo[Nome]","",1,"brain_campiform",array("class"=>"'required'")); 
                 print("<br style=\"clear:both;\"/>");                
               /* $page->create_select("Odc","Ruolo[OdcId]","OdcId","brain_campiform campiformBig",$arr_odc,"","OdcId","RagioneSociale",
                array("class"=>"'required'"),1,"Il ruolo sarà attivo per tutte le sedi disponibili.");*/
                $page->create_texarea($dizionario['generale']['descrizione'],"Descrizione","Ruolo[Descrizione]","",0,"brain_campiform",array("cols"=>"'60'","rows"=>"'12'"),"");
                ?>	
            </div> 
            
        </div>
      <div class="divSubmit">                      
        <input type="submit" name="Salva" class="brain_salva" id="Salva" value="salva">
        <a href="javascript:void(0);" onclick="loadMainContent('ruolo','ruolo.php',this);" title="Home" class="brain_annulla">Annulla</a>
        <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
      </div>
    </form>
</div>

<?  
$db->close();
}

function edit($ruoloId)
{
include_once("ruolo_validator.php");      

global $HtmlCommon,$user,$db, $dizionario;

$step=1;
$mod=0;
/*
$db= new Database();
$db->connect();  */
$page=new Form();


$ruolo=new Ruolo();
$ruolo->conn=$db;
$ruolo->inizializza($ruoloId);

$Nome=$ruolo->Nome;
$Descrizione=$ruolo->Descrizione;
$Stato=$ruolo->Stato;
$OdcId=$ruolo->OdcId;
$HtmlCommon->html_titolo_pagina($dizionario['ruolo']['titolo_edita']." - ".$Nome,1,"ruolo","ruolo.php");
$HtmlCommon->html_titolo_box ($dizionario['ruolo']['titolo_edita']." - ".$Nome);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

carica_menu_ruolo($step,$mod,$ruoloId);
?>
<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter">    
    <form id="application_form" name="application_form" method="post" action="#">
        <div id="elencosoggetti" class="brain_formModifica">
            <h2><span class="brain_colorh2"><?=$dizionario['ruolo']['editazione_biglietto']?> <?=$IntestCorrente?> - </span><?=$dizionario['convenzione']['dati_generali']?></h2>
            <div class="brain_data-content" id="brain_data-content">
                <?

                $page->create_textbox_hidden("action","update");
                $page->create_textbox_hidden("idpost",$ruoloId);

                         
                $page->create_textbox($dizionario['generale']['nome'],"Nome","Ruolo[Nome]",$Nome,1,"brain_campiform",array("class"=>"'required'")); 
                 print("<br style=\"clear:both;\"/>");                
               /* $page->create_select("Odc","Ruolo[OdcId]","OdcId","brain_campiform campiformBig",$arr_odc,$OdcId,"OdcId","RagioneSociale",
                array("class"=>"'required'"),1,"Il ruolo sarà attivo per tutte le sedi disponibili.");*/
                print("<br style=\"clear:both;\"/>"); 
                $page->create_texarea($dizionario['generale']['descrizione'],"Descrizione","Ruolo[Descrizione]",$Descrizione,0,"brain_campiform",array("cols"=>"'60'","rows"=>"'12'"),"");
                ?>	
            </div> 
            
        </div>
      <div class="divSubmit">        
        <input type="submit" name="elimina" class="brain_cancella" id="elimina" value="elimina ruolo" onClick="javascript:$('#action').val('elimina_ruolo');">        
        <input type="submit" name="Salva" class="brain_salva" id="Salva" value="salva">
        <a href="javascript:void(0);" onclick="loadMainContent('operatore','operatore.php',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
        <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
      </div>
    </form>
</div>


<?
$db->close();
}


function show_list()
{
global $user,$HtmlCommon,$db, $dizionario;
$HtmlCommon->html_titolo_pagina($dizionario['ruolo']['titolo_elenco']);
$HtmlCommon->html_titolo_box($dizionario['ruolo']['titolo_elenco']);
/*$db= new Database();
$db->connect();*/

include_once("ruolo_datatable.php");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="5%"><?=$dizionario['generale']['stato']?></th>            
            <th width="70%"><?=$dizionario['generale']['nome']?></th>
            <th width="15%"><?=$dizionario['ruolo']['odc']?></th>            
            <th width="5%"><?=$dizionario['generale']['edita']?></th>                     
        </tr>
        <tr class="brain_tabellaFilter">
            <th><span></span><input type="hidden" /></th>              
            <th><span class="hidden"></span><input type="text" /></th> 
            <th><span></span><input type="hidden" /></th>            
            <th><input type="hidden" /></th> 
        </tr>
    </thead>
    <tbody>         
        <tr>
            <td colspan="4" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
        </tr>
    </tbody>
	<tfoot> 
        <tr>
            <td colspan="4"></td>
        </tr> 
    </tfoot> 
</table>
<?   
$db->close();
}



function ruoli($ruoloId)
{
include_once("ruolo_validator.php");      

global $HtmlCommon,$user,$db;

$step=2;
$mod=0;
/*
$db= new Database();
$db->connect();  */
$page=new Form();  
$gestore=new Gestore();
$gestore->conn=$db;
$gestore->getGestoreAll($user->GestoreId);
$arr_gestori=$gestore->ArrGestore;


$ruolo=new Ruolo();
$ruolo->conn=$db;
$ruolo->inizializza($ruoloId);
$contenuto_merge=$ruolo->getMergeEditRuolo($ruoloId);

$OdcId=$ruolo->Odc;
$Nome=$ruolo->Nome;
$Stato=$ruolo->Stato;
$GestoreId=$ruolo->GestoreId;
$HtmlCommon->html_titolo_pagina("Ruolo ".$Nome." - Permessi",1,"ruolo","ruolo.php");
$HtmlCommon->html_titolo_box ("Ruolo ".$Nome);
$arr_stato[]= array("StatoId" => '0',"Stato" => 'Disattiva');
$arr_stato[]= array("StatoId" => '1',"Stato" => 'Attiva');

carica_menu_ruolo($step,$mod,$ruoloId);
?>
<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter">    
    <form id="application_form" name="application_form" method="post" action="#">
        <div id="elencosoggetti" class="brain_formModifica">
            <h2><span class="brain_colorh2">Ruolo <?=$Nome?> - </span>Permessi</h2>
            <p>Specificare il tipo di permesso per ciascun modulo e operazione.</p>
            <div class="brain_data-content" id="brain_data-content">
                 <?
                $page->create_textbox_hidden("action","update_ruoli");
                $page->create_textbox_hidden("idpost",$ruoloId);
                ?>               
              
                <div class="brain_colRight" style="width: 100%;">
                    <h3>Permessi</h3>
                     <div class="boxContenitoreTabellaOperatore" id="boxContenitoreTabella">
                       <?=$contenuto_merge?> 
                    </div>                
                </div>
                
            </div> <!-- fine brain data content -->

        </div>
        <div class="divSubmit">                    
        <input type="submit" name="Salva" class="brain_salva" id="Salva" value="salva">
        <a href="javascript:void(0);" onclick="loadMainContent('operatore','operatore.php',this);" title="Home" class="brain_annulla">Annulla</a>
        <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
      </div>
    </form>
</div>


<?
$db->close();
}



if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
    if (sizeof($permessi)>0)
    {    
	    if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }
        switch($do) {
            case "add":
                $FunzioneId=2;
                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                if (sizeof($permesso))
                    add();
                else
                    Errors::$ErrorePermessiModuloFunzione;
                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
                break;
                
            case "edit":
                $FunzioneId=4;
                $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                if (sizeof($permesso))
                   edit($_REQUEST['ruoloId']);
                else
                    Errors::$ErrorePermessiModuloFunzione;    
                // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
                break;
                
            case "step":
                $step=$_REQUEST['step'];               
                switch($step){
                   case "1":
                        $FunzioneId=4;
                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                        if (sizeof($permesso))
                           edit($_REQUEST['ruoloId']);
                        else
                            Errors::$ErrorePermessiModuloFunzione;
                        exit();
                       break;
                   case "2":                       
                        $FunzioneId=4;
                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                        if (sizeof($permesso))
                           ruoli($_REQUEST['ruoloId']);
                        else
                            Errors::$ErrorePermessiModuloFunzione;      
                        exit();
                       break;
                   case "3":
                       
                       break;
                }
            
            default:
                $FunzioneId=1;
                show_list();    
                        // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
                break;
        }		
    } // end verifica permessi
    else {
        Errors::$ErrorePermessiModulo;            
    }
} 
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>