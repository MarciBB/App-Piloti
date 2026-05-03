<?php
// filepath: c:\wamp64\www\bertoldi_git\bertoldi_sito_web\prenota\booking.php

$basepath = $_SERVER['DOCUMENT_ROOT'];
include($basepath . '/main_include.php');

$PageTitle = $dizionario['ticket']['titolo'];
$PageDescription = $dizionario['ticket']['descr'];
$PageKeywords = $dizionario['ticket']['key'];

$page_title = "Home";
$LinkActive = 1; 

$db = new Database();
$db->connect();

// --- Recupera i parametri dalla query string ---
$lineaId = isset($_GET['lineaId']) ? intval($_GET['lineaId']) : 0;
$porti = isset($_GET['porti']) ? trim($_GET['porti']) : '';
$lang = isset($_GET['lang']) ? trim($_GET['lang']) : 'it';
$data = isset($_GET['data']) ? trim($_GET['data']) : '';

// --- Query per recuperare la linea e impostare tipo_tour ---
if ($lineaId > 0) {
    $sqlCorse = "SELECT l.* FROM RT_Linea l WHERE l.LineaId = $lineaId";
    $lineaRow = $db->query_first($sqlCorse);
    if ($lineaRow && isset($lineaRow['TipoTour'])) {
        $_SESSION['tipo_tour'] = $lineaRow['TipoTour'];
    }
    $_SESSION['seleziona_lesperienza'] = $lineaId;
    $_SESSION['LineaId'] = $lineaId;
}

// --- Imposta i parametri in sessione come fa 1.php ---
if ($porti !== '') {
    $_SESSION['porti'] = $porti;
}
if ($lang !== '') {
    $_SESSION['lang'] = $lang;
}
if ($data !== '') {
    $_SESSION['ticket_date'] = $data;
    $_SESSION['DataPartenza'] = $data;
}

// --- Redirect a 2.php ---
header('Location: 2.php');
exit;