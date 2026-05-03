<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$PageTitle = "Pagina non trovata";
$PageDescription = "";
$PageKeywords = "";

$LinkActive = 1;
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/include/meta.php");

$page_title = $PageTitle; 
?>

</head>

<body class="main-bg" id="home-applicativo">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
	<div class="main-container">
		<div class="content">
			<div style="margin-bottom:10px;">
				<h3><b><?=$page_title?></b></h3>
			</div>
			<div class="ticket-list">
			
				<div class="filters">
				  <span>Pagina non trovata!</span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<p style="margin:15px !important;" class="big_text">
								404. Pagina non trovata!<br> Torna alla <a href="/<?= $_SESSION['code_gestore'] ?>">Home Page!</a>
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