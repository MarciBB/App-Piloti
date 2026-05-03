<?php
/* Configurazione log */

include_once('c_path_include.php');

$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$storico_include=$basepath.'/protected/classes/class.StoricoOperazioni.php';
$operatore_include=$basepath.'/protected/classes/class.Operatore.php';
$dt_include=$basepath.'/protected/classes/class.DT.php';

include_once($config_include);
include_once($database_include);
include_once($dt_include);
include_once($operatore_include);
include_once($storico_include);

global $user, $db;
$config = new Config();
$run=$config->loadCron($type);


$classespath_ = $basepath."/protected/classes/";
$modulespath_ = $basepath."/protected/modules/";
include_once ($classespath_ . "Graph/Graph.php");
include_once($classespath_."class.Operatore.php");
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."class.PagamentoTipo.php");
include_once($classespath_."class.PrefissoTelefono.php");
include_once ($classespath_ . "Graph/class.DisponibilitaGraph.php");
include_once ($classespath_ . "Graph/class.GrafoTratte.php");
include_once ($classespath_ . "Graph/class.GraphUtil.php");
include_once($classespath_."class.ServiceFatturaInCloud.php");

$db = new Database();
$db->connect();


$currentDate = date('Y-m-d H:i:s', time() - 60 * 60 * 24);
$currentMese = date('m', time() - 60 * 60 * 24);
$temp = explode('-',$currentDate);
$currentAnno = $temp[0];
//fattura agenzie non verificate
if($currentMese >= 1 && $currentMese <= 3) {
	$inizioTrimestre = $currentAnno."-01-01";
	$fineTrimestre = $currentAnno."-03-31";
}
if($currentMese >= 4 && $currentMese <= 6) {
	$inizioTrimestre = $currentAnno."-04-01";
	$fineTrimestre = $currentAnno."-06-30";
}
if($currentMese >= 7 && $currentMese <= 9) {
	$inizioTrimestre = $currentAnno."-07-01";
	$fineTrimestre = $currentAnno."-09-30";
}
if($currentMese >= 10 && $currentMese <= 12) {
	$inizioTrimestre = $currentAnno."-10-01";
	$fineTrimestre = $currentAnno."-12-31";
}

$service = new ServiceFatturaInCloud($db);

if($currentMese == 3 || $currentMese == 6 || $currentMese == 9 || $currentMese == 12) {
    //invio fattura trimestrale
    //agenzie non verificate ITA
    $sql = "SELECT prov.GestoreId as GestoreId, SUM(prov.ImportoAgenzia) as Tot
            FROM  RT_PrenotazioneTitoloProvvigione prov
            left join RT_PrenotazioneTitolo t on t.PrenotazioneTitoloId = prov.PrenotazioneTitoloId
            Where GestoreId IN (SELECT g.GestoreId FROM Gestore g
            left join Comune c on g.ComuneId = c.ComuneId
            left join Provincia p on c.provincia = p.ProvinciaId
            left join Regione r on p.RegioneId = r.RegioneId
            where Verificato = 0 and Stato = 1
            and r.idnazione = 1)
            and prov.Stato = 1
            and t.Stato = 1 and t.Cancella = 0
            and prov.DATE(DataIns) >= '$inizioTrimestre' and DATE(prov.DataIns) <= '$fineTrimestre'
            group by prov.GestoreId";
    
    $rows = $db->fetch_array($sql);
    
    
    foreach ($rows as $agenzia){
        if($agenzia['Tot'] > 0) {
            $articolo_nome = "Commissioni dal ".$inizioTrimestre." - ".$fineTrimestre;
            if(!$service->existFattura($agenzia['GestoreId'], $articolo_nome)){
                $gestoreId = $agenzia['GestoreId'];
                $sql = "SELECT g.*, c.Comune, p.Sigla as Provincia from Gestore g
                        LEFT JOIN Comune c ON c.ComuneId = g.ComuneId
                        LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
                        WHERE GestoreId = $gestoreId";
                $gestore = $db->query_first($sql);
                
                $sql = "SELECT Max(Progressivo) as Ultimo
                        FROM FatturaInCloudAgenzia
                        WHERE Verificata = 0 and Anno = ".date('Y');
                
                $temp1 = $db->query_first($sql);
                if(!isset($temp1['Ultimo'])) {
                    $progressivo = 1;
                } else {
                    $progressivo = $temp1['Ultimo'] +1;
                }
                $fatturaNumero = $progressivo."-AGE/".date('Y');
                $nome = $gestore['RagioneSociale'];
                $indirizzo_via = $gestore['Indirizzo'];
                $indirizzo_cap = $gestore['Cap'];
                $indirizzo_provincia = $gestore['Provincia'];
                $indirizzo_citta = $gestore['Comune'];
                $paese = 'Italia';
                $paese_iso = 'IT';
                $lingua = 'it';
                $piva = $gestore['PartitaIva'];
                $cf = $gestore['CodiceFiscale'];
                $pec = $gestore['EmailPec'];
                $email = $gestore['Email'];
                $tel = $gestore['Telefono'];
                $fax = $gestore['Fax'];
                $codice_destinatario = "";
                $articolo_quantita = 1;
                $articolo_nota = "";
                $articolo_prezzo_netto = $agenzia['Tot'];
                $articolo_prezzo_lordo = $agenzia['Tot'];
                $pagamento_importo = $agenzia['Tot'];
                $pagamento_data_scadenza = date('d/m/Y',strtotime(date("d-m-Y")));
                $pagamento_data_saldo = date('d/m/Y',strtotime(date("d-m-Y")));
                $fa_data = date('d/m/Y',strtotime(date("d-m-Y")));
                
                $service->inviaFatturaAgenzia(0, $gestoreId, $nome, $indirizzo_via, $indirizzo_cap, $indirizzo_provincia, $indirizzo_citta,
                    $paese, $paese_iso, $lingua, $piva, $cf, $articolo_nome, $articolo_quantita, $articolo_nota, $articolo_prezzo_netto,
                    $articolo_prezzo_lordo, $pagamento_data_scadenza, $pagamento_importo, $pagamento_data_saldo, $codice_destinatario, $pec,
                    $email, $tel, $fax, $fa_data, $fatturaNumero, $progressivo, '-AGE');
            }
        }
    }

}


//invio fattura mensile
//agenzie verificate ITA
//$currentMese = $currentMese-1;
$inizioMese = $currentAnno."-".$currentMese."-01";
$fineMese = $currentAnno."-".$currentMese."-".date("t", strtotime($inizioMese));
$sql = "SELECT prov.GestoreId as GestoreId, SUM(prov.ImportoAgenzia) as Tot
        FROM  RT_PrenotazioneTitoloProvvigione prov
        left join RT_PrenotazioneTitolo t on t.PrenotazioneTitoloId = prov.PrenotazioneTitoloId
        Where GestoreId IN (SELECT g.GestoreId FROM Gestore g
        left join Comune c on g.ComuneId = c.ComuneId
        left join Provincia p on c.provincia = p.ProvinciaId
        left join Regione r on p.RegioneId = r.RegioneId
        where Verificato = 1 and Stato = 1
        and r.idnazione = 1 AND GestoreId <> 1 AND GestoreId <> 369)
        and prov.Stato = 1
        and t.Stato = 1 and t.Cancella = 0
        and DATE(prov.DataIns) >= '$inizioMese' and DATE(prov.DataIns) <= '$fineMese'
        group by prov.GestoreId";

$rows = $db->fetch_array($sql);

foreach ($rows as $agenzia){
    if($agenzia['Tot'] > 0) {
        $articolo_nome = "Commissioni dal ".$inizioMese." - ".$fineMese;
        if(!$service->existFattura($agenzia['GestoreId'], $articolo_nome)){
            $gestoreId = $agenzia['GestoreId'];
            $sql = "SELECT g.*, c.Comune, p.Sigla as Provincia from Gestore g
                    LEFT JOIN Comune c ON c.ComuneId = g.ComuneId
                    LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
                    WHERE GestoreId = $gestoreId";
            $gestore = $db->query_first($sql);
            
            $sql = "SELECT Max(Progressivo) as Ultimo
                    FROM FatturaInCloudAgenzia
                    WHERE Verificata = 1 and Anno = ".date('Y');
            $temp1 = $db->query_first($sql);
            if(!isset($temp1['Ultimo'])) {
                $progressivo = 1;
            } else {
                $progressivo = $temp1['Ultimo'] +1;
            }
            $fatturaNumero = $progressivo."-AGED/".date('Y');
            
            $nome = $gestore['RagioneSociale'];
            $indirizzo_via = $gestore['Indirizzo'];
            $indirizzo_cap = $gestore['Cap'];
            $indirizzo_provincia = $gestore['Provincia'];
            $indirizzo_citta = $gestore['Comune'];
            $paese = 'Italia';
            $paese_iso = 'IT';
            $lingua = 'it';
            $piva = $gestore['PartitaIva'];
            $cf = $gestore['CodiceFiscale'];
            $pec = $gestore['EmailPec'];
            $email = $gestore['Email'];
            $tel = $gestore['Telefono'];
            $fax = $gestore['Fax'];
            $codice_destinatario = "";
            $articolo_quantita = 1;
            $articolo_nota = "";
            $articolo_prezzo_netto = $agenzia['Tot'];
            $articolo_prezzo_lordo = $agenzia['Tot'];
            $pagamento_importo = $agenzia['Tot'];
            $pagamento_data_scadenza = date('d/m/Y',strtotime(date("d-m-Y", time() + 3 * 60 * 60 * 24)));
            $pagamento_data_saldo = "";//date('d/m/Y',strtotime(date("d-m-Y")));
            $fa_data = date('d/m/Y',strtotime(date("d-m-Y")));
            
            $service->inviaFatturaAgenzia(1, $gestoreId, $nome, $indirizzo_via, $indirizzo_cap, $indirizzo_provincia, $indirizzo_citta,
                $paese, $paese_iso, $lingua, $piva, $cf, $articolo_nome, $articolo_quantita, $articolo_nota, $articolo_prezzo_netto,
                $articolo_prezzo_lordo, $pagamento_data_scadenza, $pagamento_importo, $pagamento_data_saldo, $codice_destinatario, $pec,
                $email, $tel, $fax, $fa_data, $fatturaNumero, $progressivo, '-AGED');
        }
    }
}

// ELENCO FUNZIONI



?>