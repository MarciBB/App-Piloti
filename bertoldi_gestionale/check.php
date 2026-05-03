<?php

$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath."/main_include.php");

$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
include_once($classespath_."class.Operatore.php");

function check() {
    
    $user_post=$_POST['username'];
    $pw_post=$_POST['password'];
    $sede_post=$_POST['sede']; 
    $lingua_post=$_POST['lingua'];

    $db= new Database();
    $db->connect();
    $user=new Operatore();
    $user->conn=$db;
    if($user->login($user_post,$pw_post,$sede_post)) {    
		if ($user->Moroso==1) {
			return false;
		} else {
			$_SESSION['OPERATORE']=serialize($user);

			$_SESSION['LINGUA'] = $lingua_post;
            return true;
		}
	} else {
        return false;       
	}
}



if(check()){
	header("location: principale.php?start=1");
	exit();
} else{
	header("location: index.php");
	exit();
}

?>