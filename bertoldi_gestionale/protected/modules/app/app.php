<script type="text/javascript"> 

// Registra il service worker per la PWA (Progressive Web App)
if('serviceWorker' in navigator) {
    navigator.serviceWorker.register('./service-worker.js')
      .then(function() {
            console.log('Service Worker Registered');
      });
}

// Funzione per ottenere le corse dell'autista in una data specifica
function getCorse(dataCorse){
	var dataString = dataCorse;
	var idAutista = <?php echo $autista['AutistiId'];?>;
	
	var formData = {
			action:"getCorse",
			data: dataString,
			idAutista: idAutista
	};
	// Chiamata AJAX per ottenere le corse
	$.post('<?php echo Config::$UrlMobile; ?>', formData, function(responce) {
		// inizializzazione eseguita
	}, "json")
	  .fail(function() {
	 	alert("L'applicazione non riesce a comunicare con il server");
	  });
}

// Funzione per inviare il ticket via email al cliente
function inviaTicketEmail(prenotazioneId){
	var idAutista = <?php echo $autista['AutistiId'];?>;
	var formData = {
			action: "inviaTicketEmail",
			prenotazioneId: prenotazioneId,
			idAutista: idAutista
	};
	// Chiamata AJAX per invio email
	$.ajax({
		  url: '<?php echo Config::$UrlMobile; ?>',
		  type: "POST",
		  data : formData,
		  dataType: 'json',
		  success: function(responce){
			  $('#modal-invio').modal("show");
		  },
		  error: function(xhr, ajaxOptions, thrownError) {
			alert("L'applicazione non riesce a comunicare con il server");
		  },
		});
}

// Funzione per validare un QRCode (biglietto)
function validaQRCode(responce){
	var corsaIdS = <?= isset($CorsaId)? $CorsaId : 0 ?>;
	var dataS = '<?= isset($DataPartenza)? $DataPartenza: ''?>';
	// controllo già validato
	var formData = {
		action:"findBus",
		codice: codiceQRCode,
		corsaId: corsaIdS,
		dataPartenza: dataS,
		autistaId: <?php echo $autista['AutistiId'];?>,
	};
	$.ajax({
  		  url: '<?php echo Config::$UrlMobile; ?>',
  		  type: "POST",
  		  data : formData,
  		  dataType: 'json',
  		  success: function(responce2){
  			  var busId = responce2.busId;
  			  if(busId != 0 ){
  				var corsaIdS = <?= isset($CorsaId)? $CorsaId : 0 ?>;
  				var dataS = '<?= isset($DataPartenza)? $DataPartenza: ''?>';
  				var busIdS = busId;
  				// controllo già validato
  				  var formData = {
  							action:"existQrcode",
  							codice: codiceQRCode,
  							corsaId: corsaIdS,
  							dataPartenza: dataS,
  							busId: busIdS,
  							autistaId: <?php echo $autista['AutistiId'];?>,
  					};
  					$.ajax({
  			  		  url: '<?php echo Config::$UrlMobile; ?>',
  			  		  type: "POST",
  			  		  data : formData,
  			  		  dataType: 'json',
  			  		  success: function(responce2){
  			  			  if(responce2.result == 0 || responce2.result == '0'){
  			  				alert("Biglietto convalidato");
  			  			  } else {
  			  				alert("Biglietto gia' validato");  
  			  			  }
  			  		  },
  			  		  error: function(){
  				  		alert("L'applicazione non riesce a comunicare con il server");
  			  		  }
  					});
  			  } else {
  				alert("Biglietto non valido");  
  			  }
  		  },
  		  error: function(){
	  		alert("L'applicazione non riesce a comunicare con il server");
  		  }
		});
}

// --- BLOCCO VUE/INSTASCAN PER SCANSIONE QRCODE ---
// Inizializza Vue e Instascan per la scansione dei QRCode tramite webcam
var app = new Vue({
  el: '#app',
  data: {
    scanner: null,         // Oggetto Instascan.Scanner
    activeCameraId: null,  // ID della camera attiva
    cameras: [],           // Lista delle camere disponibili
    scans: []              // Lista delle scansioni effettuate
  },
  mounted: function () {

  },
  methods: {
    formatName: function (name) {
      return name || '(unknown)';
    },
    selectCamera: function (camera) {
      this.activeCameraId = camera.id;
      this.scanner.start(camera);
    },
    start: function (camera) {
    	
        if (this.cameras.length > 0) {
          
          var act=0;
          if (this.cameras.length > 1)
              act=1;
            
          this.activeCameraId = this.cameras[act].id;
          this.scanner.start(this.cameras[act]);
          
        } else {
          alert('Nessuna camera trovata.');
        }
      },
    stop: function (camera) {
    	
        if (this.cameras.length > 0) {
          
          var act=0;
          if (this.cameras.length > 1)
              act=1;
            
          this.scanner.stop(this.cameras[act]);
          
        } else {
        	alert('Nessuna camera trovata.');
        }
      },
	initVideo: function () {
		var self = this;
		// Inizializza lo scanner Instascan con il video dell'elemento #preview
		self.scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5 });
		// Listener per la scansione di un QRCode
		self.scanner.addListener('scan', function (content, image) {
			var urlQRcode = content;
			var formData = {
				action: "validate",
				app: 1,
			};
			// Forza https se necessario
			if (!urlQRcode.includes("https")) {
				urlQRcode = urlQRcode.replace("http", "https");
			}
			// Chiamata AJAX per validare il QRCode scansionato
			$.ajax({
				url: urlQRcode,
				type: "GET",
				data: formData,
				dataType: 'json',
				success: function (responce) {
					console.log(responce);
					// Se il biglietto è valido, reindirizza alla pagina di dettaglio
					if (responce.risultato['A'] != 0) {
						app.stop();
						var codiceQRCode = responce.codice;
						var prenotazioneIdInfoResponce = responce.infoBiglietto['PrenotazioneId'];
						var corsaIdInfoResponce = responce.infoBiglietto['CorsaId'];
						var dataPartenzaInfoResponce = responce.infoBiglietto['DataPartenza'];
						var corsaIdRitornoInfoResponce = responce.infoBiglietto['CorsaIdRitorno'];
						var dataPartenzaRitornoInfoResponce = responce.infoBiglietto['DataPartenzaRitorno'];
						window.location.href = "show-ticket.php?PrenotazioneId=" + prenotazioneIdInfoResponce + "&CorsaId=" + corsaIdInfoResponce + "&DataPartenza=" + dataPartenzaInfoResponce + "&CorsaIdRitorno=" + corsaIdRitornoInfoResponce + "&DataPartenzaRitorno=" + dataPartenzaRitornoInfoResponce + "&A=" + responce.risultato['A'] + "&R=" + responce.risultato['R'];
					} else {
						alert("Biglietto non valido. Il codice non e' stato riconosciuto");
					}
				},
				error: function (xhr, ajaxOptions, thrownError) {
					alert("Lettura non eseguita correttamente");
				},
			});
		});
		// Recupera le camere disponibili e avvia la prima trovata
		Instascan.Camera.getCameras().then(function (cameras) {
    self.cameras = cameras;
	if (cameras.length > 0) {
		console.log('Numero camere trovate: ' + cameras.length);
		var logMsg = 'Numero camere trovate: ' + cameras.length + '\n';
		// Cerca la camera posteriore tra le disponibili
		var backCam = null;
		for (var i = 0; i < cameras.length; i++) {
			var label = (cameras[i].name || cameras[i].label || '').toLowerCase();
			console.log('Camera ' + i + ': ' + label);
			logMsg += 'Camera ' + i + ': ' + label + '\n';
			if (label.indexOf('back') !== -1 || label.indexOf('rear') !== -1) {
				backCam = cameras[i];
				console.log('Camera posteriore trovata: ' + label);
				logMsg += 'Camera posteriore trovata: ' + label + '\n';
				break;
			}
		}
		var camToUse = backCam || cameras[0];
		console.log('Camera selezionata: ' + ((camToUse.name || camToUse.label) ? (camToUse.name || camToUse.label) : camToUse.id));
		logMsg += 'Camera selezionata: ' + ((camToUse.name || camToUse.label) ? (camToUse.name || camToUse.label) : camToUse.id) + '\n';
		self.activeCameraId = camToUse.id;
		self.scanner.start(camToUse);
	// (alert di debug rimosso)
    } else {
        alert('Nessuna camera trovata.');
    }
}).catch(function (e) {
    console.error(e);
});
	}
	  
  }
});
</script>
