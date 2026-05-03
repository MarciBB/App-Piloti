<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
global $search, $dizionario;

include_once($basepath."/main_include.php");

$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;

$db=new Database();
$conn=$db->connect();

print("<option value=\"\">- ".$dizionario['generale']['seleziona']." -</option>");
if ($_GET['LineaId']>0) {
	$LineaId = $_GET['LineaId'];
	$sql="Select CorsaId,CorsaNome from RT_Corsa where LineaId=$LineaId 
	and Stato = 1 and Cancella = 0
	order by CorsaNome asc";
	$arr_corse=$db->fetch_array($sql);
	$conta=0;

	while ($conta<sizeof($arr_corse)) {
	    $CorsaId=$arr_corse[$conta]['ContaId'];
	    $CorsaNome=$arr_corse[$conta]['CorsaNome'];
	    ?>
			<option value="<?=$CorsaId?>"><?=$CorsaNome?></option>
	    <?
	    $conta++;
	}
}
die();
?>
