<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$PageTitle = "Grazie per averci contattato";
$PageDescription = "La ringraziamo per averci contattato e Le confermiamo che la richiesta è stata inviata all'ufficio competente che La ricontatterà il prima possibile.";
$PageKeywords = "";

include_once($basepath . "/include/meta.php");

$LinkActive = $dizionario['Contatti'];
$page_title = $dizionario['Contatti'];

?>

</head>
<body class="main-bg" id="home-applicativo">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
	<div class="main-container">
		<div class="content">
			<div style="margin-bottom:10px;">
				<h3><b><?php echo $PageTitle;?></b></h3>
			</div>
			<div class="ticket-list">
			
				<div class="filters">
				  <span>Grazie per averci contattato!</span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<p style="margin:15px !important;">
								La ringraziamo per averci contattato e Le confermiamo che la richiesta è stata inviata all'ufficio competente che La ricontatterà il prima possibile.<br/><br/>RingraziandoLa per l'attenzione dedicataci, porgiamo i ns. più cordiali saluti.<br /><em>Staff Support<br />Bertoldi Boats</em>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
    
        
        <!-- Top Header
        ================================================== -->
        <? include_once($basepath."/include/top_header.php") ?>  




		<!-- Bottom
		================================================== -->
		<?php include_once($basepath."/include/bottom.php"); ?>
        	 
        
        <!-- Footer
        ================================================== -->
        <?php include_once($basepath."/include/footer.php"); ?>    
        
    </div>
    <!-- #page -->
  
   	<?php include_once($basepath."/include/html_close.php"); ?>   

</body>
</html>
				
