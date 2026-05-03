<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];

include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['area_clienti']['coupon'];
$PageDescription = $dizionario['area_clienti']['coupon_desc'];
$PageKeywords = $dizionario['area_clienti']['coupon_key'];

$page_title = $dizionario['area_clienti']['coupon'];
$page_parent = $dizionario['area_clienti']['area'];

$LinkActive = 4;

if ( ! session_id() ) {
   session_start();
}
$userInfo = $_SESSION['USER'];

if(!isset($userInfo) || $userInfo == "" ){
    header('location: /area-clienti.php');
    die();
}

global $search;

$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;

global $db;
$db = new Database();
$db->connect();

$sql = "SELECT * from RT_Coupon
		where MembershipClubCode = '".$userInfo['MembershipClubCode']."' 
		order by DataIns desc";
$row = $db->fetch_array($sql);

include_once($basepath . "/include/meta.php");
?>

<body class="main-bg" id="home-applicativo">

    <!-- Top Header -->
    <?php include_once($basepath."/include/top_header.php"); ?>
    
    <div class="main-container">
        <div class="content">
            <div style="margin-bottom:10px;" class="benvenuto-user">
                <?=$dizionario['prenota']['benvenuto']?> <b><?php 
                if(isset($userInfo)){
                    echo $userInfo['Nome'].' '.$userInfo['CognomeRagioneSociale'];
                } else {
                    echo $dizionario['prenota']['ospite'];
                } ?>
                </b>
                <?php
                if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1){
                    echo '<br>'.$dizionario['prenota']['prenotazione_gestita_da'].'<b>'.$_SESSION['gestore']['RagioneSociale']."</b>";
                }
                ?>
            </div>
            <div style="margin-bottom:10px;margin-top:0px;">
				<h3 style="margin-top:0px;"><b><?=$PageTitle?></b></h3>
			</div>
            <div class="ticket-list">
				<div class="filters">
				  <span><a href="/area-clienti.php"><?=$dizionario['area_clienti']['area']?></a> / <?=$dizionario['area_clienti']['coupon']?></span>
				</div>
                <div class="allTicket">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="coupon-container">
                                <!-- Pulsante acquista coupon sopra la tabella -->
                                <div class="coupon-actions-top" style="margin-bottom: 20px; text-align: right;">
                                    <a href="/acquista-coupon.php" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?=$dizionario['area_clienti']['acquista_coupon']?>
                                    </a>
                                </div>
                                
                                <div class="coupon-table-container">
                                    <div class="table-responsive">
                                        <table class="coupon-table">
                                            <thead>
                                                <tr>
                                                    <th><?=$dizionario['area_clienti']['descrizione']?></th>
                                                    <th><?=$dizionario['area_clienti']['codice']?></th>
                                                    <th><?=$dizionario['area_clienti']['credito']?></th>
                                                    <th><?=$dizionario['area_clienti']['data_scadenza']?></th>
                                                    <th><?=$dizionario['area_clienti']['stato']?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if(count($row)>0){
                                                    foreach ($row as $k=>$valore){
                                                        $dataScadenzaCalcolata = !empty($valore['ValidoA']) ? $valore['ValidoA'] : date('Y-m-d', strtotime($valore['DataIns'] . ' +1 year'));
														$dataScadenza = date('d/m/Y', strtotime($dataScadenzaCalcolata));
														$oggi = date('Y-m-d');
														$scaduto = ($dataScadenzaCalcolata < $oggi);
														$valoreVisualizzato = ($valore['Valore'] <= 0 || $valore['MaxUtilizzi'] == $valore['Utilizzi']) ? 0 : $valore['Valore'];
														
														// Logica per determinare lo stato del coupon
														if ($valore['DaVendere'] == 1 && ($valore['VenditaStato'] == 0 || $valore['VenditaStato'] == 1)) {
															$statoSpeciale = 'attesa_pagamento';
														} else {
															$utilizzato = ($valoreVisualizzato <= 0);
															$statoSpeciale = null;
														}
                                                        ?>
                                                        <tr class="coupon-row">
                                                            <td>
                                                                <div class="coupon-info">
                                                                    <strong><?=$valore['CouponNome']?></strong>
                                                                    <?php if($valore['Descrizione']): ?>
                                                                        <br><small><?=$valore['Descrizione']?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="coupon-code-container">
                                                                    <code class="coupon-code"><?=$valore['Codice']?></code>
                                                                    <button class="copy-btn" onclick="copyToClipboard('<?=$valore['Codice']?>')" title="Copia codice">
                                                                        <i class="fa fa-copy"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="coupon-value">€ <?=number_format($valoreVisualizzato, 2, ',', '.')?></span>
                                                            </td>
                                                            <td>
                                                                <span class="coupon-date <?=$scaduto ? 'expired' : ''?>">
                                                                    <?=$dataScadenza?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if($statoSpeciale == 'attesa_pagamento'): ?>
                                                                    <span class="coupon-status pending"><?=$dizionario['coupon']['attesa_pagamento']?></span>
                                                                <?php elseif($utilizzato): ?>
                                                                    <span class="coupon-status used"><?=$dizionario['area_clienti']['utilizzato']?></span>
                                                                <?php elseif($scaduto): ?>
                                                                    <span class="coupon-status expired"><?=$dizionario['area_clienti']['scaduto']?></span>
                                                                <?php else: ?>
                                                                    <span class="coupon-status active"><?=$dizionario['area_clienti']['attivo']?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5' class='no-coupon'>".$dizionario['area_clienti']['no_coupon']."</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Pulsante acquista coupon sotto la tabella -->
                                <div class="coupon-actions">
                                    <a href="/acquista-coupon.php" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?=$dizionario['area_clienti']['acquista_coupon']?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
							<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/profilo.php'"><i class="fa fa-user"></i> <?=$dizionario['area_clienti']['profilo']?></button>
							<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/mie-prenotazioni.php'"><i class="fa fa-ticket  "></i> <?=$dizionario['area_clienti']['prenotazioni']?></button>
							<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" onclick="window.location.href='/miei-coupon.php'"><i class="fa fa-ticket  "></i> <?=$dizionario['area_clienti']['coupon']?></button>
							<button type="button" class="btn btn-big btn-primary btn-space" style="width: 100%;" id="logout-profilo"><i class="fa fa-sign-out"></i> <?=$dizionario['area_clienti']['logout']?></button>
                            
                            <div class="sidebar-info">
                                <h4><?=$dizionario['area_clienti']['info_coupon']?></h4>
                                <ul>
                                    <li><?=$dizionario['area_clienti']['coupon_info_1']?></li>
                                    <li><?=$dizionario['area_clienti']['coupon_info_2']?></li>
                                    <li><?=$dizionario['area_clienti']['coupon_info_3']?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom -->
        <?php include_once($basepath."/include/bottom.php"); ?>
        
        <!-- Footer -->
        <?php include_once($basepath."/include/footer.php"); ?>    
        
    </div>
    
    <?php include_once($basepath."/include/html_close.php"); ?>   

</body>

<style>

	.allTicket .row {
		margin: 0;
	}
.coupon-container {
    background: #fff;
    border-radius: 8px;
    padding: 30px 0px;
    margin-bottom: 30px;
}

.coupon-table-container {
    margin-bottom: 30px;
}

.coupon-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    overflow: hidden;
}

.coupon-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 1px solid #dee2e6;
}

.coupon-table td {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.coupon-row:hover {
    background: #f8f9fa;
}

.coupon-info strong {
    color: #333;
    font-size: 16px;
}

.coupon-info small {
    color: #666;
    font-size: 13px;
}

.coupon-code {
    background: #e9ecef;
    padding: 5px 10px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 14px;
    color: #495057;
}

.coupon-code-container {
    display: flex;
    align-items: center;
    gap: 8px;
}

.copy-btn {
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 5px 8px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.copy-btn:hover {
    background: #0056b3;
    transform: scale(1.05);
}

.copy-btn:active {
    transform: scale(0.95);
}

.copy-btn.copied {
    background: #28a745;
}

.coupon-value {
    font-weight: 600;
    color: #28a745;
    font-size: 16px;
}

.coupon-date {
    color: #666;
}

.coupon-date.expired {
    color: #dc3545;
    font-weight: 600;
}

.coupon-status {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.coupon-status.active {
    background: #d4edda;
    color: #155724;
}

.coupon-status.used {
    background: #f8d7da;
    color: #721c24;
}

.coupon-status.expired {
    background: #f5c6cb;
    color: #721c24;
}

.coupon-status.pending {
    background: #fff3cd;
    color: #856404;
    white-space: nowrap;
    font-size: 10px;
    padding: 4px 8px;
    line-height: 1.2;
}

.no-coupon {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}

.coupon-actions {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.sidebar-menu {
    background: #fff;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.sidebar-menu h4 {
    color: #333;
    margin-bottom: 20px;
    font-size: 18px;
}

.menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-list li {
    margin-bottom: 10px;
}

.menu-list li.active a {
    background: #007bff;
    color: #fff;
}

.menu-list a {
    display: block;
    padding: 12px 15px;
    color: #333;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.menu-list a:hover {
    background: #f8f9fa;
    color: #007bff;
}

.menu-list i {
    margin-right: 10px;
    width: 20px;
}

.sidebar-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.sidebar-info h4 {
    color: #333;
    margin-bottom: 15px;
    font-size: 16px;
}

.sidebar-info ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-info li {
    padding: 8px 0;
    color: #666;
    font-size: 14px;
    border-bottom: 1px solid #dee2e6;
}

.sidebar-info li:last-child {
    border-bottom: none;
}

.sidebar-info li:before {
    content: "✓";
    color: #28a745;
    font-weight: bold;
    margin-right: 8px;
}

table.coupon-table thead tr:first-child th {
  padding: 15px;
}

@media (max-width: 768px) {
    .coupon-container {
        padding: 20px;
    }
    
    .coupon-table {
        font-size: 14px;
    }
    
    .coupon-table th,
    .coupon-table td {
        padding: 10px 8px;
    }
}
</style>

<script>
$(document).ready(function(){
    $('#logout').click(function(){
        var formData = {
            action: 'logout'
        };
        $.ajax({
            type: "POST",
            url: "/gestione_utente.php",
            data: formData,
            dataType: "json",
            success: function(data){
                window.location.replace("/");
            },
            failure: function(errMsg) {
                alert(errMsg);
            }
        });
    });
});

function copyToClipboard(text) {
    // Crea un elemento temporaneo
    var tempInput = document.createElement("input");
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
    
    // Trova il pulsante (potrebbe essere l'icona o il pulsante stesso)
    var button = event.target.closest('.copy-btn');
    
    // Verifica se non è già in stato "copied"
    if (!button.classList.contains('copied')) {
        // Feedback visivo
        button.classList.add('copied');
        var originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fa fa-check"></i>';
        
        setTimeout(function() {
            button.classList.remove('copied');
            button.innerHTML = originalIcon;
        }, 2000);
    }
    
    // Notifica (opzionale)
    alert('Codice copiato negli appunti!');
}
</script>

</html>

