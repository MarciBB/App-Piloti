<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$PageTitle = "Credits - Tour motoscafi a Sirmione e sul lago di Garda | Bertoldi Boats";
$PageDescription = "Progettazione e realizzazione sito web Bertoldi Boats a cura di Brain Computing S.p.A. - Sviluppo software e consulenza informatica.";
$PageKeywords = "credits Bertoldi Boats, realizzazione Bertoldi Boats, progettazione Bertoldi Boats, sviluppo sito web Bertoldi Boats";

$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/include/meta.php");
$page_title = "Credits";
?>

</head>
<body class="main-bg" id="home-applicativo">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
		
		
	<div class="main-container">
		<div class="content">
			<div style="margin-bottom:10px;">
				<h3><b><?= $PageTitle ?></b></h3>
			</div>
			<div class="ticket-list">
			
				<div class="filters">
				  <span><?= $page_title ?></b></span>
				</div>
				<div class="allTicket">
					<div class="row">
						<div class="col-md-12">
							<p style="margin:15px !important;" class="big_text">
								<div class="general_content">
                                    <h2>Copyright 2024 <?php if(date('Y')!="2013"){echo " - ".date('Y');}?></h3>

									<p>
									Il presente Sito internet è di esclusiva proprietà di Bertoldi Boats<br/>
									Tutti i contenuti in esso riportati sono da intendersi a scopo puramente informativo.
									</p>
									
									
									<h3>Webdevelopment & Webmanagement</h3>
									<p><img style="width:200px; height:auto;" src="https://www.braincomputing.com/wp-content/uploads/2022/10/BC-LOGO_color.svg" alt="Braincomputing S.p.A." /></p>
									
									<p>
									Brain Computing S.p.A.<br/>
									Via Gian Giacomo Porro, 8<br/>
									00197 Roma<br/>
									<a href="http://www.braincomputing.com" target="_blank"><strong>www.braincomputing.com</strong></a>
									</p>
                                </div>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
    
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
