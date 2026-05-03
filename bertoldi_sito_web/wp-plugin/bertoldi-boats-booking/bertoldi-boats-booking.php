<?php
/*
Plugin Name: Bertoldi Boats Booking
Description: Plugin to include the booking of boat tours with Bertoldi Boats in your WordPress site
Version: 2.0
Author: Brain Computing S.p.A.
*/

// Function to generate a random alphanumeric string of given length
function generate_random_session_id($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Register the shortcode
function bertoldi_boats_iframe_shortcode($atts) {
    $atts = shortcode_atts(array(
        'url' => 'https://booking.bertoldiboats.com/',
        'width' => '100%', // Default width
        'height' => '400px', // Default height
        'lang' => '',
    ), $atts, 'bertoldi_boats_iframe');

    $url = esc_url($atts['url']);
    $width = esc_attr($atts['width']);
    $height = esc_attr($atts['height']);
    $lang = esc_attr($atts['lang']);
    
    if (empty($url)) {
        return '<p>Please provide a valid URL.</p>';
    }

    // Generate a random session ID
    $session_id = generate_random_session_id();
    
    // Build query parameters
    $params = array('session_id' => $session_id);
    if (!empty($lang)) {
        $params['lang'] = $lang;
    }
    $query_string = http_build_query($params);

    // Append parameters to URL
    $url_with_params = $url . (strpos($url, '?') === false ? '?' : '&') . $query_string;

    ob_start();
    ?>
    <div class="bertoldi-boats-container" style="width: <?php echo $width; ?>; height: <?php echo $height; ?>;">
        <iframe src="<?php echo $url_with_params; ?>" class="bertoldi-boats-iframe" style="width: 100%; height: 100%; border: none;"></iframe>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('bertoldi_boats', 'bertoldi_boats_iframe_shortcode');

// NUOVO SHORTCODE: [booking-bertoldiboats tour="Sirmione Tour da Desenzano" label="Prenota ora"]
function booking_bertoldiboats_shortcode($atts) {
    $atts = shortcode_atts(array(
        'tour' => '',
        'label' => 'Prenota subito il tour',
        'lang' => 'it',
        'porti' => ''
    ), $atts, 'booking-bertoldiboats');

    if (empty($atts['tour'])) {
        return '<p>Devi specificare il parametro <strong>tour</strong> nello shortcode.</p>';
    }

    $lang = in_array($atts['lang'], ['it','en','de','fr','es']) ? $atts['lang'] : 'it';

    // Label di default per ogni lingua
    $default_labels = [
        'it' => 'PRENOTA SUBITO IL TOUR',
        'en' => 'BOOK THIS TOUR NOW',
        'de' => 'TOUR JETZT BUCHEN',
        'fr' => 'RÉSERVEZ CE TOUR',
        'es' => 'RESERVA ESTE TOUR AHORA'
    ];
    $button_label = !empty($atts['lang']) ? $default_labels[$lang] : esc_html($atts['label']);

    $tour_name = esc_js($atts['tour']);
    $porti = esc_js($atts['porti']);
    $token = 'R7tPwX2kD4yHsG9mN6jQ1eL5zU8oVcB0'; // Inserisci qui il token valido

    $uniq = md5($tour_name . $lang);
    $modal_id = 'bertoldi-modal-' . $uniq;
    $calendar_id = 'bertoldi-calendar-' . $uniq;
    $year_id = 'bertoldi-calendar-year-' . $uniq;
    $month_id = 'bertoldi-calendar-month-' . $uniq;

    ob_start();
    ?>
    <button class="bertoldi-booking-btn" type="button" onclick="openBertoldiModal_<?php echo $uniq; ?>()"> <?php echo $button_label; ?> </button>
    <div id="<?php echo $modal_id; ?>" class="bertoldi-modal" style="display:none;">
        <div class="bertoldi-modal-content">
            <span class="bertoldi-modal-close" onclick="closeBertoldiModal_<?php echo $uniq; ?>()">&times;</span>
            <h2 id="bertoldi-modal-title-<?php echo $uniq; ?>">Disponibilità per: <?php echo esc_html($atts['tour']); ?></h2>
            <div id="bertoldi-calendar-filters-<?php echo $uniq; ?>" class="bertoldi-calendar-filters">
                <select id="<?php echo $year_id; ?>"></select>
                <select id="<?php echo $month_id; ?>"></select>
            </div>
            <div class="bertoldi-calendar-header">
                <button type="button" class="bertoldi-calendar-nav" id="bertoldi-prev-month-<?php echo $uniq; ?>">&lt;</button>
                <span id="bertoldi-calendar-month-label-<?php echo $uniq; ?>"></span>
                <button type="button" class="bertoldi-calendar-nav" id="bertoldi-next-month-<?php echo $uniq; ?>">&gt;</button>
            </div>
            <div id="<?php echo $calendar_id; ?>" class="bertoldi-calendar-grid"></div>
            <!-- Aggiungi questo div subito dopo il div del calendario nel modal -->
            <div id="bertoldi-iframe-container-<?php echo $uniq; ?>" style="display:none; margin-top:20px;">
                <iframe id="bertoldi-iframe-<?php echo $uniq; ?>" src="" style="width:100%;height:500px;border:none;border-radius:12px;box-shadow:0 2px 8px rgba(33,150,243,0.08);"></iframe>
            </div>
        </div>
    </div>
    <style>
        .bertoldi-modal {
    position: fixed;
    z-index: 999999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    overflow: auto;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
}
.bertoldi-modal-content {
    background: #00335e !important;
    color: white !important;
    font-family: "Open Sans", sans-serif !important;
    margin: 0 auto;
    padding: 32px 28px 24px 28px;
    border-radius: 18px;
    width: 95%;
    max-width: 480px;
    position: relative;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    top: 0;
    left: 0;
    transform: translateY(0);
}
.bertoldi-modal-close {
    position: absolute;
    right: 24px; top: 24px;
    font-size: 28px;
    cursor: pointer;
    color: #fff;
    transition: color 0.2s;
}
.bertoldi-modal-close:hover {
    color: #1565c0;
}
.bertoldi-booking-btn {
    color: #fff !important;
    background-color: #00335e !important;
    border: 2px solid #fff !important;
    border-radius: 24px;
    padding: 12px 32px;
    font-size: 18px;
    font-family: 'Montserrat', Arial, sans-serif;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(33,150,243,0.08);
    transition: background 0.2s, color 0.2s, border 0.2s, box-shadow 0.2s;
}
.bertoldi-booking-btn:hover {
    background-color: #fff !important;
    border-color: #00335e !important;
    color: #00335e !important;
}
.bertoldi-modal-content h2 {
    font-size: 22px;
    font-weight: 700;
    color: white !important;
    margin-bottom: 18px;
    letter-spacing: 0.5px;
    font-family: "Open Sans", sans-serif !important;
    background-color: #00335e !important;
    padding: 8px 0;
    border-radius: 8px;
}
.bertoldi-calendar-filters {
    margin-bottom: 12px;
    display: flex;
    gap: 12px;
    justify-content: center;
}
.bertoldi-calendar-filters select {
    padding: 7px 14px;
    font-size: 16px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    background: #f7f7f7;
    color: #333;
    font-family: 'Montserrat', Arial, sans-serif;
    transition: border 0.2s;
}
.bertoldi-calendar-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    margin-bottom: 10px;
}
.bertoldi-calendar-nav {
    background: #e3f2fd;
    border: none;
    border-radius: 50%;
    width: 36px; height: 36px;
    font-size: 20px;
    color: #2196f3;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}
.bertoldi-calendar-nav:hover {
    background: #2196f3;
    color: #fff;
}
.bertoldi-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-bottom: 10px;
}
.bertoldi-calendar-day {
    padding: 14px 0;
    text-align: center;
    border-radius: 8px;
    background: #f5f5f5;
    color: #333;
    font-size: 16px;
    min-width: 38px;
    min-height: 38px;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    box-shadow: 0 1px 2px rgba(33,150,243,0.04);
}
.bertoldi-calendar-day.header {
    background: #2196f3;
    color: #fff;
    font-weight: 700;
    font-size: 15px;
    letter-spacing: 0.5px;
    box-shadow: none;
}
.bertoldi-calendar-day.available {
    background: #2196f3;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(33,150,243,0.08);
}
.bertoldi-calendar-day.available:hover {
    background: #1565c0;
}
.bertoldi-calendar-day.today {
    border: 2px solid #1565c0;
}
.bertoldi-calendar-day.unavailable {
    background: #e0e0e0;
    color: #aaa;
    cursor: not-allowed;
    box-shadow: none;
}
    </style>
    <script>
    var portiFromShortcode_<?php echo $uniq; ?> = "<?php echo $porti; ?>";
    console.log('Porti dallo shortcode:', portiFromShortcode_<?php echo $uniq; ?>);
    // Dizionari per localizzazione
    var bertoldiLangs_<?php echo $uniq; ?> = {
        it: {
            months: ["Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre","Novembre","Dicembre"],
            days: ["Dom","Lun","Mar","Mer","Gio","Ven","Sab"],
            continue: "Continua",
            available: "Disponibile",
            unavailable: "Non disponibile"
        },
        en: {
            months: ["January","February","March","April","May","June","July","August","September","October","November","December"],
            days: ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],
            continue: "Continue",
            available: "Available",
            unavailable: "Unavailable"
        },
        de: {
            months: ["Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember"],
            days: ["So","Mo","Di","Mi","Do","Fr","Sa"],
            continue: "Weiter",
            available: "Verfügbar",
            unavailable: "Nicht verfügbar"
        },
        fr: {
            months: ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"],
            days: ["Dim","Lun","Mar","Mer","Jeu","Ven","Sam"],
            continue: "Continuer",
            available: "Disponible",
            unavailable: "Indisponible"
        },
        es: {
            months: ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
            days: ["Dom","Lun","Mar","Mié","Jue","Vie","Sáb"],
            continue: "Continuar",
            available: "Disponible",
            unavailable: "No disponibile"
        }
    };

    window.openBertoldiModal_<?php echo $uniq; ?> = function() {
        document.getElementById('<?php echo $modal_id; ?>').style.display = 'block';
        loadBertoldiCalendar_<?php echo $uniq; ?>();
    }
    window.closeBertoldiModal_<?php echo $uniq; ?> = function() {
        document.getElementById('<?php echo $modal_id; ?>').style.display = 'none';
        // Reset: mostra calendario, header e filtri
        document.getElementById('<?php echo $calendar_id; ?>').style.display = '';
        document.getElementById('bertoldi-calendar-filters-<?php echo $uniq; ?>').style.display = '';
        document.querySelector('.bertoldi-calendar-header').style.display = '';
        document.getElementById('bertoldi-iframe-container-<?php echo $uniq; ?>').style.display = 'none';
        document.getElementById('bertoldi-iframe-<?php echo $uniq; ?>').src = '';
    }

    function loadBertoldiCalendar_<?php echo $uniq; ?>() {
        const token = '<?php echo $token; ?>';
        const tourName = '<?php echo $tour_name; ?>';
        const calendarDiv = document.getElementById('<?php echo $calendar_id; ?>');
        const yearSelect = document.getElementById('<?php echo $year_id; ?>');
        const monthSelect = document.getElementById('<?php echo $month_id; ?>');
        const modalTitle = document.getElementById('bertoldi-modal-title-<?php echo $uniq; ?>');
        const lang = '<?php echo $lang; ?>';
        const dict = bertoldiLangs_<?php echo $uniq; ?>[lang];
        calendarDiv.innerHTML = 'Caricamento...';

        // Recupera LineaId tramite get-linea-by-name
        fetch('https://booking.bertoldiboats.com/api/api.php?handler=get-linea-by-name', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'token=' + encodeURIComponent(token) + '&LineaNome=' + encodeURIComponent(tourName)
        })
        .then(response => response.json())
        .then(data => {
            let lineaId = null;
            let lineaNome = tourName;
            if (data.data && data.data.LineaId) {
                lineaId = data.data.LineaId;
                if (data.data.LineaNome) {
                    lineaNome = data.data.LineaNome;
                }
            }
            // Aggiorna il titolo del modal con il nome della linea reale
            modalTitle.textContent = lineaNome;

            // Popola i filtri anno/mese
            let today = new Date();
            let currentYear = today.getFullYear();
            let currentMonth = today.getMonth();

            yearSelect.innerHTML = '';
            for (let y = currentYear - 2; y <= currentYear + 2; y++) {
                const opt = document.createElement('option');
                opt.value = y;
                opt.textContent = y;
                yearSelect.appendChild(opt);
            }
            yearSelect.value = currentYear;

            monthSelect.innerHTML = '';
            for (let m = 0; m < 12; m++) {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = dict.months[m];
                monthSelect.appendChild(opt);
            }
            monthSelect.value = currentMonth;

            // Funzione per calcolare dataInizio/dataFine
            function getMonthRange(year, month) {
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                function formatDateYMD(date) {
                    return date.getFullYear() + '-' +
                        ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                        ('0' + date.getDate()).slice(-2);
                }
                const dataInizio = formatDateYMD(firstDay);
                const dataFine = formatDateYMD(lastDay);
                console.log('Range:', dataInizio, 'to', dataFine);
                return { dataInizio, dataFine };
            }

            // Funzione per caricare le date disponibili
            function fetchAvailableDates() {
                if (!lineaId) {
                    renderCalendar([], lineaId);
                    return;
                }
                const selectedYear = parseInt(yearSelect.value);
                const selectedMonth = parseInt(monthSelect.value);
                const range = getMonthRange(selectedYear, selectedMonth);

                fetch('https://booking.bertoldiboats.com/api/api.php?handler=get-date-corsa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'token=' + encodeURIComponent(token) +
                          '&LineaId=' + lineaId +
                          '&dataInizio=' + range.dataInizio +
                          '&dataFine=' + range.dataFine
                })
                .then(response => response.json())
                .then(data2 => {
                    let availableDates = [];
                    if (data2.data && data2.data.length > 0) {
                        availableDates = data2.data.map(d => d.AppCalendarioData);
                    }
                    renderCalendar(availableDates, lineaId);
                })
                .catch(() => {
                    renderCalendar([], lineaId);
                });
            }

            // Funzione per renderizzare il calendario
            function renderCalendar(availableDates = [], lineaId = null) {
                const selectedYear = parseInt(yearSelect.value);
                const selectedMonth = parseInt(monthSelect.value);

                const firstDay = new Date(selectedYear, selectedMonth, 1);
                const lastDay = new Date(selectedYear, selectedMonth + 1, 0);
                const today = new Date();

                document.getElementById('bertoldi-calendar-month-label-<?php echo $uniq; ?>').textContent =
                    dict.months[selectedMonth] + ' ' + selectedYear;

                let html = '';
                dict.days.forEach(d => {
                    html += '<div class="bertoldi-calendar-day header">' + d + '</div>';
                });

                let startDay = firstDay.getDay();
                for (let i = 0; i < startDay; i++) {
                    html += '<div class="bertoldi-calendar-day unavailable"></div>';
                }

                for (let d = 1; d <= lastDay.getDate(); d++) {
                    const dateStr = selectedYear + '-' +
                        ('0' + (selectedMonth + 1)).slice(-2) + '-' +
                        ('0' + d).slice(-2);
                    const isAvailable = availableDates.includes(dateStr);
                    const thisDay = new Date(selectedYear, selectedMonth, d);
                    const isFutureOrToday = thisDay >= new Date(today.getFullYear(), today.getMonth(), today.getDate());
                    const isToday = (today.getFullYear() === selectedYear && today.getMonth() === selectedMonth && today.getDate() === d);

                    if (isAvailable && isFutureOrToday) {
                        html += '<button type="button" class="bertoldi-calendar-day available' + (isToday ? ' today' : '') + '" ' +
                            'data-date="' + dateStr + '">' + d + ' <span style="font-size:10px;">&#9679;</span></button>';
                    } else {
                        html += '<div class="bertoldi-calendar-day unavailable' + (isToday ? ' today' : '') + '">' + d + '</div>';
                    }
                }

                calendarDiv.innerHTML = html;

                // Event listener per click sulle date disponibili
                calendarDiv.querySelectorAll('button.bertoldi-calendar-day.available').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.getElementById('<?php echo $calendar_id; ?>').style.display = 'none';
                        document.getElementById('bertoldi-calendar-filters-<?php echo $uniq; ?>').style.display = 'none';
                        document.querySelector('.bertoldi-calendar-header').style.display = 'none';
                        document.getElementById('bertoldi-iframe-container-<?php echo $uniq; ?>').style.display = 'block';

                        const selectedDate = this.getAttribute('data-date');
                        const lang = '<?php echo $lang; ?>';
                        // Prendi il valore porti dallo shortcode PHP passato a JS
                        const porti = portiFromShortcode_<?php echo $uniq; ?>;

                        const iframeUrl = 'https://booking.bertoldiboats.com/prenota/booking.php?' +
                            'lineaId=' + encodeURIComponent(lineaId) +
                            '&data=' + encodeURIComponent(selectedDate) +
                            (porti ? '&porti=' + encodeURIComponent(porti) : '') +
                            '&lang=' + encodeURIComponent(lang);

                        document.getElementById('bertoldi-iframe-<?php echo $uniq; ?>').src = iframeUrl;
                    });
                });
            }

            // Navigazione mese avanti/indietro
            document.getElementById('bertoldi-prev-month-<?php echo $uniq; ?>').onclick = function() {
                let m = parseInt(monthSelect.value);
                let y = parseInt(yearSelect.value);
                if (m === 0) {
                    m = 11;
                    y--;
                } else {
                    m--;
                }
                monthSelect.value = m;
                yearSelect.value = y;
                fetchAvailableDates();
            };
            document.getElementById('bertoldi-next-month-<?php echo $uniq; ?>').onclick = function() {
                let m = parseInt(monthSelect.value);
                let y = parseInt(yearSelect.value);
                if (m === 11) {
                    m = 0;
                    y++;
                } else {
                    m++;
                }
                monthSelect.value = m;
                yearSelect.value = y;
                fetchAvailableDates();
            };

            // Aggiorna calendario alla selezione di anno/mese
            yearSelect.addEventListener('change', fetchAvailableDates);
            monthSelect.addEventListener('change', fetchAvailableDates);

            // Carica il calendario iniziale (mese corrente)
            fetchAvailableDates();
        });
    }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('booking-bertoldiboats', 'booking_bertoldiboats_shortcode');

// MODIFICA DELLA PAGINA DI IMPOSTAZIONI: COSTRUTTORE SHORTCODE
function bertoldi_boats_booking_settings_page() {
    ?>
    <div class="wrap">
        <h1>Bertoldi Boats Booking - Impostazioni</h1>
        <p>
            <h2>Shortcode classico:</h2>
            <code>[bertoldi_boats url="https://booking.bertoldiboats.com/" width="100%" height="400px" lang="it"]</code>
        </p>
        <ul>
            <li>
                <strong>url</strong> (opzionale):<br>
                <em>URL del sistema di prenotazione.</em><br>
                <strong>Valore di default:</strong> <code>https://booking.bertoldiboats.com/</code>
            </li>
            <li>
                <strong>width</strong> (opzionale):<br>
                <em>Larghezza dell’iframe.</em><br>
                <strong>Valori possibili:</strong> qualsiasi valore CSS valido (es. <code>100%</code>, <code>800px</code>)<br>
                <strong>Valore di default:</strong> <code>100%</code>
            </li>
            <li>
                <strong>height</strong> (opzionale):<br>
                <em>Altezza dell’iframe.</em><br>
                <strong>Valori possibili:</strong> qualsiasi valore CSS valido (es. <code>400px</code>, <code>600px</code>)<br>
                <strong>Valore di default:</strong> <code>400px</code>
            </li>
            <li>
                <strong>lang</strong> (opzionale):<br>
                <em>Lingua del sistema di prenotazione.</em><br>
                <strong>Valori possibili:</strong> <code>it</code> (italiano), <code>en</code> (inglese), <code>de</code> (tedesco), <code>fr</code> (francese), <code>es</code> (spagnolo)<br>
                <strong>Valore di default:</strong> <code>it</code>
            </li>
        </ul>
        <hr>
        <h2>Costruttore shortcode tour</h2>
        <p>
            Seleziona una linea per generare lo shortcode personalizzato:
        </p>
        <div id="bertoldi-shortcode-builder">
            <select id="bertoldi-linea-select">
                <option value="">Caricamento linee...</option>
            </select>
            <select id="bertoldi-porti-select" style="display:none;">
                <option value="">Seleziona i porti...</option>
            </select>
            <select id="bertoldi-lang-select">
                <option value="it">Italiano</option>
                <option value="en">English</option>
                <option value="de">Deutsch</option>
                <option value="fr">Français</option>
                <option value="es">Español</option>
            </select>
            <input type="text" id="bertoldi-label-input" placeholder="Testo pulsante (opzionale)" style="margin-left:10px;">
            <br><br>
            <label for="bertoldi-shortcode-output"><strong>Shortcode generato:</strong></label>
            <input type="text" id="bertoldi-shortcode-output" style="width: 400px;" readonly>
            <button id="bertoldi-copy-btn" type="button" style="margin-left:10px;">Copia</button>
            <span id="bertoldi-copy-msg" style="margin-left:10px;color:green;display:none;">Copiato!</span>
        </div>
<style>
#bertoldi-shortcode-builder {
    background: #f7fafd;
    border: 1px solid #e3eaf3;
    border-radius: 10px;
    padding: 18px 22px 18px 22px;
    margin-bottom: 24px;
    max-width: 700px;
}
#bertoldi-shortcode-builder select,
#bertoldi-shortcode-builder input[type="text"] {
    margin: 0 8px 8px 0;
    padding: 7px 12px;
    border-radius: 6px;
    border: 1px solid #cfd8dc;
    font-size: 16px;
}
#bertoldi-shortcode-builder button {
    background: #00335e;
    color: #fff;
    border: 2px solid #00335e;
    border-radius: 6px;
    padding: 7px 18px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}
#bertoldi-shortcode-builder button:disabled {
    background: #b0bec5;
    border-color: #b0bec5;
    color: #fff;
    cursor: not-allowed;
}
#bertoldi-shortcode-builder button:hover:not(:disabled) {
    background: #fff;
    color: #00335e;
}
#bertoldi-shortcode-output {
    font-family: monospace;
    background: #fff;
    border: 1px solid #b0bec5;
    border-radius: 6px;
    padding: 7px 10px;
    font-size: 16px;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiUrl = 'https://booking.bertoldiboats.com/api/api.php?handler=get-linea';
    const select = document.getElementById('bertoldi-linea-select');
    const portiSelect = document.getElementById('bertoldi-porti-select');
    const langSelect = document.getElementById('bertoldi-lang-select');
    const output = document.getElementById('bertoldi-shortcode-output');
    const copyBtn = document.getElementById('bertoldi-copy-btn');
    const copyMsg = document.getElementById('bertoldi-copy-msg');
    const token = 'R7tPwX2kD4yHsG9mN6jQ1eL5zU8oVcB0'; // Inserisci qui il token valido

    // Recupera le linee via API (POST con token)
    fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'token=' + encodeURIComponent(token)
    })
    .then(response => response.json())
    .then(data => {
        select.innerHTML = '';
        if (data.data && data.data.length > 0) {
            select.innerHTML = '<option value="">Seleziona una linea...</option>';
            data.data.forEach(function(linea) {
                const opt = document.createElement('option');
                opt.value = linea.LineaId;
                opt.textContent = linea.LineaNome;
                opt.setAttribute('data-nome', linea.LineaNome);
                select.appendChild(opt);
            });
        } else {
            select.innerHTML = '<option value="">Nessuna linea trovata</option>';
        }
    })
    .catch(() => {
        select.innerHTML = '<option value="">Errore nel caricamento</option>';
    });

    // Quando selezioni una linea, carica i porti
    select.addEventListener('change', function() {
        portiSelect.style.display = 'none';
        portiSelect.innerHTML = '<option value="">Caricamento porti...</option>';
        updateShortcode();
        const lineaId = select.value;
        if (lineaId) {
            fetch('https://booking.bertoldiboats.com/api/api.php?handler=get-porti', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'token=' + encodeURIComponent(token) + '&linea_id=' + encodeURIComponent(lineaId)
            })
            .then(response => response.json())
            .then(data => {
                portiSelect.innerHTML = '';
                if (data.data && Object.keys(data.data).length > 0) {
                    portiSelect.style.display = '';
                    portiSelect.innerHTML = '<option value="">Seleziona i porti...</option>';
                    Object.entries(data.data).forEach(function([key, value]) {
                        let partenza = value.partenza || '';
                        let destinazione = value.destinazione || '';
                        partenza = partenza.replace(/\(partenza\)/i, '').replace(/\(destinazione\)/i, '').trim();
                        destinazione = destinazione.replace(/\(partenza\)/i, '').replace(/\(destinazione\)/i, '').trim();
                        const label = 'Partenza: ' + partenza + ' - Arrivo: ' + destinazione;
                        const opt = document.createElement('option');
                        opt.value = key;
                        opt.textContent = label;
                        portiSelect.appendChild(opt);
                    });
                } else {
                    portiSelect.innerHTML = '<option value="">Nessun porto disponibile</option>';
                }
            })
            .catch(() => {
                portiSelect.innerHTML = '<option value="">Errore nel caricamento</option>';
            });
        }
    });

    // Aggiorna lo shortcode solo se linea e porto sono selezionati
    const labelInput = document.getElementById('bertoldi-label-input');
    function updateShortcode() {
        const selectedLineaOption = select.options[select.selectedIndex];
        const tourName = selectedLineaOption ? selectedLineaOption.getAttribute('data-nome') : '';
        const portiValue = portiSelect.value;
        const labelValue = labelInput.value.trim();
        if (tourName && portiValue) {
            let shortcode = '[booking-bertoldiboats tour="' + tourName + '" porti="' + portiValue + '" lang="' + langSelect.value + '"';
            if (labelValue) {
                shortcode += ' label="' + labelValue.replace(/"/g, '&quot;') + '"';
            }
            shortcode += ']';
            output.value = shortcode;
            copyBtn.disabled = false;
        } else {
            output.value = '';
            copyBtn.disabled = true;
        }
        copyMsg.style.display = 'none';
    }
    labelInput.addEventListener('input', updateShortcode);
    portiSelect.addEventListener('change', updateShortcode);
    langSelect.addEventListener('change', updateShortcode);
    select.addEventListener('change', updateShortcode);

    // Pulsante copia
    copyBtn.disabled = true;
    copyBtn.addEventListener('click', function() {
        output.select();
        output.setSelectionRange(0, 99999);
        document.execCommand('copy');
        copyMsg.style.display = 'inline';
        setTimeout(function() { copyMsg.style.display = 'none'; }, 1500);
    });
});
</script>
        <p style="margin-top:30px;">
            Copia e incolla lo shortcode generato in una pagina o articolo WordPress per mostrare il booking del tour selezionato.
        </p>
    </div>
    <?php
}

// AGGIUNGI LA PAGINA AL MENU DI WORDPRESS
function bertoldi_boats_booking_add_admin_menu() {
    add_options_page(
        'Bertoldi Boats Booking',
        'Bertoldi Boats Booking',
        'manage_options',
        'bertoldi-boats-booking',
        'bertoldi_boats_booking_settings_page'
    );
}
add_action('admin_menu', 'bertoldi_boats_booking_add_admin_menu');
