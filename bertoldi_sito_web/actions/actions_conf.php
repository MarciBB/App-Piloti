<?PHP
    error_reporting(E_ALL);
    ini_set('display_errors', 0);

    //verifica del reCaptcha v3
    
    $privatekey = "6Lftot0pAAAAAEOtzVLCDPsUvn833wIXjC7NNbiS";
    
    $response = $_POST["g-recaptcha-response"];
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
    		'secret' => $privatekey,
    		'response' => $_POST["g-recaptcha-response"]
    );
    $options = array(
    		'http' => array (
    				'method' => 'POST',
    				'content' => http_build_query($data)
    		)
    );
    $context  = stream_context_create($options);
    $verify = file_get_contents($url, false, $context);
    $captcha_success = json_decode($verify);
    
    // fine controllo reCaptcha
    
	$email_to = "luc.casaburi@gmail.com";
    $email_to_nome = "Bertoldi Boats";
?>