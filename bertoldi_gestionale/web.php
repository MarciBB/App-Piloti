<?php


$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");

$config=new Config();
$run=$config->load();


$username_a="brain";
$password_a="ciupido";
$sede_a="brain001";

check($username_a,$password_a,$sede_a);


function check($user_post,$pw_post,$sede_post)
{
 
    $db= new Database();
    $db->connect();
    $user=new Operatore();
    $user->conn=$db;
    if($user->login($user_post,$pw_post,$sede_post))
    {    
        $_SESSION['OPERATORE']=serialize($user);
        header("location: principale_web.php");    
    }
    else
        ?>
        <script type="text/javascript">
        alert("Nome utente o password non valida");
        </script>
        <?        
}
?>
