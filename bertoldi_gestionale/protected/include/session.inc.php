<?php
// inizializza la sessione
$valore_session=60 * 60 * 24 * 100;
//ini_set('session.gc_maxlifetime', $valore_session);
$dir_sessione=$_SERVER['DOCUMENT_ROOT'].'/sessioni';
session_save_path($dir_sessione);
session_start();

?>