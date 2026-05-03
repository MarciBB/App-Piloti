<?php
include_once($classespath_."Zoho/ZohoCrmApi.php");
/**
 * Classe per incapsulare le funzioni di Fiscal Gateway
 *
 * @author L.Casaburi
 */
class ServiceZoho {

	public function insertLeads($lastname, $firstname, $email, $country, $percorso, $esperienza, $phone, $mobile, $leadSource, $lingua = 'italiano') {
		$api = new ZohoCrmApi();
		
		$response = $api->insertLeads([
			[
				'Last_Name' => $lastname,
				'First_Name' => $firstname,
				'Email' => $email,
				'Country' => $country,
				'Percorso' => $percorso,
				'Esperienza' => $esperienza,
				'Phone' => $phone,
				'Mobile' => $mobile,
				'Lead_Source' => $leadSource,
				'Lingua' => $lingua
			]
		]);

		$newLeadId = false;
		if (!empty($response['data'][0]['details']['id'])) {
			
			$newLeadId = $response['data'][0]['details']['id'];
		}

		return $newLeadId;
	}
	
	
	public function getLeads() {
		$api = new ZohoCrmApi();
		
		$response = $api->getLeads();

		return $response['data'];
	}
	
	public function getLead ($newLeadId) {
		$api = new ZohoCrmApi();
		
		if (empty($newLeadId)) {
			return false;
		}

		$result = $api->getLead($newLeadId);
		return !$result['data'][0];
	}
	
	public function updateLeads ($newLeadId, $lastname, $firstname, $email, $country, $percorso, $esperienza, $phone, $mobile, $leadSource, $lingua = 'italiano') {
		$api = new ZohoCrmApi();
		
		if (empty($newLeadId)) {
			return false;
		}

		$result = $api->updateLeads([
			[
				'id' => $newLeadId,
				'Last_Name' => $lastname,
				'First_Name' => $firstname,
				'Email' => $email,
				'Country' => $country,
				'Percorso' => $percorso,
				'Esperienza' => $esperienza,
				'Phone' => $phone,
				'Mobile' => $mobile,
				'Lead_Source' => $leadSource,
				'Lingua' => $lingua
			]
		]);

		return $result['status'] === 'success'
			&& !empty($result['data'])
			&& !empty($result['data'][0]['details']['id'])
			&& $result['data'][0]['code'] === 'SUCCESS';
	}

	public function deleteLeads ($newLeadId) {
		$api = new ZohoCrmApi();
		
		if (empty($newLeadId)) {
			return false;
		}

		$result = $api->deleteLeads([$newLeadId]);

		return $result['status'] === 'success'
			&& !empty($result['data'])
			&& !empty($result['data'][0]['details']['id'])
			&& $result['data'][0]['code'] === 'SUCCESS';
	}
	
	public function insertLeadPrenotazione ($db, $prenotazioneId) {
		
		$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$prenotazioneId;
		$prenotazione = $db->query_first($sql);
		
		$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = " . $prenotazioneId ." AND Direzione = 'A'";
		$percorso = $db->query_first($sql);
		
		$leadSource = 'ReTicket Backoffice';
		switch ($prenotazione['Canale']) {
			case 'agenzia':
				$leadSource = 'ReTicket Agenzia';
				break;
			case 'backoffice':
				$leadSource = 'ReTicket Backoffice';
				break;
			case 'web_it':
				$leadSource = 'ReTicket App Cliente';
				break;
			case 'operatore':
				$leadSource = 'ReTicket App Operatore Molo';
				break;
			default: 
				$leadSource = 'ReTicket Backoffice';
		}
		
		$phone = $mobile = "+".$prenotazione['ClienteCellularePrefisso'].$prenotazione['ClienteCellulare'];
		
		$sql = "SELECT * FROM PrefissoTelefono where Prefisso = ".$prenotazione['ClienteCellularePrefisso'];
		$prefisso = $db->query_first($sql);
		$country = $prefisso['Nazione'];
		
		//inserisco o aggiorno il lead su Zoho
		$zohoId = null;
		if(!isset($prenotazione['ZohoLeadId'])) {
			$zohoId = $this->insertLeads($prenotazione['ClienteNome'], $prenotazione['ClienteNome'], $prenotazione['ClienteEmail'], $country, $percorso['PercorsoNome'], $percorso['LineaNome'], $phone, $mobile, $leadSource, 'italiano');
		} else {
			$zohoId = $prenotazione['ZohoLeadId'];
			$this->updateLeads($prenotazione['ZohoLeadId'], $prenotazione['ClienteNome'], $prenotazione['ClienteNome'], $prenotazione['ClienteEmail'], $country, $percorso['PercorsoNome'], $percorso['LineaNome'], $phone, $mobile, $leadSource, 'italiano');
		}
		
		//aggiorno la prentoazione con lead id di zoho
		if(isset($zohoId)) {
			$data = array();
			$data['ZohoLeadId'] = $zohoId;
			$data['ZohoDataUpdate'] = date('Y-m-d H:i:s');
			
			$db->update("RT_Prenotazione", $data, "PrenotazioneId = $prenotazioneId");
			
			return true;
		} else {
			return false;
		}			
	}

}