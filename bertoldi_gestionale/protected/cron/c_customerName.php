<?php
/* Configurazione log */
$file = '/var/log/csreisen_c_customerName.log';
$time_start = microtime(true);

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] START\n', FILE_APPEND | LOCK_EX);
/* LOG */

include_once("main_include.php");

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] configurazioni eseguite\n', FILE_APPEND | LOCK_EX);
/* LOG */

$x = customerName();

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] customerName eseguito\n', FILE_APPEND | LOCK_EX);
/* LOG */

/* LOG */
file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] END - Tempo esecuzione: ' . microtime(true) - $time_start . '\n', FILE_APPEND | LOCK_EX);
/* LOG */

// ELENCO FUNZIONI
function customerName()
{
global $user,$db;
$db=new Database();
$db->connect();

$currentDate = date('Y-m-d H:i:s');


$sql = "SELECT
RT_PrenotazionePasseggeri.Cognome,
RT_PrenotazionePasseggeri.Nome,
RT_PrenotazionePasseggeri.Principale,
RT_PrenotazionePasseggeri.PrenotazioneId
FROM
RT_PrenotazionePasseggeri
INNER JOIN RT_Prenotazione ON RT_PrenotazionePasseggeri.PrenotazioneId = RT_Prenotazione.PrenotazioneId
WHERE
RT_PrenotazionePasseggeri.Principale = 1 AND
RT_PrenotazionePasseggeri.Cancella = 0 order by RT_PrenotazionePasseggeri.PrenotazioneId desc";
$prenotazioni = $db->fetch_array($sql);

foreach ($prenotazioni as $prenotazione) {
   
        $prenotazioneId=$prenotazione['PrenotazioneId'];
        $cognome=  strtoupper($prenotazione['Cognome']);
        $nome=strtoupper($prenotazione['Nome']);
        $cognomeNome=$cognome." ".$nome;
        $data['ClienteNome'] = $cognomeNome;
        $db->update("RT_Prenotazione", $data, "PrenotazioneId=".$prenotazioneId);
        print($cognomeNome."\n");
       
	
}


$db->close();
return true;
}
?>