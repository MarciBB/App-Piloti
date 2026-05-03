<?php

class ZohoCrmApi
{
    /** @var int Versione dell'API di ZoHo da utilizzare */
    const API_VERSION = 7;

    /** @var array */
    protected $config;

    /** @var string */
    protected $baseUrl;

    /**
     * @throws Exception
     */
    public function __construct($configFile = __DIR__ . '/config.json')
    {
        if (!file_exists($configFile)) {
            throw new Exception('Config file does not exist');
        }

        $this->config = json_decode(file_get_contents($configFile), true);
    }

    /**
     * Compone l'URL di base per effettuare le chiamate API
     * 
     * @param string $endpoint
     * @return string
     */
    protected function getEndpointUrl($endpoint)
    {
        return strpos($endpoint, '://') !== false
            ? $endpoint
            : rtrim($this->baseUrl, '/') . '/crm/v' . self::API_VERSION . '/' . ltrim($endpoint, '/');
    }

    /**
     * Effettua una chiamata HTTP ad un'API di ZohoCRM
     *
     * @param array $curlOptions Opzioni per la funzione curl_setopt_array
     * @return array Risultato della chiamata API
     * @throws Exception Se la chiamata API fallisce
     */
    protected function request($curlOptions)
    {
        $curlOptions[CURLOPT_URL] = $this->getEndpointUrl($curlOptions[CURLOPT_URL]);

        // Aggiungo i dati del token alla richiesta
        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        $result = json_decode(curl_exec($ch), true);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status >= 300) {
            print_r($curlOptions);
            throw new Exception("Si è verificato un errore", $status);
        }

        if (!$result) {
            print_r($curlOptions);
            throw new Exception("Si è verificato un errore", 500);
        }
        if (!empty($result['error'])) {
            throw new Exception($result['error']);
        }
        return [
            'status' => 'success',
            'code' => 200,
            'response' => $result,
        ];
    }

    protected function formatResponse($response)
    {
        return [
            'status' => $response['status'],
            'code' => $response['code'],
            'data' => $response['response']['data'],
        ];
    }


    /**
     * Recupera l'access token per effettuare le chiamate API, recuperandolo da cache se non è scaduto
     * 
     * Si basa sui dati presenti nel file config.json per la richiesta. Per rigenerare i dati
     * di access_token da zero (ad esempio, quando viene creato un nuovo account CRM, o viene perso il token
     * di refresh), usare la Developer Console di ZoHo (https://api-console.zoho.com/). 
     * 
     * Per ulteriori info, vedere https://www.zoho.com/crm/developer/docs/api/v6/auth-request.html#self-client
     * 
     * @return string L'access token
     * @throws Exception Se non riesce a recuperare l'access token
     */
    protected function getAccessToken()
    {
        if (file_exists(__DIR__ . '/access_token.cache.json')) {
            $result = json_decode(file_get_contents(__DIR__ . '/access_token.cache.json'), true);
            if (time() < $result['expires_at']) {
                // Il token non è ancora scaduto, restituisco lo stesso token un'altra volta
                $this->baseUrl = $result['api_domain'];
                return $result['access_token'];
            }
        }

        // Il token è scaduto, ne genero uno nuovo
        $url = $this->config['base_url_auth'] . '/oauth/v2/token';

        $requestParameters = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'redirect_uri'  => $this->config['redirect_uri'],
            'refresh_token' => $this->config['refresh_token'],
        ];

        $result = $this->request([
            CURLOPT_URL            => $url . '?' . http_build_query($requestParameters),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
        ]);

        if (!isset($result['response'])) {
            throw new Exception("Si è verificato un errore nel recupero dell'access token", 500);
        }

        $result = $result['response'];

        $result['expires_at'] = time() + $result['expires_in'];
        file_put_contents(__DIR__ . '/access_token.cache.json', json_encode($result, JSON_PRETTY_PRINT));

        $this->baseUrl = $result['api_domain'];
        return $result['access_token'];
    }

    /**
     * Effettua una richiesta HTTP GET ad un'API di ZohoCRM.
     *
     * @param string $url L'URL relativo per la richiesta.
     * @param array $parameters Parametri opzionali per la query string.
     * @return array Risultato della chiamata API.
     * @throws Exception Se la chiamata API fallisce.
     */
    public function get($url, $parameters = [])
    {
        $accessToken = $this->getAccessToken();

        return $this->request([
            CURLOPT_URL            => $url . (!empty($parameters) ? '?' . http_build_query($parameters) : ''),
            CURLOPT_HTTPHEADER => ["Authorization: Zoho-oauthtoken {$accessToken}"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => false,
            CURLOPT_HTTPGET        => true,
        ]);
    }

    /**
     * Effettua una richiesta HTTP POST ad un'API di ZohoCRM.
     *
     * @param string $url L'URL relativo per la richiesta.
     * @param array $parameters Parametri opzionali per la query string.
     * @param array $body Il corpo della richiesta da inviare.
     * @return array Risultato della chiamata API.
     * @throws Exception Se la chiamata API fallisce.
     */
    public function post($url, $parameters = [], $body = [])
    {

        $accessToken = $this->getAccessToken();

        return $this->request([
            CURLOPT_URL            => $url . (!empty($parameters) ? '?' . http_build_query($parameters) : ''),
            CURLOPT_HTTPHEADER => ["Authorization: Zoho-oauthtoken {$accessToken}"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
        ]);
    }

    /**
     * Effettua una richiesta HTTP PUT ad un'API di ZohoCRM.
     *
     * @param string $url L'URL relativo per la richiesta.
     * @param array $parameters Parametri opzionali per la query string.
     * @param array $body Il corpo della richiesta da inviare.
     * @return array Risultato della chiamata API.
     * @throws Exception Se la chiamata API fallisce.
     */
    public function put($url, $parameters = [], $body = [])
    {

        $accessToken = $this->getAccessToken();

        return $this->request([
            CURLOPT_URL            => $url . (!empty($parameters) ? '?' . http_build_query($parameters) : ''),
            CURLOPT_HTTPHEADER => ["Authorization: Zoho-oauthtoken {$accessToken}"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => "PUT",
            CURLOPT_POSTFIELDS     => json_encode($body),
        ]);
    }

    /**
     * Effettua una richiesta HTTP DELETE ad un'API di ZohoCRM.
     *
     * @param string $url L'URL relativo per la richiesta.
     * @param array $parameters Parametri opzionali per la query string.
     * @return array Risultato della chiamata API.
     * @throws Exception Se la chiamata API fallisce.
     */
    public function delete($url, $parameters = [])
    {
        $accessToken = $this->getAccessToken();

        return $this->request([
            CURLOPT_URL            => $url . (!empty($parameters) ? '?' . http_build_query($parameters) : ''),
            CURLOPT_HTTPHEADER => ["Authorization: Zoho-oauthtoken {$accessToken}"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => "DELETE",
        ]);
    }

    /**
     * Ritorna una lista di lead.
     *
     * @param int $page Pagina da recuperare (facoltativo, default 1).
     * @param int $limit Numero di record da recuperare per pagina (facoltativo, default 10).
     * @return array Risultato della chiamata API o errore se la chiamata API fallisce.
     */
    public function getLeads($page = 1, $limit = 10, $fields = ['Last_Name', 'Email'])
    {
        $params = [
            'fields'   => implode(',', $fields),
            'page'     => $page,
            'per_page' => $limit,
        ];

        $url = '/Leads?' . http_build_query($params);
        try {
            return $this->formatResponse($this->get($url));
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'code' => $e->getCode(),
                'error'  => $e->getMessage()
            ];
        }
    }

    /**
     * Ritorna tutti i campi per un lead specifico.
     *
     * @param string $id L'ID del lead.
     * @return array Risultato della chiamata API o errore se la chiamata API fallisce.
     */
    public function getLead($id)
    {

        $url = "/Leads/{$id}";
        try {
            return $this->formatResponse($this->get($url));
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'code' => $e->getCode(),
                'error'  => $e->getMessage()
            ];
        }
    }

    /**
     * Inserisce uno o più lead nel sistema Zoho CRM.
     * 
     * esempio:
     *  {
     *      "Layout": {
     *          "id": "554023000002734009"
     *      },
     *      "Lead_Source": "Employee Referral",
     *      "Company": "ABC",
     *      "Last_Name": "Daly",
     *      "First_Name": "Paul",
     *      "Email": "p.daly@zylker.com",
     *      "State": "Texas"
     *  },
     *
     * @param array $leads Un array di lead da inserire, ciascuno dei quali deve avere l'attributo 'Last_Name'.
     * @param array $options Altre opzioni per la chiamata API (e.g. skip_feature_execution, apply_feature_execution,...).
     * @return array Risultato della chiamata API o errore se l'inserimento fallisce.
     */
    public function insertLeads($leads, $options = [])
    {
        $incomplete = array_filter($leads, function ($lead) {
            return empty($lead['Last_Name']);
        });

        if (!empty($incomplete)) {
            return [
                'status' => 'error',
                'code' => 400,
                'error'  => "Attributo 'Last_Name' mancante da " . count($incomplete) . " record."
            ];
        }

        try {
            return $this->formatResponse($this->post('/Leads', [], array_merge(["data" => $leads], $options)));
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'code' => $e->getCode(),
                'error'  => $e->getMessage()
            ];
        }
    }

    /**
     * Aggiorna uno o più lead nel sistema Zoho CRM.
     * 
     * Per ogni lead, il campo 'id' deve essere specificato.
     * 
     * esempio:
     *  {
     *      "id": "3652397000003852095",
     *      "Last_Name": "Smith",
     *  },
     *
     * @param array $leads Un array di lead da inserire, ciascuno dei quali deve avere l'attributo 'Last_Name'.
     * @param array $options Altre opzioni per la chiamata API (e.g. skip_feature_execution).
     * @return array Risultato della chiamata API o errore se l'inserimento fallisce.
     */

    public function updateLeads($leads, $options = [])
    {
        $missingIds = array_filter($leads, function ($lead) {
            return empty($lead['id']);
        });

        if (!empty($missingIds)) {
            return [
                'status' => 'error',
                'code' => 400,
                'error'  => "Attributo 'id' mancante da " . count($missingIds) . " record."
            ];
        }

        try {
            return $this->formatResponse($this->put('/Leads', [], array_merge(["data" => $leads], $options)));
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'code' => $e->getCode(),
                'error'  => $e->getMessage()
            ];
        }
    }

    /**
     * Cancella uno o più lead dal sistema Zoho CRM.
     * 
     * @param mixed $ids Un array di identificatori di lead da cancellare.
     * @return array Risultato della chiamata API o errore se la cancellazione fallisce.
     */
    public function deleteLeads($ids)
    {
        try {
            return $this->formatResponse($this->delete('/Leads', ['ids' => implode(',', $ids)]));
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'code' => $e->getCode(),
                'error'  => $e->getMessage()
            ];
        }
    }
}
