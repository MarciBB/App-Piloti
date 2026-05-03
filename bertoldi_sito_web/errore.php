<?php
$PageTitle = "Transazione Negata";
$PageDescription = "";
$PageKeywords = "";

$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
global $dizionario;
$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
$db=new Database();
$conn=$db->connect();  
include_once($basepath."/include/meta.php");
$error=true;
$cliente_nome;
   
?>

</head>
<body class="main-bg" id="home-applicativo">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
	<div class="main-container">
    <div class="content">
        <div style="margin-bottom:20px; text-align: center;">
            <h2><b><?=$dizionario['errore']['titolo']?></b></h2>
        </div>
        <div class="ticket-list">
            <div class="allTicket">
                <div class="row">
                    <div class="col-md-12" style="text-align: center;">
                        <div style="margin: 30px 0;">
                            <i class="fa fa-exclamation-triangle" style="font-size: 80px; color: #f39c12; margin-bottom: 20px;"></i>
                        </div>
                        
                        <div style="margin: 30px 0;">
                            <h3 style="color: #e74c3c; margin-bottom: 20px;"><?=$dizionario['errore']['transazione_non_completata']?></h3>
                            <p style="font-size: 18px; line-height: 1.6; margin-bottom: 30px; color: #666;">
                                <?=$dizionario['errore']['messaggio_principale']?>
                            </p>
                        </div>
                        
                        <div style="margin: 40px 0;">
                            <h4 style="color: #2c3e50; margin-bottom: 15px;"><?=$dizionario['errore']['cosa_fare_titolo']?></h4>
                            <ul style="text-align: left; display: inline-block; font-size: 16px; color: #666;">
                                <li style="margin-bottom: 10px;"><?=$dizionario['errore']['suggerimento_1']?></li>
                                <li style="margin-bottom: 10px;"><?=$dizionario['errore']['suggerimento_2']?></li>
                                <li style="margin-bottom: 10px;"><?=$dizionario['errore']['suggerimento_3']?></li>
                                <li style="margin-bottom: 10px;"><?=$dizionario['errore']['suggerimento_4']?></li>
                            </ul>
                        </div>
                        
                        <div style="margin: 40px 0;">
                            <a href="/index.php<?= ($sessionId != '') ? '?session_id='.$sessionId : '' ?>" 
                               class="btn btn-primary btn-big" 
                               style="padding: 15px 30px; font-size: 18px; margin-right: 15px;">
                                <i class="fa fa-home"></i> <?=$dizionario['errore']['torna_home']?>
                            </a>
                            
                            <a href="mailto:info@bertoldiboats.com" 
                               class="btn btn-secondary btn-big" 
                               style="padding: 15px 30px; font-size: 18px;">
                                <i class="fa fa-envelope"></i> <?=$dizionario['errore']['contattaci']?>
                            </a>
                        </div>
                        
                        <div style="margin-top: 40px; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
                            <p style="margin: 0; font-size: 14px; color: #666;">
                                <i class="fa fa-info-circle"></i> 
                                <?=$dizionario['errore']['info_aggiuntiva']?>
                            </p>
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
    <!-- #page -->
  
   	<?php include_once($basepath."/include/html_close.php"); ?>   

</body>
</html>