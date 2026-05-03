<?php
/**
* @package as_reportool - class for Html Report composing & printing
* @name as_reportool.php
* @author Alexander Selifonov <as-works@narod.ru>
* @link http://www.selifan.ru
* @license http://www.gnu.org/copyleft/gpl.html
* modified 06.10.2011
* @version 1.11.010
**/
define('ASREPT_ERR_WRONGXMLFILE', -101);
define('ASREPT_ERR_WRONGFORMAT', -102);
define('ASREPT_ERR_WRONGPHP', -201);
/**
* @desc CReportField - one reporting field definition
*/

require_once('as_dbutils.php'); # db access wrapper class
/**
* report parameter definition class
*/
class CReportParam {
  var $id;
  var $inputtype;
  var $type;
  var $prompt;
  var $fillfunc;
  var $mandatory;
  var $expression = '';
  function CReportParam($id,$itype='text', $type='char', $prompt='',$ffunc='',$expr='',$mand=0) {
    $this->id=$id;
    $this->inputtype=$itype;
    $this->type=$type;
    $this->prompt = empty($prompt) ? $id : $prompt;
    $this->fillfunc=$ffunc;
    $this->expression = $expr;
    $this->mandatory = $mand;
  }
}

class CReportField {
  var $fieldno;
  var $title; # title for this field
  var $formid = '';
  var $summable;
  var $fconv; # '' or function name to make presentation string
  var $format; # r=right, c=center, money= "money fmt",...
  function CReportField($title='',$summable=0, $fconv='', $format='') {
    $this->title=$title;
    $this->summable=$summable;
    $this->fconv=$fconv;
    $this->format=$format;
  }
}
class CReporTool { // main class def.
  var $_title = '';
  var $_query = array(); # main query, that gets all info for report
  var $_srcfile = '';
  var $_headers = ''; # header rows for the top header
  var $_rfields = array(); # fields to draw or calculate
  var $_grpfields = array();
  var $_sumfields = array();
  var $_totals = array(); # internal use - accumulated totals
  var $_summary = array();
  var $_multitotals = '';
  var $_grp_curval = array();
  var $_supress_eg = true; # supress printing headers and subtotals for zeroed grouping field values
  var $_debug = 0; # set to positive integer N to break reporting after N-th line
  var $_summarytitle = ''; # if not empty, summary totals line will be printed at report's bottom
  var $_fontstyles = '';
  var $_rowcount = 0;
  var $errorcode = 0;
  var $_errormessage = 0;
  var $_delim_tho = ','; # delimiters for number_format
  var $_delim_dec = '.';
  var $_outcharset = '';
  var $_incharset = '';
  var $_nodefhead = 0;
  var $_suppresscss = false;
  var $_muids = array(); # in multi-totals case here will be saved all total "key" values
  var $_rownumbers = false; # print row � at first cell
  static $_backenduri = '';
  var $_params = array(); # CReportParam object array
  /**
  * constructor
  *
  * @param string $filename passed XML file name to load report definition from
  * @return CReporTool
  */
  function CReporTool($par=false, $cset=null) {
      self::$_backenduri = $_SERVER['PHP_SELF'];
      if(is_array($par)) {
          if(isset($par['filename']))   $this->_srcfile    = trim($par['filename']);
          if(isset($par['outcharset'])) $this->_outcharset = trim($par['outcharset']);
          if(isset($par['backend']))    self::$_backenduri = trim($par['backend']);
          $this->_rownumbers = isset($par['rownumbers']) ? intval($par['rownumbers']):0;
      }
      elseif(is_scalar($par)) {
          $this->_srcfile = $par;
          $this->_outcharset = $cset;
      }

      if(is_string($this->_srcfile) && file_exists($this->_srcfile) && function_exists('simplexml_load_file')) {
          $this->LoadFromXml($this->_srcfile);
      }
  }
  function SetBackendURI($parm) { self::$_backenduri = $parm; }
  function LoadFromXml($filename, $outcharset=null) {
    # parse xml file and load all report parameters
    ini_set('zend.ze1_compatibility_mode', 0);
    if(!function_exists('simplexml_load_file')) {
        $this->_errorcode = ASREPT_ERR_WRONGPHP;
    }
    $xml = @simplexml_load_file($filename);
    $this->_outcharset = ($outcharset!==null) ? $outcharset : 'UTF-8';
    if(!$xml) {
#      echo '<pre>'.htmlspecialchars(file_get_contents($filename)).'</pre>';
      $this->_errorcode = ASREPT_ERR_WRONGXMLFILE;
      $this->_errormessage = "$filename has wrong XML format or non XML at all!";
      echo "debug print: error {$this->_errorcode} - {$this->_errormessage}<br />";
      return false;
    }
    $this->_errorcode=0;  $this->_errormessage='';
    $goodfmt = false;
    if(isset($xml->headings)) {
      $header = $this->DecodeCharValue($xml->headings);
      $this->SetHeadings($header);
    }
    foreach($xml->children() as $cid=>$obj) {
      switch($cid) {
        case 'title':
          $this->_title = $this->DecodeCharValue($obj);
          break;
        case 'parameter':
          $id = isset($obj['id'])? trim($obj['id']) : '';
          $itype = isset($obj['inputtype'])? trim($obj['inputtype']) : 'text';
          $type = isset($obj['type'])? trim($obj['type']) : '';
          $prompt = isset($obj['prompt'])? $this->DecodeCharValue($obj['prompt']) : $id;
          $ffunc = isset($obj['fillfunc'])? trim($obj['fillfunc']) : '';
          $expression = "$obj"; # tag value is "expression" to put into SQL, "%value%" will be replaced with user entered value
          if($expression=='') $expression="$id=%value%";
          $mand = isset($obj['mandatory'])? trim($obj['mandatory']) : 0;
          if(!empty($id)) $this->_params[] = new CReportParam($id,$itype,$type,$prompt,$ffunc,$expression,$mand);
          break;

        case 'paramform':
          $id = isset($obj['id'])? trim($obj['id']) : '';
          $this->formid = $id;
          break;
        case 'query':
          $this->SetQuery($obj);
          break;
        case 'grpfield':
          $fldid = isset($obj['name'])?  trim($obj['name']) : '';
          $fconv = isset($obj['fconv'])? trim($obj['fconv']) : '';
          $title = isset($obj['title'])? $this->DecodeCharValue($obj['title']) : '';
          $ttitle = isset($obj['totaltitle'])? $this->DecodeCharValue($obj['totaltitle']) : '';
          if(!empty($fldid)) { $this->AddGroupingField($fldid,$fconv,$title,$ttitle); }
          break;
        case 'field':
          $fldid = isset($obj['name'])?  trim($obj['name']) : '';
          $title = isset($obj['title'])? $this->DecodeCharValue($obj['title']) : '';
          $summa = isset($obj['summable'])? trim($obj['summable']) : '';
          $fconv = isset($obj['fconv'])? trim($obj['fconv']) : '';
          $fmt = isset($obj['format'])? trim($obj['format']) : '';
          if(!empty($fldid)) {
            $this->AddField($fldid,$title,$summa,$fconv, $fmt);
            $goodfmt = true;
          }
          break;
        case 'nodefaultheadings':
          $this->_nodefhead = isset($obj['value'])? trim($obj['value']) : 0;
          break;
        case 'fontstyles':
          $this->_fontstyles = (!empty($obj['value']))? $obj['value'] : '';
          break;
        case 'delimiters':
          $dec = (!empty($obj['decimal']))? $obj['decimal'] : '';
          $tho = (!empty($obj['thousand']))? $obj['thousand'] : '';
          if($dec) $this->_delim_dec = $dec;
          if($tho) $this->_delim_tho = $tho;
          break;
        case 'multitotals':
          $this->_multitotals = isset($obj['byfield'])? trim($obj['byfield']) : '';
          break;
        case 'summary':
          $title = (!empty($obj['title']))? $this->DecodeCharValue($obj['title']) : 'summary';
          $this->SetSummary($title);
          break;
      }
    }

    if(!$goodfmt) {
      $this->_errorcode = ASREPT_ERR_WRONGFORMAT;
      $this->_errormessage = "$filename is not AS_REPORTOOL or empty XML file !";
    }
    unset($xml);
    return true;
  }
  function DecodeCharValue($strg) {
    $ret = $strg;
    if($this->_outcharset!='' && $this->_outcharset!='UTF-8'  && function_exists('mb_convert_encoding'))
        $ret = @mb_convert_encoding($ret,$this->_outcharset,'UTF-8');
    return $ret;
  }
  function SetCharSet($par) {
    $this->_outcharset=strtoupper($par);
    if(count($this->_rfields)>0 && $this->_outcharset!='' && $this->_incharset!='' && $this->_outcharset!=$this->_incharset)
    {
      @mb_convert_variables($this->_outcharset,'UTF-8',$this->_rfields, $this->_grpfields);
      $this->_summarytitle = mb_convert_encoding($this->_summarytitle,$this->_outcharset,'UTF-8');
    }
  }
  /**
  * register one field to draw in report page
  *
  * @param mixed $fldid field name in the query
  * @param mixed $fldtitle title in the headers for the field
  * @param mixed $summable if not 0, make sub-totals for this field
  * @param mixed $fconv function name for converting field value to something readable, if needed (name for id etc.)
  */
  function AddField($fldid,$fldtitle='',$summable=0,$fconv='', $format='') {
    # if(empty($fldtitle)) $fldtitle=$fldid;
    $this->_rfields[$fldid] = new CReportField($fldtitle,$summable,$fconv,$format);
    if($summable) $this->_sumfields[] = $fldid;
  }
  function SetHeaders($par,$no_defaultheader=0) { $this->_headers = $par; $this->_nodefhead=$no_defaultheader; }
  function SetHeadings($par,$no_defaultheader=0) { $this->_headers = $par; $this->_nodefhead=$no_defaultheader; }
  function SetQuery($sqlquery) { $this->_query=array();  $this->_query[]=$sqlquery; }
  function AddQuery($sqlquery) { $this->_query[]=$sqlquery; }
  function SetSummary($par='Total summary:') { $this->_summarytitle = $par; }
  function SetFontStyles($par='') { $this->_fontstyles = $par; }
  function AddGroupingField($fldid,$fconv='',$title='',$totaltitle='') {
    $this->_grpfields[$fldid] = array('fconv'=>$fconv,'title'=>$title,'ttitle'=>$totaltitle);
  }

  /**
  * sets decimal and thousand delimiters for number_format()
  *
  * @param mixed $dec
  * @param mixed $tho
  */
  function SetNumberDelimiters($dec,$tho='') {
    $this->_delim_dec = $dec;
    $this->_delim_tho = $tho;
  }

  function SetMultiTotals($fieldname) { $this->_multitotals = $fieldname; }

  function SuppressCss($par=true) { $this->_suppresscss = $par; }
  /**
  * runs SQL query and echoes resulting report
  *
  */
  function DrawReport($title='') {
    global $as_dbengine;
    $this->_grp_curval = array();
    $this->_totals = array();
    if(empty($title)) $title = $this->_title;
    foreach($this->_grpfields as $grpfld=>$grp) $this->_grp_curval[$grpfld]='{*}';
    foreach($this->_rfields as $fid=>$fld) { # init subtotals values array
      $this->_totals[$fid] = array();
      foreach($this->_sumfields as $sumf) { $this->_totals[$fid][$sumf] = ($this->_multitotals)? array():0; }
    }
    foreach($this->_sumfields as $sumf) { $this->_summary[$sumf] = ($this->_multitotals)? array():0; }
    $fnt = ($this->_fontstyles=='')? '': $this->_fontstyles;
    if(!$this->_suppresscss) {
?>
<style type="text/css">
/** styles for report drawing **/
table.rep_table { width:80%; <?=$fnt?>}
   td.rep_ltrb { background-color:#333; border: 1px solid #D6D6D6; padding: 10px; text-align:left; color: #FFF; font-size:13px !important; font-weight: bold; <?=$fnt?>}
   td.rep_lrb { border-left:1px solid #D6D6D6; border-top:none; border-right: 1px solid #D6D6D6; border-bottom: 1px solid #D6D6D6; padding: 10px; text-align:left; font-size:13px !important; <?=$fnt?>}
      td.rep_lrbBold { background-color:#fff; border-left:1px solid #D6D6D6; border-top:none; border-right: 1px solid #D6D6D6; border-bottom: 1px solid #D6D6D6; padding: 10px; text-align:left; font-weight: bold; font-size:13px !important; <?=$fnt?>}
	  td.rep_lrbBoldTot { background-color:#CCD6E0; border-left:1px solid #D6D6D6; border-top:none; border-right: 1px solid #D6D6D6; border-bottom: 1px solid #D6D6D6; padding: 10px; text-align:left; font-weight: bold; font-size:13px !important; <?=$fnt?>}
   td.rep_rb  { border-left:none; border-top:none; border-right: 1px solid #D6D6D6; border-bottom: 1px solid #D6D6D6; padding: 10px; text-align:left; font-size:13px !important; <?=$fnt?> }
   /**td.rep_lrb.num { text-align: left!important;; font-size:13px !important; }**/
   td.num { text-align: right; font-size:13px !important; }
   td.numBold { text-align: right; font-size:13px !important; font-weight: bold;}
   td.cnt { text-align: center; font-size:13px !important; }
   td.newgroup { background: #EAEAEA; font-weight: bold; font-size:13px !important; <?=$fnt?> }
</style>
<?php
    }
    $colsp = count($this->_rfields);
    echo "<h4 style='text-align:left; font-weight:bold; padding-bottom:10px; padding-left:9px;'>$title</h4>\n<table class=\"report_excel\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"80%\">{$this->_headers}";

    # draw report field headers
    if(empty($this->_nodefhead)) {
      $thead = '<tr>';
      if($this->_rownumbers) $thead .= "<td class='rep_ltrb cnt'>�</td>";
      foreach($this->_rfields as $fid=>$fld) {
        if($fld->title!='') $thead .="<td class=\"rep_ltrb\">{$fld->title}</td>";
      }
      $thead .='</tr>';
      echo $thead;
    }
    $iquery = -1;
    $rlink = -1;

    $this->_rowcount = 0;
    while(true) {
      if($rlink===-1) {
        $iquery++;
        if(!isset($this->_query[$iquery])) break;
        $rlink = $as_dbengine->sql_query($this->_query[$iquery]);
        if(!is_resource($rlink)) { $rlink=-1; continue; } # try next query
      }
      $r=$as_dbengine->fetch_assoc($rlink);
      if(!$r) { $as_dbengine->free_result($rlink); $rlink = -1; continue; } # process next query in queue
      # check if some "grouping" field values changed, draw subtotals if so
      $this->_rowcount++;
      
      if($this->_multitotals && !in_array($r[$this->_multitotals],$this->_muids)) $this->_muids[] = $r[$this->_multitotals];
      $grp_ffield = ''; # becomes a name of the "major" grouping field that has changed
      foreach($this->_grp_curval as $fid=>$curval) {
        if(isset($r[$fid]) && $curval !== $r[$fid]) {
          if($curval!=='{*}') { # draw and reset all accumulated sub-totals
            $this->__DrawSubtotals($fid);
          }
          $this->_grp_curval[$fid]=$r[$fid];
          $grp_ffield=$fid;
          $b_tmp = false;
          foreach($this->_grpfields as $grid=>$grp) {
            if($grid==$grp_ffield) { $b_tmp=true; continue; }
            if($b_tmp) $this->_grp_curval[$grid]='{*}';
          }
          if(!$b_tmp) $grp_ffield='';
          break;
        }
      }
      if($grp_ffield!=='') {
        # draw headers for next sub-total group
        if($this->_debug>0 && $this->_rowcount >= $this->_debug) break; # debug stop
        $ingrp = false;
        $leftoff='';
        foreach($this->_grpfields as $fid=>$grp) {
          if($fid==$grp_ffield) $ingrp=true;
          if($ingrp) {
            if(($this->_supress_eg) && empty($r[$fid])) continue; # don't print header for empty grouping value
            $fldval = (!empty($grp['fconv']) && function_exists($grp['fconv']))? call_user_func($grp['fconv'],$r[$fid],$r): $r[$fid];
            $txt = $grp['title'].' '.$fldval;
            $colsp = count($this->_rfields);
            echo "<tr><td class=\"rep_lrb newgroup\" colspan=\"$colsp\">{$leftoff}{$txt}</td></tr>\n";
            $this->_grp_curval[$fid]=$r[$fid];
          }
          $leftoff.=' &nbsp;&nbsp;';
        }
      }
      # now draw "normal" report row
      $strow = '<tr>';
      $colno = 0;
      if($this->_rownumbers) $strow .= "<td class='rep_lrb cnt'>{$this->_rowcount}</td>";
      foreach($this->_rfields as $fid=>$fld) {
        $cls = ((++$colno>1) or $this->_rownumbers) ? 'rep_rb':'rep_lrb';
        if(in_array($fld->format, array('c','d'))) $cls .= ' cnt';
        elseif(in_array($fld->format, array('r','right','money','i'))) $cls .= ' num';

        if(isset($r[$fid])) {
          if (!empty($fld->fconv) && function_exists($fld->fconv)) $value=call_user_func($fld->fconv,$r[$fid],$r);
          else $value = $this->FormatValue($fid,$r[$fid]);
          if(in_array($fid,$this->_sumfields)) {
            if($this->_multitotals) {
              $muid=$this->_multitotals;
              if(isset($this->_summary[$fid][$r[$muid]])) $this->_summary[$fid][$r[$muid]]+=floatval($r[$fid]);
              else $this->_summary[$fid][$r[$muid]]=floatval($r[$fid]);
            }
            else $this->_summary[$fid] +=floatval($r[$fid]);
            # if(!isset($this->_totals[$fid])) $this->_totals[$fid] = 0;
            foreach($this->_grpfields as $grpid=>$gr) {
              if(empty($this->_multitotals)) {
                if(!isset($this->_totals[$grpid][$fid])) $this->_totals[$grpid][$fid]=0;
                $this->_totals[$grpid][$fid] += floatval($r[$fid]);
              }
              else { #<5>
                $mulid = $r[$this->_multitotals];
                if(!isset($this->_totals[$grpid][$fid][$mulid])) $this->_totals[$grpid][$fid][$mulid]=floatval($r[$fid]);
                else $this->_totals[$grpid][$fid][$mulid] += floatval($r[$fid]);
              } #<5>
            }
#            $cls .= ' num';
          }
        }
        else {
          $value = @function_exists($fld->fconv)? call_user_func($fld->fconv,0,$r) : "($fid)";
        }
        if($value==='') $value='&nbsp;';
        $strow .= "<td class=\"$cls\">$value</td>";
      }
      $strow .= "</tr>\n";
      echo $strow;
    }
    if(is_resource($rlink)) $as_dbengine->free_result($rlink);
    if(count($this->_grpfields)) $this->__DrawSubtotals();
    if(!empty($this->_summarytitle)) $this->__DrawSummaryTotals();
    echo "</table>";
  }
  /**
  * internal function, draws all sub-totals from lowest level to $gfield level
  *
  * @param string $gfield upper level grouping field
  */
  function __DrawSubtotals($gfield='') {
    $grfields = array_reverse($this->_grpfields); # go up from "inner" subtotal level
    $b_draw= true;
    foreach($grfields as $fid=>$grp) {
      # .. draw sub-total row;
      if(($this->_supress_eg) && empty($this->_grp_curval[$fid])) continue; # don't print subtotals if empty grouping value

      $ttval = $this->FormatValue($fid,$this->_grp_curval[$fid], $grp['fconv']);
      $ttpl = empty($grp['ttitle']) ? 'Total for %name%': $grp['ttitle'];
      $totitle = str_replace('%name%',$ttval,$grp['ttitle']);
      $cspan=0;
      foreach($this->_rfields as $fldid=>$fld) { if($fld->summable) break; $cspan++; }
      if($this->_rownumbers) $cspan++;
      if($cspan<1) $txt = "<tr><td class=\"rep_lrbBold\" colspan=10 >$totitle</td></tr><tr>";
      else $txt = "<tr><td class=\"rep_lrb\" colspan=\"$cspan\">$totitle</td>";

      $b_strt = false;
      foreach($this->_rfields as $flddid=>$fld) {
        if(!$fld->summable && !$b_strt) continue;
        if($fld->summable) {
          $b_strt = true;
          if($this->_multitotals) {
            $value='';
            foreach($this->_muids as $muid) $value .= ($value==''? '':'<br />').
             $this->FormatValue($flddid,(isset($this->_totals[$fid][$flddid][$muid])?$this->_totals[$fid][$flddid][$muid]:0));
          }
          else $value = $this->FormatValue($flddid,$this->_totals[$fid][$flddid]);
          $txt .= "<td class=\"rep_rb numBold\" nowrap=\"nowrap\">$value</td>";

          if($this->_multitotals) { foreach($this->_muids as $muid) $this->_totals[$fid][$flddid][$muid]=0; }
          else $this->_totals[$fid][$flddid] = 0;
        }
        elseif($flddid===$this->_multitotals) {
          $value = ''; foreach($this->_muids as $muid) $value.=($value===''?'':'<br />').$muid;
          $txt.= "<td class=\"rep_rb\">$value</td>";
        }
        else $txt .= '<td class="rep_rb num">&nbsp;</td>';
      }
      $txt .="</tr>\n";
      echo $txt;
      if(!empty($gfield) && $fid==$gfield) break; # upper level of subtotal reached
    }
  }
  function __DrawSummaryTotals() {
    $cspan=0;
    $title = str_replace('%rowcount%',$this->_rowcount,$this->_summarytitle);
    foreach($this->_rfields as $fldid=>$fld) { if($fld->summable) break; $cspan++; }
    $colcnt = count($this->_rfields);
    if($this->_rownumbers) { $colcnt++; $cspan++; }
    if($cspan<1) $txt = "<tr><td class=\"rep_lrbBoldTot\" colspan=\"$colcnt\" >$title</td></tr><tr>";
    else $txt = "<tr><td class=\"rep_lrb\" colspan=\"$cspan\">$title</td>";

    $b_strt = false;
    foreach($this->_rfields as $flddid=>$fld) {
      if(!$fld->summable && !$b_strt) continue;
      if($fld->summable) {
        $b_strt = true;
        if($this->_multitotals) {
          $value = '';
          foreach($this->_muids as $muid) $value .= ($value==''? '':'<br />').
           $this->FormatValue($flddid,(isset($this->_summary[$flddid][$muid])?$this->_summary[$flddid][$muid]:0));
        }
        else  $value = $this->FormatValue($flddid,$this->_summary[$flddid]);
        $txt .= "<td class=\"rep_rb numBold\" nowrap=\"nowrap\">$value</td>";
      }
      elseif($flddid===$this->_multitotals) {
        $value = ''; foreach($this->_muids as $muid) $value.=($value===''?'':'<br />').$muid;
        $txt.= "<td class=\"rep_rb cnt\">$value</td>";
      }
      else $txt .= '<td class="rep_rb cnt">&nbsp;</td>';
    }
    $txt .="</tr>\n";
    echo $txt;
  }
  function HasParameters(){ return (count($this->_params)>0); }
  function FormatValue($fieldid, $value) {
    $conv = !empty($this->_rfields[$fieldid]->fconv) ? $this->_rfields[$fieldid]->fconv :
     (!empty($this->_grpfields[$fieldid]['fconv']) ? $this->_grpfields[$fieldid]['fconv']:'');
    $fmt =  !empty($this->_rfields[$fieldid]->format) ? $this->_rfields[$fieldid]->format : '';
    if(!empty($conv) && function_exists($conv)) $ret = call_user_func($conv,$value);
    elseif($fmt=='money') $ret = number_format($value,2,$this->_delim_dec, $this->_delim_tho);
    elseif($fmt=='i') $ret = number_format($value,0,$this->_delim_dec, $this->_delim_tho);
    else $ret = $value;
    return $ret;
  }

  static function DrawJsCode($nostarttag=false, $to_var=false) {
    global $as_iface;
    $err_fill = isset($as_iface['err_emptymandatoryparam']) ? $as_iface['err_emptymandatoryparam']:'Not all mandatory parameters set';
    $jscode = $nostarttag ? '': "<script type=\"text/javascript\">\n";
    $jscode .= <<<ENDCRIPT


var asrep_activefrm = '';
var reptbkend = "<?=self::_backenduri?>";
function asReportShowForm(formid) {
  if(asrep_activefrm!='') $("#"+asrep_activefrm).hide();
  asrep_activefrm = formid;
  if(formid!="") $("#"+asrep_activefrm).show();
  return false;
}

function asOpenReParamForm(divid) {
   if(jQuery("#"+divid).get(0)) {
       jQuery("#"+divid).show();
       return;
   }
    jQuery("body").floatWindow(
    {
       url: reptbkend + "?showform="+divid
       ,userData: { showform:divid }
//       ,closeobj: "btncancel"
       ,title: "Enter parameters!"
    });

}

function asReportExec(formid) {
  var bfill=true;
  var pars = "";
  alert(formid);
//  $("#"+formid+" input").each(function() {
//    var vl = $(this).val();
//    alert(this.name+" "+vl);
//    if(vl=="") bfill=false;
//    if(this.name) pars += '&'+this.name+"="+encodeURIComponent(vl);
//  });
//  if(!bfill) { alert("<?=$err_fill?>"); return false; }
//  var wnd=window.open('{self::_backenduri}?reportdef='+name+pars, "_report","location=1,menubar=1,resizable=1,scrollbars=1,status=0,toolbar=1,top=40,left=40");
//  wnd.focus();
}
ENDCRIPT;
      if(!$nostarttag) $jscode .= "</script>\n";
      if($to_var) return $jscode;
      echo $jscode;

  }
  /**
  * returns HTML code with <form...> containing all parameters for input
  *
  * @param string $formid desired form's name and id
  */
  function DrawParametersForm($formid='') {
    global $as_iface;
    $frmid = ($formid)? $formid : $this->formid;
    if(count($this->_params)<1) return '';
    $ret = "<div id=\"div_$frmid\"><form name=\"$frmid\" id=\"$frmid\"><table border=0 cellpadding=2 cellspacing=1 width=40%>";
    foreach($this->_params as $no=>$prm) {
      $prompt = $prm->prompt;
      $itag = '';
      switch($prm->inputtype) {
        case 'text':
          $width = ($prm->type=='char')? '180':'70';
          $itag = "<input type=\"text\" name=\"{$prm->id}\" id=\"{$prm->id}\" style='width:{$width}px' class='ibox'/>";
          break;
        case 'select':
          $opts = @call_user_func($prm->fillfunc);
          $itag = "<select name=\"{$prm->id}\" >".DrawSelectOptions($opts,0,1).'</select>';
          break;
        case 'const':
          $val = ($prm->fillfunc) ? @call_user_func($prm->fillfunc) : 0;
          $ret.= "<input type=\"hidden\" name=\"{$prm->id}\" id=\"{$prm->id}\" value=\"$val\" />";
          break;
      }
      if($prm->inputtype!=='const') $ret .= "<tr><td>$prompt</td><td>$itag</td></tr>";
    }
    $submtxt = isset($as_iface['prompt_submit'])? $as_iface['prompt_submit']: 'submit';
    $ret .="<tr><td>&nbsp;</td><td><input type=\"button\" name=\"submit\" class=\"button\" value=\"$submtxt\" onclick=\"asReportExec('$formid')\"/></td></tr></table></form></div>";
    return $ret;
  }
} # CReporTool end
