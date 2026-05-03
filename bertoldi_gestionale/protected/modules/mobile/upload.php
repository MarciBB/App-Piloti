<?php
// print_r($_FILES);

$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.DT.php");
$db = new Database();
$db->connect();

$path = dirname(dirname(dirname(__DIR__)));
$new_image_name = time().".jpg";
move_uploaded_file($_FILES["foto"]["tmp_name"], $path."\\upload\\photoApp\\".$new_image_name);

$sql = "UPDATE RT_FoglioViaggioSpesa SET Url = '$new_image_name' WHERE FoglioViaggioSpesaId = ".$_POST['spesaId'];
$db->query($sql);
echo $new_image_name;
?>