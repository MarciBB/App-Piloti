<?PHP

require_once 'actions_conf.php';
$basepath = $_SERVER['DOCUMENT_ROOT'];

$config_include = $basepath . '/custom/reserved/class.Config.php';
$database_include = $basepath . '/protected/classes/class.Database.php';
include_once($database_include);
include_once($config_include);
global $db;

$config = new Config();
$run = $config->load();

$classespath_ = Config::$classespath;
include_once($classespath_ . "class.NotificaAutomaticaMessaggiInvio.php");

if ($captcha_success->success == false) {
    $indirizzo_redirect = "/errore-captcha.php";
    header('Location: ' . $indirizzo_redirect);
} else if ($captcha_success->success == true) {
    $email_subject = "Richiesta rimborso dal sito web";
    $indirizzo_redirect = "/grazie-invio-contatti.php";
    $nome_template = "template-mail-rimborso.html";

    invio_mail($email_to, $email_to_nome, $email_subject, $indirizzo_redirect, $nome_template, $_POST, $_FILES);
}

function invio_mail($email_to, $email_to_nome, $email_subject, $indirizzo_redirect, $nome_template, $post, $files)
{
    global $db;
    /*if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
            $eol = "\r\n";
        } elseif (strtoupper(substr(PHP_OS, 0, 3) == 'MAC')) {
            $eol = "\r";
        } else {
            $eol = "\n";
        }*/

    # Message Subject
    $emailsubject = $email_subject;
    # Boundry for marking the split & Multitype Headers
    //$mime_boundary = md5(time());
    # Common Headers
    $headers .= 'From: ' . $email_to_nome . '  <' . $email_to . '>';
    //$headers .= "Content-Type: text/html; charset=iso-8859-1" . $eol;
    $headers .= "Reply-To:" . $email_to;


    $template = "../template_mail/" . $nome_template;
    $fh = fopen($template, "r");
    $content = fread($fh, filesize($template));
    fclose($fh);
    $content = str_replace("##-NOME-##", $_POST['nome'], $content);
    $content = str_replace("##-COGNOME-##", $_POST['cognome'], $content);
    $content = str_replace("##-EMAIL-##", $_POST['email'], $content);
    $content = str_replace("##-TELEFONO-##", $_POST['telefono'], $content);
    $content = str_replace("##-NUMERO_BIGLIETTO-##", $_POST['numero_biglietto'], $content);
    $content = str_replace("##-DATA_PARTENZA-##", $_POST['data_partenza'], $content);
    $content = str_replace("##-TIPOLOGIA_BIGLIETTO-##", $_POST['tipologia_biglietto'], $content);
    $content = str_replace("##-MODALITA_RIMBORSO-##", $_POST['tipologia_pagamento'], $content);
    $content = str_replace("##-INTESTATARIO_CONTO-##", $_POST['intestatario_conto'], $content);
    $content = str_replace("##-IBAN-##", $_POST['iban'], $content);
    $content = str_replace("##-BIC-##", $_POST['bic'], $content);

    $content = str_replace("##-OGGETTO_MAIL-##", $email_subject, $content);

    //$nome_file = $files['attachment']['name'];
    //$array_estensioni_ammesse = array('.pdf','.jpg','.doc');
    //$stampa_estensioni = implode(', ', $array_estensioni_ammesse);
    //$dimensione_massima = 1024; //dimensione massima consentita per file in byte -> 1024 byte = 1 Kb
    //$dimensione_massima_Kb = $dimensione_massima / 1024;
    //$estensione = strtolower(substr($nome_file, strrpos($nome_file, "."), strlen($nome_file) - strrpos($nome_file, ".")));
    //$dir = dirname(dirname(__FILE__));

    //         if (!in_array($estensione, $array_estensioni_ammesse)) {
    //             echo "<script type=\"text/javascript\">alert(\"Upload file non ammesso. Estensioni ammesse: $stampa_estensioni \"); history.go(-1)</script>";
    //             //alert ("Upload file non ammesso. Estensioni ammesse: '.implode(', ',$array_estensioni_ammesse)");
    //         } elseif ($files['file1']['size'] > $dimensione_massima) {
    //             echo "<script type=\"text/javascript\">alert(\"Il file selezionato per l'upload supera dimensione massima di $dimensione_massima_Kb Kb!\"); history.go(-1)</script>";
    //             //alert ("Il file selezionato per l'upload supera dimensione massima di $dimensione_massima_Kb Kb");
    //         } else {

    //             move_uploaded_file($files["attachment"]["tmp_name"], $dir.DIRECTORY_SEPARATOR.'file_upload'.DIRECTORY_SEPARATOR.$nome_file);       
    //             mail_attachment($_POST['email'], $email_to, $email_subject, $content, $headers, ($dir.DIRECTORY_SEPARATOR.'file_upload'.DIRECTORY_SEPARATOR.$nome_file),$nome_file,$indirizzo_redirect);
    //         }

    /**invio email**/
    $db = new Database();
    $db->connect();
    $data = [
        "nome" => $_POST["nome"],
        "cognome" => $_POST["cognome"],
        "email" => $_POST["email"],
        "telefono" => $_POST["telefono"],
        "biglietto" => $_POST["numero_biglietto"],
        "data_viaggio" => $_POST["data_partenza"],
        "tipo_viaggio" => $_POST["tipologia_biglietto"],
        "modalita" => $_POST["tipologia_pagamento"],
        "intestatario_conto" => isset($_POST["intestatario_conto"]) ? $_POST["intestatario_conto"] : null,
        "iban" => isset($_POST["iban"]) ? $_POST["iban"] : null,
        "bic" => isset($_POST["bic"]) ? $_POST["bic"] : null,
        "ip_from" => get_ip_address(),
    ];
    $db->insert("rimborsi_forms", $data);
    $invioEmail = new NotificaAutomaticaMessaggiInvio();
    $invioEmail->InvioEmailSemplice($content, $emailsubject, $email_to, 1);
    

    //         ini_set(SMTP, '217.72.102.149');
    //         ini_set(sendmail_from, 'info@braincomputing.com');  // the INI lines are to force the From Address to be used !
    //         $emailaddress = $email_to;
    //         $send = mail($emailaddress, $emailsubject, $content, $headers);
    header('Location: ' . $indirizzo_redirect);
}

function mail_attachment($from, $to, $subject, $message, $headers2, $attachment, $nome_file, $indirizzo_redirect)
{
    $fileatt = $attachment; // Path to the file
    $fileatt_type = "application/octet-stream"; // File Type
    $start = strrpos($attachment, '/') == -1 ? strrpos($attachment, '//') : strrpos($attachment, '/') + 1;
    //$fileatt_name = substr($attachment, $start, strlen($attachment)); // Filename that will be used for the file as the attachment
    $fileatt_name = $nome_file;

    $email_from = $from; // Who the email is from

    $email_subject = $subject; // The Subject of the email
    $email_txt = $message; // Message that the email has in it

    $email_to = $to; // Who the email is to

    //$headers = "From: " . $email_from;
    $headers = $headers2;

    $file = fopen($fileatt, 'rb');
    $data = fread($file, filesize($fileatt));
    fclose($file);
    $msg_txt = "";
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
    $headers .= "\nMIME-Version: 1.0\n" .
        "Content-Type: multipart/mixed;\n" .
        " boundary=\"{$mime_boundary}\"";
    $email_txt .= $msg_txt;
    $email_message .= "Formato file MIME .\n\n" .
        "--{$mime_boundary}\n" .
        "Content-Type:text/html; charset=\"iso-8859-1\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" .
        $email_txt . "\n\n";
    $data = chunk_split(base64_encode($data));
    $email_message .= "--{$mime_boundary}\n" .
        "Content-Type: {$fileatt_type};\n" .
        " name=\"{$fileatt_name}\"\n" .
        //"Content-Disposition: attachment;\n" .
        //" filename=\"{$fileatt_name}\"\n" .
        "Content-Transfer-Encoding: base64\n\n" .
        $data . "\n\n" .
        "--{$mime_boundary}--\n";

    unlink($attachment);

    # SEND THE EMAIL
    ini_set(SMTP, '217.72.102.149');
    ini_set(sendmail_from, 'info@braincomputing.com');  // the INI lines are to force the From Address to be used !

    $ok = mail($email_to, $email_subject, $email_message, $headers);

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
