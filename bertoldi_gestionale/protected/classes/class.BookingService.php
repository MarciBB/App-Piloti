<?php
class BookingService {
    public $conn;
    public function getBookings($offset = null, $limit = null) {
        global $user;
        $db = $this->conn;
        $limitSql = "";
        if (is_numeric($limit) && intval($limit) > 0) {
            if (is_numeric($offset) && intval($offset) >= 0) {
                $limitSql = " LIMIT " . intval($offset) . ", " . intval($limit);
            } else {
                $limitSql = " LIMIT " . intval($limit);
            }
        }
        $sql = "
        SELECT 
            p.PrenotazioneId,
            p.CodicePrenotazione AS PrenotazioneCodice,
            st.PrenotazioneStato AS PrenotazioneStato,
            g.RagioneSociale AS Agenzia,
            DATE_FORMAT(p.DataIns, _utf8 '%d/%m/%Y %H:%i:%s') AS DataCreazione,
            operatore.Username AS UtenteCreatore,
            pp.LineaNome AS LineaNome,
            DATE_FORMAT(pp.DataOraSalita, _utf8 '%d/%m/%Y') AS CorsaDataPartenza,
            DATE_FORMAT(pp.DataOraSalita, _utf8 '%H:%i') AS CorsaOrarioPartenza,
            pp.ComuneSalita AS ComuneSalita,
            pp.ComuneDiscesa AS ComuneDiscesa,
            IF((p.TipoTour = 1), _utf8 'Privato / Personalizzato', _utf8 'Gruppo') AS TipoTour,
            (p.TotalePaxPrenotati - pp.PasseggeriEsclusi) AS TotalePostiPrenotati,
            p.TotalePrenotazione AS TotalePrenotazione,
            f.Modello AS Imbarcazione,
            CASE 
                WHEN p.GestoreIdRef = 1 THEN 0
                ELSE (
                    SELECT SUM(tp.ImportoAgenzia)
                    FROM RT_PrenotazioneTitolo t
                    LEFT JOIN RT_PrenotazioneTitoloProvvigione tp ON t.PrenotazioneTitoloId = tp.PrenotazioneTitoloId
                    WHERE t.PrenotazioneId = p.PrenotazioneId
                      AND tp.GestoreId <> 1
                      AND t.Stato = 1
                      AND t.Cancella = 0
                )
            END AS Provvigioni,
            ROUND(p.TotalePrenotazione / 1.05, 2) AS Imponibile,
            ROUND(p.TotalePrenotazione - (p.TotalePrenotazione / 1.05), 2) AS IVA,
            '5%' AS PercentualeIVA,
            mp.MetodoPagamento AS MetodoPagamento,
            o.Odc AS AziendaEmittente
        FROM RT_Prenotazione p
        JOIN RT_AppPrenotazioneStato st ON (p.PrenotazioneStato = st.PrenotazioneStatoId)
        JOIN RT_PrenotazionePercorso pp ON (p.PrenotazioneId = pp.PrenotazioneId)
        LEFT JOIN RT_Corsa c ON (c.CorsaId = pp.CorsaId)
        LEFT JOIN RT_Flotta f ON (c.FlottaDefaultId = f.FlottaId)
        JOIN Operatore operatore ON (p.OpeIns = operatore.OperatoreId)
        JOIN Gestore g ON (p.GestoreIdRef = g.GestoreId)
        LEFT JOIN Odc o ON (p.OdcIdRef = o.OdcId)
        LEFT JOIN (
            SELECT 
                m1.PrenotazioneId,
                tp.PagamentoTipo AS MetodoPagamento
            FROM RT_PrenotazioneMovimento m1
            JOIN (
                SELECT PrenotazioneId, MIN(PrenotazioneMovimentoId) AS MinMovId
                FROM RT_PrenotazioneMovimento
                WHERE TipoMovimento = 'I' AND Stato = 1 AND Cancella = 0 AND OdcIdRef = $user->OdcId
                GROUP BY PrenotazioneId
            ) x ON (x.PrenotazioneId = m1.PrenotazioneId AND x.MinMovId = m1.PrenotazioneMovimentoId)
            LEFT JOIN RT_PagamentoTipo tp ON (m1.PagamentoTipoId = tp.PagamentoTipoId)
        ) mp ON (mp.PrenotazioneId = p.PrenotazioneId)
        
        
        WHERE p.Stato = 1 
          AND p.Cancella = 0
          AND p.OdcIdRef = $user->OdcId
        GROUP BY p.PrenotazioneId, pp.PrenotazionePercorsoId
        ORDER BY p.PrenotazioneId DESC" . $limitSql . "
        ";
        return $db->fetch_array($sql);
    }
}
?>
