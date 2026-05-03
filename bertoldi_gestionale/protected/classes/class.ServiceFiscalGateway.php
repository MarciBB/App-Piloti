<?php
/**
 * Classe per incapsulare le funzioni di Fiscal Gateway
 *
 * @author L.Casaburi
 */
class ServiceFiscalGateway
{
    private $baseUrl;
    private $authentication;
    private $accountCode;
	private $storeId;

    public function __construct($baseUrl, $authentication, $accountCode, $storeId)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->authentication = $authentication;
        $this->accountCode = $accountCode;
		$this->storeId = $storeId;
    }

    private function request($method, $endpoint, $queryParams = [], $body = null)
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $ch = curl_init($url);

        $headers = [
            "authentication: {$this->authentication}"
        ];

        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status_code' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }

    public function getBills($params = ['v' => 1])
    {
        return $this->request('GET', "$this->accountCode/bills", $params);
    }

    public function putWebhook($webhookUrl)
    {
        $body = ['url' => $webhookUrl, 'headers' => []];
        return $this->request('PUT', "$this->accountCode/webhook", [], $body);
    }

    public function getBillDownloadUrl($receiptId)
    {
        return $this->request('GET', "$this->accountCode/receipt/$receiptId/download-url");
    }

    public function deleteBill($receiptId)
    {
        return $this->request('DELETE', "$this->accountCode/receipt/$receiptId");
    }

    public function postBillReceipt($orderId, $paymentMethodsType = 'CASH', $amount, $productId, $vat = 5)
    {
        $body = [
            'order_id' => $orderId,
            'store_id' => $this->storeId,
			'payment_methods' => [
									[
										'type' => $paymentMethodsType,
										'amount' => $amount
									]
								],
            'rows' => [
						[
							'id' => $productId,
							'name' => 'Ticket Bertoldi Boats',
							'price' => $amount,
							'quantity' => 1,
							'VAT' => $vat,
							'subtotal' => false
						]
					]
        ];
        return $this->request('POST', "$this->accountCode/bill/receipt", [], $body);
    }

    public function postBillCreditNote($orderId, $creditNoteId, $note, $productId, $amount, $vat = 5)
    {
        $body = [
            'order_id' => $orderId,
            'credit_note_id' => $creditNoteId,
            'store_id' => $this->storeId,
            'note' => $note,
            'rows' => [
						[
							'id' => $productId,
							'name' => 'Ticket Bertoldi Boats',
							'price' => $amount,
							'quantity' => 1,
							'VAT' => $vat,
							'subtotal' => false
						]
					]
        ];
		
        return $this->request('POST', "$this->accountCode/bill/credit-note", [], $body);
    }


    public function getNumeroOrdine($db) {
        $sql = "SELECT MAX(ScontrinoId) AS max FROM RT_PrenotazioneMovimento WHERE ScontrinoTipo = 1";
        $orderRow = $db->query_first($sql);
        if(isset($orderRow['max'])) {
            $orderId = intval($orderRow['max']) + 1;
        } else {
            $orderId = 1;
        }
        $orderId = strval($orderId);
        return $orderId;
    }

    public function getTipoPagamento($PagamentoTipoId) {

        switch ($PagamentoTipoId) {
            case 1:
                $paymentMethodsType = 'CASH'; // Contanti
                break;
            case 2:
                $paymentMethodsType = 'CARD'; // Postapay
                break;
            case 3:
                $paymentMethodsType = 'CARD'; // Carta di credito su POS fisico
                break;
            case 4:
                $paymentMethodsType = 'BANK_TRANSFER'; // Bonifico Bancario
                break;
            case 5:
                $paymentMethodsType = 'CARD'; // PayPal (considerato pagamento elettronico)
                break;
            case 6:
                $paymentMethodsType = 'CARD'; // Agenzia
                break;
            case 7:
                $paymentMethodsType = 'CASH'; // A bordo (NO RICEVUTA) 
                break;
            case 12:
                $paymentMethodsType = 'CARD'; // Coupon (NO RICEVUTA) 
                break;
            case 22:
                $paymentMethodsType = 'CARD'; // Stripe (pagamento con carta)
                break;
            case 23:
                $paymentMethodsType = 'CARD'; // Pagamento in hotel (ipotizziamo contanti)
                break;
            default:
                $paymentMethodsType = 'CARD'; // Caso predefinito per nuovi/metodi non mappati
                
        }
        return $paymentMethodsType;
    }
}
