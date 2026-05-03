<?php
include_once('c_path_include.php');
ini_set('display_errors', 1);

/* Configurazione log */
//$file = '/var/log/csreisen_c_disattivaScontiSovrapprezzi.log';
$time_start = microtime(true);

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] START\n', FILE_APPEND | LOCK_EX);
/* LOG */

$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$dt_include=$basepath.'/protected/classes/class.DT.php';

include_once($config_include);
include_once($database_include);
include_once($dt_include);

global $user;
global $db;

$config = new Config();
$run = $config->loadCron($type);
  
$classespath_ = $basepath."/protected/classes/";
$modulespath_ = $basepath."/protected/modules/";

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] configurazioni eseguite\n', FILE_APPEND | LOCK_EX);
/* LOG */

$x = disattivaScontiSovrapprezzi();

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] disattivaScontiSovrapprezzi eseguito\n', FILE_APPEND | LOCK_EX);
/* LOG */

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] END - Tempo esecuzione: ' . microtime(true) - $time_start . '\n', FILE_APPEND | LOCK_EX);
/* LOG */

// ELENCO FUNZIONI
function disattivaScontiSovrapprezzi()
{
	global $user,$db;
	$db=new Database();
	$db->connect();

	$currentDate = date('Y-m-d H:i:s');


	$sql = "update RT_ScontisticaCorsa as t INNER JOIN
	(
	SELECT
	RT_ScontisticaCorsa.ScontisticaCorsaId
	FROM
	RT_ScontisticaCorsa
	WHERE
	RT_ScontisticaCorsa.Al <= NOW()
	ORDER BY
	RT_ScontisticaCorsa.Dal ASC) as t1
	on t.ScontisticaCorsaId=t1.ScontisticaCorsaId
	set t.Stato=0";
	$db->query($sql);

	$sql="update RT_Scontistica set NumeroCorseAttive=0";
	$r=$db->query($sql);

	$sql="update RT_Scontistica as t INNER JOIN (SELECT
	RT_ScontisticaCorsa.ListinoId,
	Count(RT_ScontisticaCorsa.ScontisticaCorsaId) AS N,
	RT_Scontistica.ListinoNome
	FROM
	RT_ScontisticaCorsa
	INNER JOIN RT_Scontistica ON RT_ScontisticaCorsa.ListinoId = RT_Scontistica.ListinoId
	WHERE
	RT_ScontisticaCorsa.Stato = 1
	GROUP BY
	RT_ScontisticaCorsa.ListinoId) as t1
	on t.ListinoId=t1.ListinoId
	set t.NumeroCorseAttive=t1.N";
	$r=$db->query($sql);


	$db->close();
	return true;
}
?>