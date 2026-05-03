<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" type="text/css" href="/css/print_stat_mediazione.css" media="print" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />

<?php 
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$config = new Config();
$run = $config->load(); 
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Nazione.php");
include_once($classespath_ . "class.Regione.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");

$ModuloId = 12;

function show_list() {
  global $user, $dizionario;

  $datacorrente = Date('d/m/Y');

  $db = new Database();
  $db->connect();
  $page = new Form();
  $gestore = new Gestore();
  $gestore->conn = $db;
  $ges = $user->GestoreId;

  if (($user->GestoreId == 1) || ($user->GestoreId == 2)) {
    $ges = 1;
  }

  $gestore->getGestoreAll($ges);
  $arr_gestore = $gestore->ArrGestore;

  $sede = new Sede();
  $sede->conn = $db;
  $gestorecorrente = $user->GestoreId;
  $arr_sedi = $sede->getSediByGestori($user->GestoreId);

  include_once("stat_competenza_provvigioni_validator.php");  
?>

<div>
  <!-- Inizio riga per i filtri -->
  <div class="brainFiltri">
    <form id="application_form" name="application_form" method="post" action="#">
      <?php
      $page->create_select(
        $dizionario['generale']['agenzia'] . ":",
        "GestoreId",
        "GestoreId",
        "rowForm",
        $arr_gestore,
        $gestorecorrente,
        "GestoreId",
        "RagioneSociale",
        array("onChange" => "'javascript:getSediByGestore(this);'"),
        1
      );

      $page->create_select(
        $dizionario['conto']['rivendita'] . ":",
        "SedeId",
        "SedeId",
        "rowForm",
        $arr_sedi,
        $user->SedeId,
        "SedeId",
        "Sede",
        null,
        1
      );
      ?>

      <div class="rowForm">
        <label for="tipo_report"><?= $dizionario['conto']['tipo_report'] ?></label>
        <select name="tipo_report">
          <option value="1" selected><?= $dizionario['conto']['totali'] ?></option>
        </select>
      </div>

      <div class="rowForm">
        <label for="Dal"><?= $dizionario['generale']['dal'] ?></label>
        <input class="required" type="text" value="<?= $datacorrente ?>" id="Dal" name="Dal" maxlength="255" size="10">
        <label for="dataAl"><?= $dizionario['generale']['al'] ?></label>
        <input class="required" type="text" value="<?= $datacorrente ?>" id="Al" name="Al" maxlength="255" size="10">
      </div>

      <div class="rowForm">
        <input name="applica" type="submit" value="<?= $dizionario['generale']['genera'] ?>" />
      </div>
      <br style="clear:both;" />
    </form>
  </div>
  <!-- Fine riga per i filtri -->

  <div id="risultato_report"></div>
</div>

<?php
}

if (is_object($user)) {
  $db = new Database();
  $db->connect();
  $user->conn = $db;
  $permessi = $user->get_permessi_modulo($ModuloId);

  $do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';

  switch ($do) {
    default:
      $FunzioneId = 1;
      $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

      if (sizeof($permesso)) {
        show_list();
      }
      break;
  }
} else {
  // Se l'utente non è loggato
  header("Location: /logout.php");
}
?>
