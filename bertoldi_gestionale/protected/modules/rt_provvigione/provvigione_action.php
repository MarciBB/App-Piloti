<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Mediatore.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.Percorso.php");
include_once($classespath_ . "class.Linea.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Tratta.php");
include_once($classespath_ . "class.TrattaTipo.php");
include_once($classespath_ . "class.Mezzo.php");
include_once($classespath_ . "class.TrattaDirezione.php");
include_once($classespath_ . "class.Provvigione.php");

$ModuloId = 27;

global $tratta_wizard;
$tratta_wizard = null;

if (isset($_SESSION['TRATTA_WIZARD'])) {
  $tratta_wizard = unserialize($_SESSION['TRATTA_WIZARD']);
}

function getRealPOST() {
  $pairs = explode("&", file_get_contents("php://input"));
  $vars = array();
  foreach ($pairs as $pair) {
    $nv = explode("=", $pair);
    $name = urldecode($nv[0]);
    $value = urldecode($nv[1]);
    if (strpos($name, 'ProvvigioneBiglietto') !== false) {
      $temp = explode("[", $name);
      $nameT = $temp[0];
      $index = $temp[1];
      $index = trim($index, "]");
      $vars[$nameT][$index] = $value;
    } else {
      $vars[$name] = $value;
    }
  }
  return $vars;
}

function create() {
  global $user, $db;

  $storico = new StoricoOperazioni();
  $storico->conn = $db;

  $post2 = getRealPOST();
  $data = $post2['ProvvigioneBigliettoPerc'];
  $data1 = $post2['ProvvigioneBigliettoFisso'];

  $dt = new DT();
  // $data=$_POST['ProvvigioneBigliettoPerc'];
  // $data1=$_POST['ProvvigioneBigliettoFisso'];

  $lastidA = $db->delete("RT_ProvvigioneBiglietto", "OdcIdRef=$user->OdcId");
  foreach ($data as $chiave => $valore) {
    $chiave = str_replace("'", "", $chiave);
    $chiave = str_replace("\\", "", $chiave);

    $arr_chiave = explode('_', $chiave);

    $LineaId = $arr_chiave[0];
    $ProvvigioneId = $arr_chiave[1];
    $BigliettoId = $arr_chiave[2];
    $Percentuale = $valore;
    if(!isset($Percentuale) || $Percentuale == "") {
      $Percentuale = 0;
    }
    $d1['LineaId'] = $LineaId;
    $d1['ProvvigioneId'] = $ProvvigioneId;
    $d1['BigliettoId'] = $BigliettoId;
    $fisso = $data1[$LineaId . "_" . $ProvvigioneId . "_" . $BigliettoId];
    if(!isset($fisso) || $fisso == "") {
      $fisso = 0;
    }
    $d1['Percentuale'] = str_replace(",", ".", $Percentuale);
    $d1['Fisso'] = str_replace(",", ".", $fisso);

    // print_r($d1);

    if ((!empty($fisso) and ($fisso) and ($fisso != "")) or ((!empty($Percentuale) and ($Percentuale) and ($Percentuale != "")))) {
      $d1 = $storico->operazioni_insert($d1, $user);
      $lastidA = $db->insert("RT_ProvvigioneBiglietto", $d1);
    }
  }

  echo("ok" . "," . $lastidA);

  $db->close();
  exit();
}

function createAll() {
  global $user, $db;

  $storico = new StoricoOperazioni();
  $storico->conn = $db;

  $lineaId = $_POST['massLineaId'];
  $provvigioneId = $_POST['massProvvigioneId'];
  $percentuale = $_POST['massPercentuale'];
  $fisso = $_POST['massFisso'];

  //recupero le linee
  $where = "ProvvigioneId = ".$provvigioneId;
  if(isset($lineaId) && $lineaId != "") {
    $where .= " AND LineaId = ".$lineaId;
    $arrayLinea = [$lineaId];
  } else {
    $sql = "Select * from RT_ElencoLinea where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId";
    $ArrObjectLinea = $db->fetch_array($sql);
    $arrayLinea = array();
    foreach ($ArrObjectLinea as $key => $value) {
        $arrayLinea[] = $value['LineaId'];
    }
  }

  //recupero le tipologie biglietto
  $sql = "Select * from RT_TipologiaBiglietto where Stato=1 and Cancella=0 and OdcIdRef=$user->OdcId";
  $ArrObjectTB = $db->fetch_array($sql);

  //recupero la percentuale
  if(!isset($percentuale) || $percentuale == "") {
    $percentuale = 0;
  }
  $percentuale = str_replace(",", ".", $percentuale);

  //recupero il fisso
  if(!isset($fisso) || $fisso == "") {
    $fisso = 0;
  }
  $fisso = str_replace(",", ".", $fisso);

  //cancello i dati per provvigione e linea
  $db->delete("RT_ProvvigioneBiglietto", $where);

  foreach($arrayLinea as $linea) {
    foreach($ArrObjectTB as $tb) {
      $d1 = array();
      $d1['LineaId'] = $linea;
      $d1['ProvvigioneId'] = $provvigioneId;
      $d1['BigliettoId'] = $tb['TipologiaBigliettoId'];
      $d1['Percentuale'] = $percentuale;
      $d1['Fisso'] = $fisso;

      if ((!empty($fisso) and ($fisso) and ($fisso != "")) or ((!empty($percentuale) and ($percentuale) and ($percentuale != "")))) {
        $d1 = $storico->operazioni_insert($d1, $user);
        $lastidA = $db->insert("RT_ProvvigioneBiglietto", $d1);
      }
    }
  }

  echo("ok" . "," . $lastidA);

  $db->close();
  exit();
}

if (is_object($user)) {
  $db = new Database();
  $db->connect();
  $user->conn = $db;
  $permessi = $user->get_permessi_modulo($ModuloId);
  if (sizeof($permessi) > 0) {
    if (!empty($_POST)) {
      switch ($_POST['action']) {
        case "create":
          $FunzioneId = 1;
          $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
          if (sizeof($permesso)) {
            create();
          } else {
            Errors::$ErrorePermessiModuloFunzione;
          }
          break;
        case "createAll":
          $FunzioneId = 1;
          $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
          if (sizeof($permesso)) {
            createAll();
          } else {
            Errors::$ErrorePermessiModuloFunzione;
          }
          break;
      }
    }
  } else {
    echo("no");
  }
} else {
  header("Location: /logout.php");
}
?>
