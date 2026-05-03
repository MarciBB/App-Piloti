<?PHP

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'actions_conf.php';
$basepath = $_SERVER['DOCUMENT_ROOT'];

$config_include = $basepath . '/custom/reserved/class.Config.php';
$database_include = $basepath . '/protected/classes/class.Database.php';
include_once($database_include);
include_once($config_include);
global $db;

$config = new Config();
$run = $config->load();

$db = new Database();
$db->connect();

$classespath_ = Config::$classespath;
include_once($classespath_ . "class.NotificaAutomaticaMessaggiInvio.php");

if ($captcha_success->success == false) {
    // What happens when the CAPTCHA was entered incorrectly
    $indirizzo_redirect = "/errore-captcha.php";
    header('Location: ' . $indirizzo_redirect);
} else {
    
    $email_subject = "Contatto dal sito web";
    $indirizzo_redirect = "/grazie-invio-contatti.php";
    $nome_template = "template-mail-contatto.html";

    invio_mail($email_to, $email_to_nome, $email_subject, $indirizzo_redirect, $nome_template, $_POST);
}

function invio_mail($email_to, $email_to_nome, $email_subject, $indirizzo_redirect, $nome_template, $post)
{
	global $db;
	
    if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
        $eol = "\r\n";
    } elseif (strtoupper(substr(PHP_OS, 0, 3) == 'MAC')) {
        $eol = "\r";
    } else {
        $eol = "\n";
    }

    # Message Subject
    $emailsubject = $email_subject;
    # Boundry for marking the split & Multitype Headers
    $mime_boundary = md5(time());
    # Common Headers
	$headers = "";
    $headers .= 'From: ' . $email_to_nome . '  <' . $email_to . '>' . $eol;
    $headers .= "Content-Type: text/html; charset=iso-8859-1" . $eol;
    $headers .= "Reply-To:" . $email_to . $eol;


    $template = "../template_mail/" . $nome_template;
    $fh = fopen($template, "r");
    $content = fread($fh, filesize($template));
    fclose($fh);
    $content = str_replace("##-NOME-##", $_POST['nome'], $content);
    $content = str_replace("##-COGNOME-##", $_POST['cognome'], $content);
    $content = str_replace("##-EMAIL-##", $_POST['email'], $content);
    $content = str_replace("##-TELEFONO-##", $_POST['telefono'], $content);
    $content = str_replace("##-RICHIESTA-##", $_POST['richiesta'], $content);
    $content = str_replace("##-OGGETTO_MAIL-##", $email_subject, $content);

    $emailaddress = $email_to;

	$sender = new NotificaAutomaticaMessaggiInvio();
	$sender->InvioEmailSemplice($content, $emailsubject, $emailaddress, 1);

    $data = [
        "nome" => $_POST["nome"],
        "cognome" => $_POST["cognome"],
        "email" => $_POST["email"],
        "telefono" => $_POST["telefono"],
        "richiesta" => $_POST["richiesta"],
        "ip_from" => get_ip_address(),
    ];
    $response = $db->insert("contact_forms", $data);
    header('Location: ' . $indirizzo_redirect);
}

function get_ip_address()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
