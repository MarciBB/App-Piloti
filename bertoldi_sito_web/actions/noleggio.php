<?PHP

require_once 'actions_conf.php';

if ($captcha_success->success==false) {
    // What happens when the CAPTCHA was entered incorrectly
//    die("Errore Captcha." .
//            "(reCAPTCHA said: " . $resp->error . ")");
    $indirizzo_redirect = "/errore-captcha.php";
    header('Location: ' . $indirizzo_redirect);
} else {
    
    $email_subject = "Richiesta noleggio dal sito web";
    $indirizzo_redirect = "/grazie-invio-contatti.php";
    $nome_template = "template-mail-noleggio.html";

    invio_mail($email_to, $email_to_nome, $email_subject, $indirizzo_redirect, $nome_template, $_POST);

}

function invio_mail($email_to, $email_to_nome, $email_subject, $indirizzo_redirect, $nome_template, $post) {

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
        $headers .= 'From: ' . $email_to_nome . '  <' . $email_to . '>' . $eol;
        $headers .= "Content-Type: text/html; charset=iso-8859-1" . $eol;
        $headers .= "Reply-To:" . $email_to . $eol;
        
        $tipo_autobus = '';
        foreach($_POST['tipo_autobus'] as $tipo){
            $tipo_autobus .= '- '.$tipo.'<br/>';
        }


        $template = "../template_mail/" . $nome_template;
        $fh = fopen($template, "r");
        $content = fread($fh, filesize($template));
        fclose($fh);
        $content = str_replace("##-NOME-##", $_POST['nome'], $content);
        $content = str_replace("##-COGNOME-##", $_POST['cognome'], $content);
        $content = str_replace("##-AZIENDA-##", $_POST['azienda'], $content);
        $content = str_replace("##-VIA-##", $_POST['via'], $content);
        $content = str_replace("##-NUMERO-##", $_POST['numero'], $content);
        $content = str_replace("##-CAP-##", $_POST['cap'], $content);
        $content = str_replace("##-CITTA-##", $_POST['citta'], $content);
        $content = str_replace("##-TELEFONO-##", $_POST['telefono'], $content);
        $content = str_replace("##-EMAIL-##", $_POST['email'], $content);
        $content = str_replace("##-LUOGO_PARTENZA-##", $_POST['luogo_partenza'], $content);
        $content = str_replace("##-DATA_PARTENZA-##", $_POST['data_partenza'], $content);
        $content = str_replace("##-ORA_PARTENZA-##", $_POST['ora_partenza'], $content);
        $content = str_replace("##-LUOGO_ARRIVO-##", $_POST['luogo_arrivo'], $content);
        $content = str_replace("##-DATA_RITORNO-##", $_POST['data_ritorno'], $content);
        $content = str_replace("##-ORA_RIENTRO-##", $_POST['ora_rientro'], $content);
        $content = str_replace("##-TIPO_AUTOBUS-##", $tipo_autobus, $content);
        $content = str_replace("##-RICHIESTA-##", $_POST['richiesta'], $content);
        $content = str_replace("##-OGGETTO_MAIL-##", $email_subject, $content);

        ini_set(SMTP, '217.72.102.149');
        ini_set(sendmail_from, 'info@braincomputing.com');  // the INI lines are to force the From Address to be used !
        $emailaddress = $email_to;
        $send = mail($emailaddress, $emailsubject, $content, $headers);
        header('Location: ' . $indirizzo_redirect);
    }
?>