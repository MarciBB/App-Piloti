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


$ModuloId=13;


function carica_menu_operatore($step_corrente,$mod,$idOperatore)
{
global $abilita_modifica,$mediazione_wizard,$db, $dizionario;
$mediazione_wizard->conn=$db;
//$menu=$mediazione_wizard->getMenuWizard();

   
$menu=array(
    1=>$dizionario['operatore']['dati_generali'],
    2=>$dizionario['operatore']['ruoli']
    //3=>"Statistiche e log"
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
                                    <a href="javascript:void(0);" onclick="loadMediazioneStep('operatore','operatore.php?do=step&step=<?=$contamenu?>&operatoreId=<?=$idOperatore?>',this);" title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?></a>        
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
global $HtmlCommon,$user,$db, $dizionario;  
/*
$db= new Database();
$db->connect();  */
$step=1;
$mod=1;


$page=new Form();  
$gestore=new Gestore();
$gestore->conn=$db;
$gestore->getGestoreAll($user->GestoreId);
$arr_gestori=$gestore->ArrGestore;

include_once("operatore_validator.php");       
$HtmlCommon->html_titolo_pagina($dizionario['operatore']['titolo_aggiungi']." "."",1,"operatore","operatore.php");
$HtmlCommon->html_titolo_box($dizionario['operatore']['titolo_aggiungi']);  
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['comune']['non_attivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

$arr_tipo[]= array("OperatoreTipoId" => '1',"Tipo" => $dizionario['operatore']['operatore']);


$arr_admin[]= array("IsAdminId" => '0',"IsAdmin" => $dizionario['generale']['no']);
$arr_admin[]= array("IsAdminId" => '1',"IsAdmin" => $dizionario['generale']['si']);




carica_menu_operatore($step,$mod,$OperatoreId);
?>

<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter">    
    <form id="application_form" name="application_form" method="post" action="#">
        <div id="elencosoggetti" class="brain_formModifica">
             <h2><span class="brain_colorh2"><?=$dizionario['operatore']['aggiungi']?></span></h2>
            <div class="brain_data-content" id="brain_data-content"> 
                <?
                $page->create_textbox_hidden("action","create");
                 $page->create_textbox_hidden("Operatore[OperatoreTipoId]","1");
                
                
                 print("<br style=\"clear:both;\"/>");
                /*$page->create_select("Tipo","Operatore[OperatoreTipoId]","OperatoreTipoId","brain_campoForm campiformBig",$arr_tipo,0,"OperatoreTipoId","Tipo",
                                              array(
                                                  "class"=>"'required'",
                                                  "onChange"=>"'javascript:getElencoMediatori(this);'"
                                                   ),1);*/
                
            
                
                print("<br style=\"clear:both;\"/>");
                
               if ($user->IsAdmin==1)
                {     
              $page->create_select($dizionario['operatore']['amministratore'],"Operatore[IsAdmin]","AdminId","brain_campiform campiformBig",$arr_admin,0,"IsAdminId","IsAdmin",array("class"=>"'required'"),1,"...");
                print("<br style=\"clear:both;\"/>");  
                }
                
                
                $page->create_textbox($dizionario['generale']['cognome'],"Cognome","Operatore[Cognome]","",1,"brain_campiform",array("class"=>"'required'"));           
                $page->create_textbox($dizionario['generale']['nome'],"Nome","Operatore[Nome]","",1,"brain_campiform",array("class"=>"'required'")); 
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox($dizionario['odc']['username'],"Username","Operatore[Username]","",1,"brain_campiform",array("class"=>"'required'"));
                print("<br style=\"clear:both;\"/>");
                $page->create_textbox_password($dizionario['odc']['password'],"Password","Operatore[Password]","",1,"brain_campiform",array("class"=>"'required'"));
                $page->create_textbox_password($dizionario['generale']['conferma_password'],"Password","confermaPassword","",1,"brain_campiform",array("class"=>"'required'"));
                print("<br style=\"clear:both;\"/>");           
                $page->create_textbox($dizionario['generale']['email'],"Email","Operatore[Email]","",1,"brain_campiform",array("class"=>"'email'"));
                print("<br style=\"clear:both;\"/>");                
                $page->create_select($dizionario['gestore']['gestore'],"Operatore[GestoreId]","GestoreId","brain_campiform campiformBig",$arr_gestori,$GestoreId,"GestoreId","RagioneSociale",
                array("class"=>"'required'"),1,$dizionario['operatore']['mess_accesso']);                        
                print("<br style=\"clear:both;\"/>");
                $page->create_select($dizionario['generale']['stato'],"Operatore[Stato]","StatoId","brain_campiform campiformBig",$arr_stato,1,"StatoId","Stato",array("class"=>"'required'"),1);
                print("<br style=\"clear:both;\"/>");
                ?>
            </div>
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
$db->close();
}


function edit() {
    include_once("operatore_validator.php");      
    $OperatoreId=$_REQUEST['operatoreId'];
    
    global $HtmlCommon,$user,$db, $dizionario;
    
    $step=1;
    $mod=0;
    /*
    $db= new Database();
    $db->connect();  */
    $page=new Form();  
    $gestore=new Gestore();
    $gestore->conn=$db;
    $gestore->getGestoreAll($user->GestoreId);
    $arr_gestori=$gestore->ArrGestore;
    
    $operatore=new Operatore();
    $operatore->conn=$db;
    $operatore->inizializza($OperatoreId);
    
    $GestoreId=$operatore->GestoreId;
    $Gestore=$operatore->Gestore;
    $Cognome=$operatore->Cognome;
    $Nome=$operatore->Nome;
    $Username=$operatore->Username;
    $Password=$operatore->Password;
    $Email=$operatore->Email;
    $Stato=$operatore->Stato;
    $OperatoreTipoId=$operatore->OperatoreTipoId;
    if ($Cognome) $IntestCorrente=strtoupper($Cognome." ".$Nome);     
    
    $HtmlCommon->html_titolo_pagina("",1,"operatore","operatore.php");
    $HtmlCommon->html_titolo_box ($dizionario['operatore']['titolo_edita']." - ".$IntestCorrente);
    $arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattivo']);
    $arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);
    
    $arr_tipo[]= array("OperatoreTipoId" => '1',"Tipo" => $dizionario['operatore']['operatore']);
    
    
    
    $arr_admin[]= array("IsAdminId" => '0',"IsAdmin" => $dizionario['generale']['no']);
    $arr_admin[]= array("IsAdminId" => '1',"IsAdmin" => $dizionario['generale']['si']);
    
    $IsAdmin=$operatore->IsAdmin;
    
    carica_menu_operatore($step,$mod,$OperatoreId);
    
    
    ?>
    <div class="brain_boxLeft wizart" id="brain_mediazioneformcenter">    
        <form id="application_form" name="application_form" method="post" action="#">
            <div id="elencosoggetti" class="brain_formModifica">
                <h2><span class="brain_colorh2"><?=$dizionario['operatore']['modifica_operatore']?> <?=$IntestCorrente?> - </span><?=$dizionario['operatore']['dati_generali']?></h2>
                <div class="brain_data-content" id="brain_data-content">
                    <?php
                    $page->create_textbox_hidden("action","update");
                    $page->create_textbox_hidden("idpost",$OperatoreId);
                    $page->create_textbox_hidden("Operatore[OperatoreTipoId]",1);
    
                    print("<br style=\"clear:both;\"/>");
                    /*$page->create_select("Tipo","Operatore[OperatoreTipoId]","OperatoreTipoId","brain_campoForm campiformBig",$arr_tipo,$OperatoreTipoId,"OperatoreTipoId","Tipo",
                                                  array(
                                                      "class"=>"'required'",
                                                      "onChange"=>"'javascript:getElencoMediatori(this);'"
                                                       ),1);*/
                    
               
                      print("<br style=\"clear:both;\"/>");
                   
                    
                   
                    if ($user->IsAdmin==1) {
                        $page->create_select($dizionario['operatore']['amministratore'],"Operatore[IsAdmin]","AdminId","brain_campiform campiformBig",$arr_admin,$IsAdmin,"IsAdminId","IsAdmin",array("class"=>"'required'"),1,"...");
                        print("<br style=\"clear:both;\"/>");  
                    }
        
                    print("<br style=\"clear:both;\"/>");
                    $page->create_textbox($dizionario['generale']['cognome'],"Cognome","Operatore[Cognome]",$Cognome,1,"brain_campiform",array("class"=>"'required'"));                
                    $page->create_textbox($dizionario['generale']['nome'],"Nome","Operatore[Nome]",$Nome,1,"brain_campiform",array("class"=>"'required'")); 
                    print("<br style=\"clear:both;\"/>");
                    $page->create_textbox($dizionario['generale']['email'],"Email","Operatore[Email]","$Email",1,"brain_campiform campiformBig",array("class"=>"'email'"));
                    print("<br style=\"clear:both;\"/>");
                    ?>
                    <div class="brain_campiform">
                        <label for="username"><?=$dizionario['odc']['username']?></label><br />
                        <span class="username"><?=$Username?></span>
                    </div>
                    <br style="clear:both;"/>
                    <div id="cambia_pwd_1" class="cambia_pwd_1" style="display:block;">
                    	<a class="seleziona_comune" href="javascript:void(0);" onclick="set_password();"><?=$dizionario['operatore']['cambia_password']?></a>    
                    </div>
                    <div id="cambia_pwd_2" class="cambia_pwd_2" style="display:none;">                
                        <?php           
                        $page->create_textbox_password($dizionario['odc']['password'],"password","pass","",1,"brain_campiform","");
                        $page->create_textbox_password($dizionario['generale']['conferma_password'],"co_password","confermaPassword","",1,"brain_campiform","");
                        ?>
                    	<a class="seleziona_comune" href="javascript:void(0);" onclick="unset_password();">Annulla operazione</a>        
                    </div>    
                    <?php
                    print("<br style=\"clear:both;\"/>");                                        
                    $page->create_select($dizionario['gestore']['gestore'],"Operatore[GestoreId]","GestoreId","brain_campiform campiformBig",$arr_gestori,$GestoreId,"GestoreId","RagioneSociale",
                    array("class"=>"'required'"),1,$dizionario['operatore']['mess_accesso']);?>               
                    <?php
                    print("<br style=\"clear:both;\"/>");    
                    $page->create_select($dizionario['generale']['stato'],"Operatore[Stato]","StatoId","brain_campiform campiformBig",$arr_stato,$Stato,"StatoId","Stato",array("class"=>"'required'"),1);
                    print("<br style=\"clear:both;\"/>");
                    
                    ?>	
                </div> 
                
            </div>
          <div class="divSubmit">        
            <!--<input type="submit" name="elimina" class="brain_cancella" id="elimina" value="elimina operatore" onClick="javascript:$('#action').val('elimina_operatore');">-->        
            <input type="submit" name="Salva" class="brain_salva" id="Salva" value="salva">
            <a href="javascript:void(0);" onclick="loadMainContent('operatore','operatore.php',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
            
          </div>
        </form>
    </div>
    
    
    <?
    $db->close();
}


function show_list()
{

global $user,$HtmlCommon,$db,$ModuloId, $dizionario;

$HtmlCommon->html_titolo_pagina($dizionario['operatore']['titolo_elenco_op']);
$HtmlCommon->html_titolo_box($dizionario['operatore']['titolo_elenco_op']);
$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
/*$db= new Database();
$db->connect();*/
include_once("operatore_validator.php"); 
include_once("operatore_datatable.php");
if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi','operatore','operatore.php?do=add',$dizionario['operatore']['aggiungi_operatore']);
?> 
<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    <thead>
        <tr class="brain_tabellaTr">
            <th width="5%"><?=$dizionario['generale']['stato']?></th>
            <th width="15%"><?=$dizionario['generale']['cognome']?></th>
            <th width="15%"><?=$dizionario['generale']['nome']?></th>
            <th width="20%"><?=$dizionario['odc']['username']?></th>
             <th width="10%"><?=$dizionario['generale']['tipo']?></th>
            <th width="10%"><?=$dizionario['generale']['email']?></th>
            <th width="15%"><?=$dizionario['gestore']['gestore']?></th>
            <th width="10%"><?=$dizionario['operatore']['azioni']?></th>                     
        </tr>
        <tr class="brain_tabellaFilter">
            <th><span></span><input type="hidden" /></th> 
            <th><span class="hidden"></span><input type="text" /></th> 
            <th><span class="hidden"></span><input type="text" /></th> 
            <th><span class="hidden"></span><input type="text" /></th>
             <th><span></span><input type="hidden" /></th> 
             <th><span class="hidden"></span><input type="text" /></th>
             
            <th><span></span><input type="hidden" /></th> 
            <th><input type="hidden" /></th> 
        </tr>
    </thead>
    <tbody>         
        <tr>
            <td colspan="8" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
        </tr>
    </tbody>
	<tfoot> 
        <tr>
            <td colspan="8"></td>
        </tr> 
    </tfoot> 
</table>
<?   
$db->close();
}


function statistiche()
{
include_once("operatore_validator.php");      
$OperatoreId=$_REQUEST['operatoreId'];

global $HtmlCommon,$user,$db, $dizionario;

$step=1;
$mod=0;
/*
$db= new Database();
$db->connect();  */
$page=new Form();  
$gestore=new Gestore();
$gestore->conn=$db;
$gestore->getGestoreAll($user->GestoreId);
$arr_gestori=$gestore->ArrGestore;

$operatore=new Operatore();
$operatore->conn=$db;
$operatore->inizializza($OperatoreId);

$GestoreId=$operatore->GestoreId;
$Gestore=$operatore->Gestore;
$Cognome=$operatore->Cognome;
$Nome=$operatore->Nome;
$Username=$operatore->Username;
$Password=$operatore->Password;
$Email=$operatore->Email;
$Stato=$operatore->Stato;
if ($Cognome) $IntestCorrente=strtoupper($Cognome." ".$Nome);     

$HtmlCommon->html_titolo_pagina("",1,"operatore","operatore.php");
$HtmlCommon->html_titolo_box ($dizionario['operatore']['titolo_edita']." - ".$IntestCorrente);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

carica_menu_operatore($step,$mod,$OperatoreId);
?>
<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter">    
    <form id="application_form" name="application_form" method="post" action="#">
        <div id="elencosoggetti" class="brain_formModifica">
            <h2><span class="brain_colorh2"><?=$dizionario['operatore']['modifica_operatore']?> <?=$IntestCorrente?> - </span><?=$dizionario['operatore']['dati_generali']?></h2>
            <div class="brain_data-content" id="brain_data-content">
                <h3>Statistiche</h3>
                <div class="boxStatistiche">
                        <span><?=$dizionario['operatore']['dati_generali']?> <strong>10/06/2011 12:32</strong></span>
                        <span><?=$dizionario['operatore']['ultima_aggiunta']?> <strong>10/06/2011 12:59 - <?=$dizionario['operatore']['anagrafica']?></strong></span>
                        <span><?=$dizionario['operatore']['ultima_modifica']?> <strong>10/06/2011 12:59 - <?=$dizionario['operatore']['anagrafica']?></strong><br /><br /><br /></span>

                        <span><?=$dizionario['operatore']['tot_accessi']?> <strong>230</strong></span>
                        <span><?=$dizionario['operatore']['tot_aggiunte']?> <strong>47</strong></span>
                        <span><?=$dizionario['operatore']['tot_modifiche']?> <strong>364</strong></span>
                </div>
            </div> <!-- fine brain data content -->

            
        </div>    
    </form>
</div>
<div class="brain_stato-mediazione">
        <h3><?=$dizionario['operatore']['stato_operatore']?></h3>
        <form id="CambiaStatoOperatoreId">
        <?
        $page->create_textbox_hidden("idpost",$OperatoreId);
        $page->create_textbox_hidden("action","cambia_stato_operatore");      
        if($Stato==1) 
            echo("<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"Attivo\"></i>");
        else
            echo("<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"Disattivo\"></i>");
        ?>
        <p><?=$dizionario['operatore']['mess_stato']?></p>
        <?
        $page->create_select($dizionario['generale']['stato'],"StatoOperatore","StatoId","brain_campoForm",$arr_stato,$Stato,"StatoId","Stato",
                                     array("class"=>"'required'"),1);        ?>
        
        <div class="CambiaStatoMediazioneSubmit">
            <input class="brain_CambiaStato" type="submit" name="CambiaStato" value="Cambia" />
        </div> 
        </form>
</div>

<?
$db->close();
}

function ruoli()
{
include_once("operatore_validator.php");      
$OperatoreId=$_REQUEST['operatoreId'];

global $HtmlCommon,$user,$db, $dizionario;

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

$operatore=new Operatore();
$operatore->conn=$db;
$operatore->inizializza($OperatoreId);

$GestoreId=$operatore->GestoreId;
$Gestore=$operatore->Gestore;
$Cognome=$operatore->Cognome;
$Nome=$operatore->Nome;
$Username=$operatore->Username;
$Password=$operatore->Password;
$Email=$operatore->Email;
$Stato=$operatore->Stato;
if ($Cognome) $IntestCorrente=strtoupper($Cognome." ".$Nome);     

$HtmlCommon->html_titolo_pagina("",1,"operatore","operatore.php");
$HtmlCommon->html_titolo_box ($dizionario['operatore']['titolo_edita']." - ".$IntestCorrente);
$arr_stato[]= array("StatoId" => '0',"Stato" => $dizionario['generale']['disattivo']);
$arr_stato[]= array("StatoId" => '1',"Stato" => $dizionario['generale']['attivo']);

$ruolo=new Ruolo();
$ruolo->conn=$db;
$ruoli_attivi=$ruolo->getRuoliAttivi($OperatoreId,$user->OdcId);
foreach ($ruoli_attivi as &$value) {
    if($value['Attivo']) $ruoli.=$value['RuoloId'].",";
}
$ruoli=substr($ruoli,0,strlen($ruoli)-1);
if($ruoli!="")
    $contenuto_merge=$ruolo->getMergeRuoli($ruoli);
else
    $contenuto_merge=$dizionario['operatore']['no_ruolo'];

carica_menu_operatore($step,$mod,$OperatoreId);
?>
<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter">    
    <form id="application_form" name="application_form" method="post" action="#">
        <div id="elencosoggetti" class="brain_formModifica">
            <h2><span class="brain_colorh2"><?=$dizionario['operatore']['modifica_operatore']?> <?=$IntestCorrente?> - </span><?=$dizionario['operatore']['dati_generali']?></h2>
            <p><?=$dizionario['operatore']['seleziona_ruolo']?></p>
            <div class="brain_data-content" id="brain_data-content">
                 <?
                $page->create_textbox_hidden("action","update_ruoli");
                $page->create_textbox_hidden("idpost",$OperatoreId);
                ?>
                
                <div class="brain_colLeft">
                        <h3><?=$dizionario['operatore']['ruoli_attivi']?></h3>
                        <?
                        foreach ($ruoli_attivi as &$value) {
                            ?>
                            <input type="checkbox" id="ruoliAttivi" onClick="javascript:mergeRuoli();" name="RuoliAttivi[]" <?if($value['Attivo']) echo("checked='checked'");?> value="<?=$value['RuoloId']?>" >
                            <label for="ruolo1"><strong><?=$value['RuoloNome']?></strong><br /><?=$value['RuoloDescrizione']?></label>
                            <br style="clear:both;"/>
                            
                        <?
                        }
                        ?>
                </div>
                <div class="brain_colRight">
                    <h3><?=$dizionario['operatore']['permessi']?></h3>
                    <div class="boxContenitoreTabellaOperatore" id="boxContenitoreTabella">
                       <?=$contenuto_merge?> 
                    </div>                
                </div>
                
            </div> <!-- fine brain data content -->

        </div>
        <div class="divSubmit">                    
        <input type="submit" name="Salva" class="brain_salva" id="Salva" value="salva">
        <a href="javascript:void(0);" onclick="loadMainContent('operatore','operatore.php',this);" title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a>
        <select name="application_formTrackList" id="application_formTrackList" multiple="multiple" class="changeListClass" style="display: none;"></select></form>
      </div>
    </form>
</div>
 
<div class="brain_stato-mediazione">
        <h3><?=$dizionario['operatore']['stato_operatore']?></h3>
        <form id="CambiaStatoOperatoreId">
        <?       
        if($Stato==1) 
            echo("<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"".$dizionario['generale']['attivo']."\"></i>");
        else
            echo("<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"".$dizionario['generale']['disattivo']."\"></i>");
        ?>
        <p><?=$dizionario['operatore']['mess_stato']?></p>        
                
        </form>
</div>

<?
$db->close();
}

function modifica_password()
{
   
global $user,$db,$HtmlCommon, $dizionario;

$page=new Form();
 $dt=new DT();

$HtmlCommon->html_titolo_box($dizionario['operatore']['titolo_mod_password']);

include_once("operatore_validator_stop.php");
?>
<div class="brain_boxLeft wizart" id="brain_mediazioneformcenter">    
    <form id="application_form" name="application_form" method="post" action="#">
        <div id="elencosoggetti" class="brain_formModifica" style="min-height: 170px;">
            <h2><span class="brain_colorh2"><strong><?=$user->Cognome." ".$user->Nome;?> -</strong></span> <?=$dizionario['operatore']['titolo_mod_password']?></h2>
            <div class="brain_data-content" id="brain_data-content">
                <?
                $page->create_textbox_hidden("action","mod_password");
                $page->create_textbox_hidden("idpost",$user->OperatoreId);
                ?>                
                <br style="clear:both;"/>
                
                <div id="cambia_pwd_2" class="cambia_pwd_2">                
                <?           
                $page->create_textbox_password($dizionario['operatore']['vecchia_psw'],"v_password","v_password","",1,"brain_campiform",array("class"=>"'required'"));                
                print("<br style=\"clear:both;\"/>");
                
                
                $page->create_textbox_password($dizionario['operatore']['nuova_psw'],"password","Operatore[Password]","",1,"brain_campiform",array("class"=>"'required'"));
                $page->create_textbox_password($dizionario['generale']['conferma_password'],"co_password","confermaPassword","",1,"brain_campiform",array("class"=>"'required'"));                
                print("<br style=\"clear:both;\"/>");
                ?>	
            </div> 
            
        </div>
        <? spara_pulsanti_wizard_box() ?>
    </form>
</div>

 
<?  
 $db->Close();   
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
            case "mod_password":
                modifica_password();
                break;
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
                   edit();
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
                           edit();
                        else
                            Errors::$ErrorePermessiModuloFunzione;
                        exit();
                       break;
                   case "2":                       
                        $FunzioneId=4;
                        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                        if (sizeof($permesso))
                           ruoli();
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
// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}
?>