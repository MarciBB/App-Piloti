<?php
/**
 * Description of class
 *
 * @author L.Casaburi (MDT Software)
 */
class ServiceSalesManago  {

	// Event type constants
    const EVENT_PURCHASE     = 'PURCHASE';
    const EVENT_CART         = 'CART';
    const EVENT_TRANSACTION  = 'TRANSACTION';
    const EVENT_CANCELLATION = 'CANCELLATION';
    const EVENT_RETURN       = 'RETURN';
    const EVENT_VISIT        = 'VISIT';
    const EVENT_PHONE_CALL   = 'PHONE_CALL';
    const EVENT_OTHER        = 'OTHER';
    const EVENT_RESERVATION  = 'RESERVATION';
    const EVENT_CANCELLED    = 'CANCELLED';
    const EVENT_ACTIVATION   = 'ACTIVATION';
    const EVENT_MEETING      = 'MEETING';
    const EVENT_OFFER        = 'OFFER';
    const EVENT_DOWNLOAD     = 'DOWNLOAD';
    const EVENT_LOGIN        = 'LOGIN';
    const EVENT_SURVEY       = 'SURVEY';

	private $clientId;
    private $apiSecret;
    private $apiUrl;
	private $owner;
	public $conn;

	public function __construct($conn)
    {
        $this->clientId = Config::$salesmanago_clientid;
        $this->apiSecret = Config::$salesmanago_apisecret;
		$this->apiUrl = Config::$salesmanago_endpoint . 'api/';
		$this->apiKey = Config::$salesmanago_apikey;
		$this->owner = Config::$salesmanago_owner;
		$this->conn = $conn;

    }

	private function generateSha()
    {
        return $sha = sha1($this->apiKey . $this->clientId . $this->apiSecret);
    }

	private function getRequestTime()
    {
        return round(microtime(true) * 1000);
    }

	public function sendEvent($prenotazioneId, $eventType, $email, $products, $importo, $lang = null, $tour = null, $esperienza = null, $phone = null, $name = null, $country = null, $desctiption = null, $from = "web", $urlPagamento = null)
    {
        $date = $this->getRequestTime();
        $location = 'bertoldiboatscom';
        if ($lang && $lang != 'it') {
            $location .= '_' . $lang; 
        }

        // PRIMA: aggiungi o aggiorna il contatto
        $contactPayload = [
            'clientId' => $this->clientId,
            'apiKey' => $this->apiKey,
            'sha' => $this->generateSha(),
            'requestTime' => $date,
            'owner' => $this->owner,
            'contact' => [
                'email' => $email,
                'phone' => $phone,
                'name' => $name,
                'address' => [
                    'country' => $country,
                ],
            ]
        ];

        $contactUrl = str_replace('addContactExtEvent', 'contact/upsert', $this->apiUrl); // endpoint per add/modify contact
        $jsonContactPayload = json_encode($contactPayload);

        $chContact = curl_init($this->apiUrl.'contact/upsert');
        curl_setopt($chContact, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chContact, CURLOPT_POSTFIELDS, $jsonContactPayload);
        curl_setopt($chContact, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonContactPayload)
        ]);
        $contactResponse = curl_exec($chContact);
        
        if (curl_errno($chContact)) {
            throw new Exception('Errore CURL (contatto): ' . curl_error($chContact));
        }
        curl_close($chContact);

        // ...invio evento come già presente...
        $payload = [
            'clientId' => $this->clientId,
            'apiKey' => $this->apiKey,
            'sha' => $this->generateSha(),
            'requestTime' => $date,
            'owner' => $this->owner,
            'email' => $email,
            'contactEvent' => [
                'contactExtEventType' => $eventType,
                'date' => $date,
                'products' => $products,
                'value' => $importo,
                'location' => $location
            ]
        ];

        if ($tour) {
            $payload['contactEvent']['detail1'] = $tour;
        }
        if ($esperienza) {
            $payload['contactEvent']['detail2'] = $esperienza;
        }
        if ($desctiption) {
            $payload['contactEvent']['description'] = $desctiption;
        }
        if ($eventType === self::EVENT_PURCHASE || $eventType == self::EVENT_RESERVATION) {
            $pid = intval($prenotazioneId);
            $rowQ = $this->conn->query_first("SELECT CodiceQrcode FROM RT_PrenotazioneNumero WHERE PrenotazioneId = $pid ORDER BY PrenotazioneNumeroId ASC");
            if (isset($rowQ['CodiceQrcode']) && $rowQ['CodiceQrcode'] !== '') {
                $code = $rowQ['CodiceQrcode'];
                $payload['contactEvent']['detail3'] = "https://office.bertoldiboats.com/protected/modules/rt_qrcode/qrcode.php?code=" . $code;
                $payload['contactEvent']['detail4'] = "https://office.bertoldiboats.com/protected/modules/rt_qrcode/ticket.php?code=" . $code;
            }
        }
        if(isset($urlPagamento) && $eventType === self::EVENT_OFFER){
            $payload['contactEvent']['detail5'] = $urlPagamento;
        }

        $jsonPayload = json_encode($payload);

        $ch = curl_init($this->apiUrl.'v2/contact/addContactExtEvent');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload)
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Errore CURL: ' . curl_error($ch));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $dataPT = [
            'PrenotazioneId' => $prenotazioneId,
            'Event' => $eventType,
            'Email' => $email,
            'Products' => $products,
            'Value' => $importo,
            'Lang' => $lang,
            'Detail1' => $tour,
            'Detail2' => $esperienza,
            'Date' => date('Y-m-d H:i:s', $date / 1000), // Convert milliseconds to seconds
            'ContactName' => $name,
            'ContactPhone' => $phone,
            'ContactCountry' => $country,
            'Result' => $response,
            'From' => $from,
        ];

        // Insert into the database
        $this->conn->insert("RT_SalesManagoEvent", $dataPT);

        return [
            'statusCode' => $statusCode,
            'response' => json_decode($response, true)
        ];
    }
}
?>
