<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$PageTitle = "Errore Captcha";
$PageDescription = "Errore Captcha";
$PageKeywords = "";

include_once($basepath . "/include/meta.php");

$LinkActive = "Errore Captcha";
$page_title = "Errore Captcha";

?>

</head>
<body class="main-bg" id="home-applicativo">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
	<div class="main-container">
		<div class="content">
			<div style="margin-bottom:10px;">
				<h3><b><?php echo $page_title;?></b></h3>
			</div>
			<div class="ticket-list">
			
				<div class="filters">
				  <span>Errore Captcha!</span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<p style="margin:15px !important;">
								Il captcha inserito &egrave; errato, <a href="#" onclick="javascript: history.back()">tornare indietro</a> e riprovare.
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
