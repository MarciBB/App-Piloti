<?php
/**
 * Description of class
 *
 * @author L.Casaburi (Braincomputing)
 */
class ServiceGooglePeople  {

	public $conn;

	function __construct($conn) {
	    $this->conn = $conn;
	}
	
	public function existPeopleByEmail($email){
		$sql = "SELECT * FROM GooglePeople WHERE Email = '$email'";
		$row =  $this->conn->query_first($sql);
		
		if(isset($row['Email']) && $row['Email'] != ''){
			return true;
		} else {
			return false;
		}
	}
	
	public function existPeopleByCellulare($cellulare){
		$sql = "SELECT * FROM GooglePeople WHERE Cellulare = '$cellulare'";
		$row =  $this->conn->query_first($sql);
	   
		if(isset($row['Cellulare']) && $row['Cellulare'] != ''){
			return true;
		} else {
			return false;
		}
	}
	
	public function insertPeople($nome, $cognome, $email, $cellulare){
		
		ini_set('display_errors', 0);
		ini_set('error_reporting', E_ALL);

		$url = Config::$servicesUrl.'googlepeople/insertPeople?token='.Config::$servicesToken;
		
		if(!$this->existPeopleByEmail($email) || !$this->existPeopleByCellulare($cellulare)) {
			$data = array(
					'nome' => $nome,
					'cognome' => $cognome,
					'email' => $email,
					'cellulare' => $cellulare
			);
			
			$options = array(
					'http' => array(
							'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
							'method'  => 'POST',
							'content' => http_build_query($data)
					)
			);
			$context  = stream_context_create($options);
			$result = @file_get_contents($url, false, $context);
			if ($result === false) {
			    // Log o gestisci l'errore come preferisci
			    error_log("Errore chiamata a GooglePeople: $url");
			    return false;
			}
			return $result;
		}	
	}
}
?>
