<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "as_reportool/as_reportool.php");

$ModuloId = 12;

function show_list()
{
    global $user, $HtmlCommon, $dizionario;
    $db = new Database();
    $db->connect();

    $gestore = new Gestore();
    $gestore->conn = $db;
    $ges = $user->GestoreId;
    if (($user->GestoreId == 1) or ($user->GestoreId == 2)) {
        $ges = 1;
    }
    $gestorefigli = $gestore->getGestoreFigli($ges);
    $InGestoreFigli = implode(",", $gestorefigli);
    $post_gestore_id = $_POST['GestoreId'];
    if(isset($post_gestore_id) && $post_gestore_id == -1){
        $filtroGestore = "m.GestoreIdRef IN (SELECT GestoreId FROM Gestore WHERE Stato = 1 AND Cancella = 0 AND GestoreId NOT IN (142,143,1))";
    } elseif(isset($post_gestore_id) && $post_gestore_id == -2){
        $filtroGestore = "m.GestoreIdRef IN (SELECT GestoreId FROM Gestore WHERE Stato = 1 AND Cancella = 0 AND GestoreId NOT IN (142,143))";
    } else {
        $filtroGestore = "m.GestoreIdRef IN ($InGestoreFigli)";
    }
?>

    <div>
        <?php
        // Your MySQL host, login, password and database name.
        // $post_gestore_id valorizzato sopra
        $post_sede_id = $_POST['SedeId'];
        $post_tipo_report = $_POST['tipo_report'];
        $post_dal = $_POST['Dal'];
        $post_al = $_POST['Al'];
        $post_pagamento_tipo_id = $_POST['PagamentoTipoId'];
        $post_tipo_titolo_id = $_POST['TipoTitoloId'];
        $post_ricevuta_id = $_POST['RicevutaId'];
        $post_tipo_data = isset($_POST['TipoData']) ? $_POST['TipoData'] : 'pagamento';

        // Scegli il campo data da usare nelle query
        if ($post_tipo_data == 'scontrino') {
            if ($post_ricevuta_id == 3) {
                $campo_data = 'm.ScontrinoDataAnnullato';
            } else {
                $campo_data = 'm.ScontrinoData';
            }
        } else {
            $campo_data = 'm.DataPagamento';
        }

        $dt = new DT();
        $post_dal_format = $dt->format($post_dal, "d/m/Y", "Y-m-d");
        $post_al_format = $dt->format($post_al, "d/m/Y", "Y-m-d");

        if ($post_tipo_report == 1) {
            $tipo_report1 = "Giornaliero";
            $rep = new CReporTool();

            $dataInizioGestioneExtra = "2016-02-25";

            // Select prima parte per selezionare tutti i pagamenti tranne i coupon senza emissione di titoli
            $q = "SELECT 
                m.GestoreIdRef AS GestoreId,
    m.PrenotazioneMovimentoId,
    g2.RagioneSociale AS RagioneSociale,
    m.ImportoPagato AS ImportoMovimento,
    tp.PagamentoTipo AS TipoPagamento,
    DATE_FORMAT(m.DataPagamento, _UTF8'%Y-%m-%d') AS DataIncasso,
    DATE_FORMAT(m.DataPagamento, _UTF8'%d/%m/%Y') AS DataIncassoF,
    p.CodicePrenotazione AS NumeroItinerario,
    (CASE
        WHEN (m.Causale LIKE '%Residuo%' AND m.Causale NOT LIKE '%coupon%')
        THEN 'Extra'
        ELSE t.TipoTitolo
    END) AS Tipo,
    t.Codice AS CodiceTitolo,
    t.Anno AS AnnoTitolo,
    t.ImportoVenduto AS ImportoTitolo,
    (CASE
        WHEN (m.GestoreIdRef = 1) THEN 0
        ELSE (CASE
            WHEN (t.TipoTitolo = 'R')
            THEN (CASE
                WHEN (pr.ImportoAgenzia > 0) THEN - pr.ImportoAgenzia
                ELSE pr.ImportoAgenzia
            END)
            ELSE pr.ImportoAgenzia
        END)
    END) AS ImportoAgenzia,
    p.ClienteNome AS Passeggero,
    (CASE
        WHEN m.ScontrinoId IS NOT NULL AND m.ScontrinoId <> ''
        THEN CONCAT(
            'n. ', m.ScontrinoId,
            ' data: ', DATE_FORMAT(m.ScontrinoData, '%d/%m/%Y %H:%i'),
            ' invio: ',
            CASE
                WHEN m.ScontrinoDataInvio IS NOT NULL AND m.ScontrinoDataInvio <> ''
                THEN DATE_FORMAT(m.ScontrinoDataInvio, '%d/%m/%Y %H:%i')
                ELSE 'non inviato'
            END,
            CASE
                WHEN m.ScontrinoIdAnnullato IS NOT NULL AND m.ScontrinoIdAnnullato <> ''
                THEN CASE
                    WHEN m.ScontrinoIdAnnullato = m.ScontrinoId
                    THEN CONCAT(', ANNULLATO: ', DATE_FORMAT(m.ScontrinoDataAnnullato, '%d/%m/%Y %H:%i'))
                    ELSE CONCAT(', ANNULLATO SCONTRINO PRECEDENTE n.', m.ScontrinoIdAnnullato, ': ', DATE_FORMAT(m.ScontrinoDataAnnullato, '%d/%m/%Y %H:%i'))
                END
                ELSE ''
            END
        )
        WHEN (m.ScontrinoId IS NULL OR m.ScontrinoId = '') AND m.ScontrinoIdAnnullato IS NOT NULL AND m.ScontrinoIdAnnullato <> ''
        THEN CONCAT(
            'ANNULLATO n. ', m.ScontrinoIdAnnullato,
            ' data: ', DATE_FORMAT(m.ScontrinoDataAnnullato, '%d/%m/%Y %H:%i')
        )
        ELSE '-'
    END) AS DettagliScontrino
            FROM RT_PrenotazioneMovimento m
            LEFT JOIN RT_Prenotazione p ON m.PrenotazioneId = p.PrenotazioneId
            LEFT JOIN RT_PagamentoTipo tp ON m.PagamentoTipoId = tp.PagamentoTipoId
            LEFT JOIN RT_PrenotazioneTitolo t ON (t.PrenotazioneId = m.PrenotazioneId AND t.PrenotazioneId = p.PrenotazioneId)
            LEFT JOIN Gestore AS g2 ON m.GestoreIdRef = g2.GestoreId
            LEFT JOIN RT_PrenotazioneTitoloProvvigione pr ON (t.PrenotazioneTitoloId = pr.PrenotazioneTitoloId AND pr.GestoreId = m.GestoreIdRef)
            LEFT JOIN Gestore AS g ON pr.GestoreId = g.GestoreId
            WHERE ((m.PagamentoTipoId <> 12
        OR (m.PagamentoTipoId = 12 AND m.DataPagamento <= t.DataIns))
        AND m.DataPagamento <= t.DataIns
        AND t.DataIns >= m.DataIns
        AND (
        (
            m.TipoMovimento = 'I'
            AND t.ImportoTitolo > 0
            AND (t.TipoTitolo = 'E' OR t.TipoTitolo = 'X')
            AND m.Causale NOT LIKE '%Residuo%'
            AND (
                (p.PrenotazioneStato = 4 AND m.ScontrinoId IS NOT NULL)
                OR
                (p.PrenotazioneStato = 3 OR p.PrenotazioneStato = 7)
            )
        )
        OR
        (m.TipoMovimento = 'R'
        AND t.TipoTitolo = 'R'
        AND m.ImportoPagato = t.ImportoTitolo
        AND m.DataPagamento <= t.DataIns
        AND m.Causale LIKE CONCAT('%', t.Codice, '%'))
        OR
        (m.TipoMovimento = 'I'
        AND t.ImportoTitolo > 0
        AND t.TipoTitolo = 'E'
        AND (m.Causale LIKE '%Residuo%' OR m.DataPagamento <= t.DataIns)
        AND m.Causale LIKE '%coupon%')
        )
        OR (m.TipoMovimento = 'I'
        AND t.ImportoTitolo > 0
        AND t.TipoTitolo = 'E'
        AND (m.Causale LIKE '%Residuo%'))
        )
            AND m.Importo <> 0
            AND (p.PrenotazioneStato = 3 OR p.PrenotazioneStato = 7 OR p.PrenotazioneStato = 4)
            AND $filtroGestore 
            AND m.OdcIdRef = $user->OdcId 
            AND DATE_FORMAT($campo_data, _UTF8'%Y-%m-%d') >= '$post_dal_format' 
            AND DATE_FORMAT($campo_data, _UTF8'%Y-%m-%d') <= '$post_al_format'
            AND (p.Multi = 0 OR (p.Multi = 1 AND t.TipoTitolo <> 'E'))";
            $qw = "";
            if (isset($post_gestore_id) and ($post_gestore_id > 0)) {
                $qw .= " AND m.GestoreIdRef = $post_gestore_id ";
            }
            if (isset($post_pagamento_tipo_id) and ($post_pagamento_tipo_id > 0)) {
                $qw .= " AND m.PagamentoTipoId = $post_pagamento_tipo_id ";
            }
            if (isset($post_tipo_titolo_id) and ($post_tipo_titolo_id != '')) {
                if ($post_tipo_titolo_id == 'entrate') {
                    $qw .= " AND (t.TipoTitolo = 'E' OR t.TipoTitolo = 'X') ";
                } else {
                    $qw .= " AND t.TipoTitolo = 'R' ";
                }
            }
            // Filtro ricevuta
            if (isset($post_ricevuta_id) && $post_ricevuta_id != 0) {
                if ($post_ricevuta_id == 1) {
                    $qw .= " AND m.ScontrinoId IS NOT NULL AND m.ScontrinoId <> '' ";
                } else if ($post_ricevuta_id == 2) {
                    $qw .= " AND (m.ScontrinoId IS NULL OR m.ScontrinoId = '') ";
                } else if ($post_ricevuta_id == 3) {
                    $qw .= " AND m.ScontrinoDataAnnullato IS NOT NULL ";
                }
            }

            if (isset($post_sede_id) and ($post_sede_id > 0)) {
                $qw .= " AND t.SedeIns = $post_sede_id ";
            }




            $qo = "GROUP BY m.PrenotazioneMovimentoId, CodiceTitolo
            ORDER BY Date(m.DataPagamento) ASC, tp.PagamentoTipo ASC, RagioneSociale ASC, m.DataPagamento ASC, ImportoMovimento DESC";

            $q = $q . $qw . $qo;

            // Select seconda parte per selezionare tutti i pagamenti con coupon senza emissione di titoli
            $q1 = "SELECT
                m.GestoreIdRef AS GestoreId, 
                m.PrenotazioneMovimentoId, 
                g2.RagioneSociale AS RagioneSociale,
                m.Importo AS ImportoMovimento,
                tp.PagamentoTipo AS TipoPagamento,
                DATE_FORMAT(m.DataPagamento, _UTF8'%Y-%m-%d') AS DataIncasso,
                DATE_FORMAT(m.DataPagamento, _UTF8'%d/%m/%Y') AS DataIncassoF,
                p.CodicePrenotazione AS NumeroItinerario,
                (CASE WHEN (m.Causale LIKE '%Residuo%' AND m.Causale NOT LIKE '%coupon%')
                    THEN 'Extra'
                    ELSE t.TipoTitolo
                END) AS Tipo,
                (CASE WHEN (m.DataPagamento < '$dataInizioGestioneExtra' AND m.Causale LIKE '%Residuo%')
                    THEN ''
                    ELSE t.Codice
                END) AS CodiceTitolo,
                (CASE WHEN (m.DataPagamento < '$dataInizioGestioneExtra' AND m.Causale LIKE '%Residuo%')
                    THEN ''
                    ELSE t.Anno
                END) AS AnnoTitolo,
                '' AS ImportoTitolo,
                '' AS ImportoAgenzia,
                p.ClienteNome AS Passeggero,
                '-' AS DettagliScontrino
            FROM RT_PrenotazioneMovimento m
            LEFT JOIN RT_Prenotazione p ON m.PrenotazioneId = p.PrenotazioneId
            LEFT JOIN RT_PagamentoTipo tp ON m.PagamentoTipoId = tp.PagamentoTipoId
            LEFT JOIN RT_PrenotazioneTitolo t ON (t.PrenotazioneId = m.PrenotazioneId AND t.PrenotazioneId = p.PrenotazioneId)
            LEFT JOIN Gestore AS g2 ON m.GestoreIdRef = g2.GestoreId
            LEFT JOIN RT_PrenotazioneTitoloProvvigione pr ON (t.PrenotazioneTitoloId = pr.PrenotazioneTitoloId AND pr.GestoreId = m.GestoreIdRef)
            LEFT JOIN Gestore AS g ON pr.GestoreId = g.GestoreId
            WHERE (m.PagamentoTipoId = 12 
                AND DATE(m.DataPagamento) <> DATE(t.DataIns) 
                AND (DATE_SUB(t.DataIns, INTERVAL 120 SECOND) <= m.DataIns OR DATE_SUB(t.DataIns, INTERVAL 120 SECOND) <= m.DataAgg) 
                AND t.DataIns >= m.DataIns 
                AND ((m.DataPagamento < '$dataInizioGestioneExtra' AND m.TipoMovimento = 'I' AND t.ImportoTitolo > 0) 
                    OR (m.DataPagamento >= '$dataInizioGestioneExtra' AND m.TipoMovimento = 'I' AND t.ImportoTitolo > 0 AND t.TipoTitolo = 'E' AND m.Causale NOT LIKE '%Residuo%') 
                    OR (m.DataPagamento >= '$dataInizioGestioneExtra' AND m.TipoMovimento = 'I' AND t.ImportoTitolo > 0 AND t.TipoTitolo = 'E' AND m.Causale LIKE '%Residuo%' AND t.DataIns >= m.DataIns AND t.TipologiaBigliettoId IN (SELECT TipologiaBigliettoId FROM RT_TipologiaBiglietto WHERE OccupaPosto = 0))
                    OR (m.DataPagamento >= '$dataInizioGestioneExtra' AND m.TipoMovimento = 'I' AND t.ImportoTitolo > 0 AND t.TipoTitolo = 'X' AND m.Causale LIKE '%Residuo%') 
                    OR (m.DataPagamento < '2018-02-27' AND m.TipoMovimento = 'R' AND t.TipoTitolo = 'R' AND m.ImportoPagato = t.ImportoTitolo) 
                    OR (m.DataPagamento >= '2018-02-27' AND m.TipoMovimento = 'R' AND t.TipoTitolo = 'R' AND m.ImportoPagato = t.ImportoTitolo AND m.Causale LIKE CONCAT('%', t.Codice, '%')) 
                    OR (m.DataPagamento >= '$dataInizioGestioneExtra' AND m.TipoMovimento = 'I' AND t.ImportoTitolo > 0 AND t.TipoTitolo = 'E' AND ((m.Causale LIKE '%Residuo%' AND m.Causale LIKE '%coupon%') OR (m.DataPagamento <> t.DataIns AND m.PagamentoTipoId = 12))))) 
                AND m.Importo = m.ImportoPagato 
                AND m.Importo <> 0 
                AND (p.PrenotazioneStato = 3 OR p.PrenotazioneStato = 7) 
                AND $filtroGestore 
                AND m.OdcIdRef = $user->OdcId 
                AND DATE_FORMAT($campo_data, _UTF8'%Y-%m-%d') >= '$post_dal_format' 
                AND DATE_FORMAT($campo_data, _UTF8'%Y-%m-%d') <= '$post_al_format' ";
            $qw1 = "";
            if (isset($post_gestore_id) and ($post_gestore_id > 0)) {
                $qw1 .= " AND m.GestoreIdRef = $post_gestore_id ";
            }
            if (isset($post_pagamento_tipo_id) and ($post_pagamento_tipo_id > 0)) {
                $qw1 .= " AND m.PagamentoTipoId = $post_pagamento_tipo_id ";
            }
            if (isset($post_tipo_titolo_id) and ($post_tipo_titolo_id != '')) {
                if ($post_tipo_titolo_id == 'entrate') {
                    $qw1 .= " AND (t.TipoTitolo = 'E' OR t.TipoTitolo = 'X') ";
                } else {
                    $qw1 .= " AND t.TipoTitolo = 'R' ";
                }
            }
            if (isset($post_sede_id) and ($post_sede_id > 0)) {
                $qw1 .= " AND t.SedeIns = $post_sede_id ";
            }

            $qo1 = "GROUP BY m.PrenotazioneMovimentoId 
            ORDER BY Date(m.DataPagamento) ASC, tp.PagamentoTipo ASC, RagioneSociale ASC, m.DataPagamento ASC, ImportoMovimento DESC";

            $q1 = $q1 . $qw1 . $qo1;

            // Per le prenotazioni multi
    
            $q2 = "
		SELECT m.GestoreIdRef as GestoreId, 
m.PrenotazioneMovimentoId, 
g2.RagioneSociale as RagioneSociale, 
(case when (m.Causale like '%Residuo%' and m.Causale not like '%dopo coupon%') or (t.TipoTitolo = 'X' and m.DataPagamento = date(t.DataIns)) 
	THEN m.Importo 
	ELSE (case when (t.TipoTitolo = 'R') THEN m.Importo 
		ELSE m.Importo / (Select  COALESCE(sum(pa.TotalePaxPrenotati),0)  from RT_PrenotazioneMovimento ma
right join(
select pa1.*, pa2.PrenotazioneId as PrenotazioneIdPrincipale from RT_Prenotazione pa1
left join RT_Prenotazione pa2 on pa1.CodicePrenotazione = pa2.CodicePrenotazione and pa1.PrenotazioneStato = pa2.PrenotazioneStato
where 
pa1.PrenotazioneStato = 3 and pa1.Multi = 1
and pa2.TotalePagato > 0
group by pa1.PrenotazioneId) as pa on pa.PrenotazioneId = ma.PrenotazioneId or pa.PrenotazioneIdPrincipale = ma.PrenotazioneId
where ma.PrenotazioneMovimentoId = m.PrenotazioneMovimentoId)
		END) 
	END) as ImportoMovimento,
 tp.PagamentoTipo as TipoPagamento, 
date_format(m.DataPagamento,_utf8'%Y-%m-%d') AS DataIncasso, 
date_format(m.DataPagamento,_utf8'%d/%m/%Y') AS DataIncassoF, 
p.CodicePrenotazione as NumeroItinerario, 
(case when (m.Causale like '%Residuo%' and m.Causale not like '%coupon%') THEN 'Extra' ELSE t.TipoTitolo END) as Tipo, 
(case when (m.DataPagamento < '$dataInizioGestioneExtra' and m.Causale like '%Residuo%') THEN '' ELSE t.Codice END) as CodiceTitolo, 
(case when (m.DataPagamento < '$dataInizioGestioneExtra' and m.Causale like '%Residuo%') THEN '' ELSE t.Anno END) as AnnoTitolo, 
(case when ((m.DataPagamento < '$dataInizioGestioneExtra' and m.Causale like '%Residuo%') or (m.DataPagamento >= '$dataInizioGestioneExtra' and m.Causale not like '%Residuo%' and m.Causale like '%coupon%') ) THEN '' ELSE t.ImportoTitolo END) as ImportoTitolo,
(case when (m.GestoreIdRef = 1) THEN 0 ELSE (case when ((m.DataPagamento < '$dataInizioGestioneExtra' and m.Causale like '%Residuo%') or (m.DataPagamento >= '$dataInizioGestioneExtra' and m.Causale not like '%Residuo%' and m.Causale like '%coupon%') ) THEN '' ELSE (case when (t.TipoTitolo = 'R') THEN (case when (pr.ImportoAgenzia > 0) THEN -pr.ImportoAgenzia ELSE pr.ImportoAgenzia END) ELSE pr.ImportoAgenzia END) END) END) as ImportoAgenzia,
p.ClienteNome as Passeggero,
'-' as DettagliScontrino 

FROM RT_PrenotazioneMovimento m 
right join(
select  p2.PrenotazioneId as PrenotazioneIdPrincipale, p1.* from RT_Prenotazione p1
left join RT_Prenotazione p2 on p1.CodicePrenotazione = p2.CodicePrenotazione and p1.PrenotazioneStato = p2.PrenotazioneStato
where 
p1.PrenotazioneStato = 3 and p1.Multi = 1
and p2.TotalePagato > 0
group by p1.PrenotazioneId) p on p.PrenotazioneIdPrincipale = m.PrenotazioneId
left JOIN RT_PagamentoTipo tp ON m.PagamentoTipoId = tp.PagamentoTipoId 
left JOIN RT_PrenotazioneTitolo t ON (t.PrenotazioneId = p.PrenotazioneId) 
left JOIN Gestore as g2 ON m.GestoreIdRef = g2.GestoreId 
left JOIN RT_PrenotazioneTitoloProvvigione pr ON t.PrenotazioneTitoloId = pr.PrenotazioneTitoloId 
left JOIN Gestore as g ON pr.GestoreId = g.GestoreId 

where ((m.PagamentoTipoId <> 12 or (m.PagamentoTipoId = 12 and m.DataPagamento = t.DataIns)) 
	and date(m.DataPagamento) = date(t.DataIns) and (DATE_SUB(t.DataIns,INTERVAL 60 SECOND) <= m.DataIns or DATE_SUB(t.DataIns,INTERVAL 60 SECOND) <= m.DataAgg) and t.DataIns >= m.DataIns 
	and ((m.DataPagamento < '$dataInizioGestioneExtra' and m.TipoMovimento = 'I' and t.ImportoTitolo >0) 
or (m.DataPagamento >= '$dataInizioGestioneExtra' and m.TipoMovimento = 'I' and t.ImportoTitolo >0 and t.TipoTitolo = 'E' and m.Causale not like '%Residuo%') 
or (m.DataPagamento >= '$dataInizioGestioneExtra' and m.TipoMovimento = 'I' and t.ImportoTitolo >0 and t.TipoTitolo = 'E' and m.Causale like '%Residuo%' and t.DataIns >= m.DataIns and t.TipologiaBigliettoId in (Select TipologiaBigliettoId from RT_TipologiaBiglietto where OccupaPosto = 0))
or (m.DataPagamento >= '$dataInizioGestioneExtra' and m.TipoMovimento = 'I' and t.ImportoTitolo >0 and t.TipoTitolo = 'X' and (m.Causale like '%Residuo%' or m.DataPagamento = date(t.DataIns))) 
or (m.TipoMovimento = 'R' and t.TipoTitolo = 'R' and m.ImportoPagato = t.ImportoTitolo and time(m.DataPagamento) <= time(t.DataIns) and ((second(m.DataPagamento) +10 > second(t.DataIns) ))) 
or (m.DataPagamento >= '$dataInizioGestioneExtra' and m.TipoMovimento = 'I' and t.ImportoTitolo >0 and t.TipoTitolo = 'E' and (m.Causale like '%Residuo%' or m.DataPagamento = date(t.DataIns)) and m.Causale like '%coupon%'))) 
and (m.Importo + m.Supplemento) = m.ImportoPagato 
and m.Importo<>0 
and (p.PrenotazioneStato = 3 or p.PrenotazioneStato = 7) 
and $filtroGestore 
and m.OdcIdRef=$user->OdcId 
and date_format($campo_data,_utf8'%Y-%m-%d')>='$post_dal_format' 
and date_format($campo_data,_utf8'%Y-%m-%d')<='$post_al_format' ";

            $q2w = "";
            if (isset($post_gestore_id) and ($post_gestore_id > 0))
                $q2w .= " and m.GestoreIdRef=$post_gestore_id ";
            if (isset($post_pagamento_tipo_id) and ($post_pagamento_tipo_id > 0))
                $q2w .= " and m.PagamentoTipoId = $post_pagamento_tipo_id ";
            if (isset($post_tipo_titolo_id) and ($post_tipo_titolo_id != '')) {
                if ($post_tipo_titolo_id == 'entrate') {
                    $q2w .= " and (t.TipoTitolo = 'E' or  t.TipoTitolo = 'X') ";
                } else {
                    $q2w .= " and t.TipoTitolo = 'R' ";
                }
            }
            if (isset($post_sede_id) and ($post_sede_id > 0)) {
                $q2w .= " and t.SedeIns = $post_sede_id ";
            }

            $q2o = "group by m.PrenotazioneMovimentoId, CodiceTitolo
		order by Date(m.DataPagamento) asc, tp.PagamentoTipo asc, RagioneSociale asc, m.DataPagamento asc, ImportoMovimento desc";

            $q2 = $q2 . $q2w . $q2o;
            // echo $q2;
            $q = "Select * from ( (" . $q . ") UNION ALL (" . $q1 . ") UNION ALL (" . $q2 . ")) a 
		order by Date(DataIncasso) asc, TipoPagamento asc, RagioneSociale asc, ImportoMovimento desc";


            //   echo($q);
            $rep->SetQuery($q);
            $rep->AddGroupingField('DataIncassoF', 'DataIncassoF ', $dizionario['generale']['data'] . ': ', $dizionario['stampe']['tot_data'] . ': %name%');
            $rep->AddGroupingField('TipoPagamento', 'TipoPagamento ', $dizionario['stampe']['pagamento'] . ': ', $dizionario['stampe']['tot_pagamento'] . ': %name%');
            $rep->AddGroupingField('RagioneSociale', 'RagioneSociale ', $dizionario['gestore']['gestore'] . ': ', $dizionario['stampe']['totali_agenzia'] . ': %name%');

            $rep->AddField('TipoPagamento', $dizionario['stampe']['tot_pagamento']);
            //$rep->AddField('RagioneSociale','Agenzia');
            $rep->AddField('RagioneSociale', $dizionario['stampe']['agenzia_movimento']);

            $rep->AddField('DataIncassoF', $dizionario['generale']['data']);
            $rep->AddField('Tipo', $dizionario['generale']['tipo']);
            $rep->AddField('Passeggero', $dizionario['stampe']['passeggero']);


            $rep->AddField('CodiceTitolo', $dizionario['generale']['biglietto']);

            $rep->AddField('ImportoMovimento', $dizionario['stampe']['importo_movimento'] . ' (&euro;)', 1, '', 'money');
            $rep->AddField('ImportoTitolo', $dizionario['stampe']['importo_totale'] . ' (&euro;)', 1, '', 'money');
            $rep->AddField('ImportoAgenzia', $dizionario['gestore']['provvigione'] . ' (&euro;)', 1, '', 'money');
            $rep->AddField('DettagliScontrino', $dizionario['conto']['ricevuta']);


            $rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
            $rep->SetNumberDelimiters(',', '.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter
            $rep->SetSummary('<strong>' . $dizionario['stampe']['totali'] . ' &euro; <strong> ');



        }
        if(isset($post_gestore_id) && $post_gestore_id == -1){
            $gestore = "Tutte le agenzie";
        } else if(isset($post_gestore_id) && $post_gestore_id == -2){
            $gestore = "Tutte le agenzie e Bertoldi Boats";
        } else {
            $gestore = "Tutti";
        }
        
        $sede = "Tutte";

        if ($post_gestore_id > 0) {
            $sql = "SELECT RagioneSociale from Gestore where GestoreId=$post_gestore_id";
            $row = $db->query_first($sql);
            if (!empty($row['RagioneSociale']))
                $gestore = $row['RagioneSociale'];



        }



        $titolo_report = $dizionario['menu_voci']['42'] . "<small><a href='#' class='exportToExcel'>" . $dizionario['stampe']['esporta'] . "</a></small><br />";
        $titolo_report .= "<br />" . $dizionario['stampe']['periodo_considerato'] . " " . $post_dal . " " . $dizionario['generale']['al'] . " " . $post_al;
        $titolo_report .= "<br />" . $dizionario['gestore']['gestore'] . ": " . $gestore;





        $rep->DrawReport($titolo_report);
        ?>


    </div>

    <style>
        .exportToExcel {
            font-size: 12px !important;
            margin-left: 10px !important;
        }
    </style>
    <script src="/js/jquery.table2excel.js"> </script>

    <script>
        $(document).ready(function () {
            $(".exportToExcel").click(function (e) {
                $(".report_excel").table2excel({
                    exclude: ".noExl",
                    name: "Pagamenti",
                    filename: "pagamenti" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
                    fileext: ".xls",
                    exclude_img: true,
                    exclude_links: true,
                    exclude_inputs: true,
                    preserveColors: true
                });
            });

        });
    </script>
    <?php

}



if (is_object($user)) {

    $db = new Database();
    $db->connect();
    $user->conn = $db;
    $permessi = $user->get_permessi_modulo($ModuloId);

    if (!isset($_REQUEST['do'])) {
        $do = '';
    } else {
        $do = $_REQUEST['do'];
    }


    switch ($do) {



        default:
            $FunzioneId = 1;
            $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

            if (sizeof($permesso))
                show_list();


            break;
    }




}
// se l'utente non è loggato
else {
    header("Location: /logout.php");
}
?>
