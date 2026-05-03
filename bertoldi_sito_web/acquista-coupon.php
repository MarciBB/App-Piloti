<?php

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

if ( ! session_id() ) {
   session_start();
}
if(isset($_SESSION['USER'])){
   $user = $_SESSION['USER'];
 }

$PageTitle = $dizionario['coupon']['titolo'];
$PageDescription = $dizionario['coupon']['descr'];
$PageKeywords = $dizionario['coupon']['key'];

$LinkActive = 4;

$page_title = $dizionario['coupon']['titolo'];
$page_parent = $dizionario['coupon']['descr'];

include_once($basepath . "/include/meta.php");

global $search;

$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;

?>

<style>
:root {
    --primary-color: #007bff;
    --primary-light: #e7f3ff;
    --primary-dark: #0056b3;
    --secondary-color: #f8f9fa;
    --success-color: #28a745;
    --success-light: #e8f5e8;
    --text-color: #333;
    --text-light: #666;
    --text-lighter: #888;
    --border-color: #ddd;
    --white: #fff;
    --border-radius: 5px;
    --font-size-base: 16px;
    --font-size-small: 14px;
    --font-size-tiny: 12px;
    --font-size-h3: 18px;
    --font-size-h4: 16px;
    --spacing-small: 10px;
    --spacing-medium: 15px;
    --spacing-large: 20px;
    --spacing-xl: 30px;
}


</style>

</head>
<body class="main-bg" id="home-applicativo">

    <!-- Top Header -->
    <?php include_once($basepath."/include/top_header.php"); ?>
    
    <div class="main-container">
        <div class="content">
            <div style="margin-bottom:10px;" class="benvenuto-user">
                <?=$dizionario['prenota']['benvenuto']?> <b><?php 
                if(isset($user)){
                    echo $user['Nome'].' '.$user['CognomeRagioneSociale'];
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
            
            <div class="coupon-form-group">
                <h3 class="coupon-title"><b><?=$dizionario['coupon']['titolo']?></b></h3>
            </div>

            <div class="ticket-list">
                
                <!-- Invito all'acquisto -->
                <div class="coupon-gradient-header">
                    <p>
                        <b><?=$dizionario['coupon']['invito_acquisto']?>!</b>
                        <br>
                        <?=$dizionario['coupon']['invito_acquisto_desc']?>
                    </p>
                </div>
                
                <!-- Come funziona -->
                <div class="coupon-info-box">
                    <h3 class="coupon-info-title"><?=$dizionario['coupon']['come_funziona']?></h3>
                    <p class="coupon-info-text"><?=$dizionario['coupon']['come_funziona_desc']?></p>
                </div>
                
                <!-- Vantaggi -->
                <div class="coupon-form-group">
                    <h3 class="coupon-advantages-title"><?=$dizionario['coupon']['vantaggi_titolo']?></h3>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="coupon-advantage-card">
                                <i class="fa fa-calendar coupon-advantage-icon"></i>
                                <h4 class="coupon-advantage-title"><?=$dizionario['coupon']['vantaggio_1']?></h4>
                                <p class="coupon-advantage-desc"><?=$dizionario['coupon']['vantaggio_1_desc']?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="coupon-advantage-card">
                                <i class="fa fa-refresh coupon-advantage-icon"></i>
                                <h4 class="coupon-advantage-title"><?=$dizionario['coupon']['vantaggio_2']?></h4>
                                <p class="coupon-advantage-desc"><?=$dizionario['coupon']['vantaggio_2_desc']?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="coupon-advantage-card">
                                <i class="fa fa-flash coupon-advantage-icon"></i>
                                <h4 class="coupon-advantage-title"><?=$dizionario['coupon']['vantaggio_3']?></h4>
                                <p class="coupon-advantage-desc"><?=$dizionario['coupon']['vantaggio_3_desc']?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="coupon-advantage-card">
                                <i class="fa fa-gift coupon-advantage-icon"></i>
                                <h4 class="coupon-advantage-title"><?=$dizionario['coupon']['vantaggio_4']?></h4>
                                <p class="coupon-advantage-desc"><?=$dizionario['coupon']['vantaggio_4_desc']?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form di acquisto -->
                <div class="allTicket">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="coupon-form-container">
                                <h3 class="coupon-form-title"><?=$dizionario['coupon']['importi_disponibili']?></h3>
                                <p class="coupon-form-desc"><?=$dizionario['coupon']['importi_desc']?></p>
                                
                                <form id="couponForm" method="post" action="/splash_coupon.php">
                                    <!-- Informazioni acquirente -->
                                    <div class="coupon-form-group">
                                        <label class="coupon-form-label"><?=$dizionario['coupon']['email_acquirente']?></label>
                                        <input type="email" 
                                               name="email_acquirente" 
                                               id="emailAcquirente" 
                                               required 
                                               class="coupon-form-input"
                                               value="<?=isset($user['Email']) ? $user['Email'] : ''?>"
                                               placeholder="<?=$dizionario['coupon']['email_acquirente_placeholder']?>">
                                    </div>
                                    
                                    <!-- Tipo coupon -->
                                    <div class="coupon-form-group">
                                        <label class="coupon-form-label"><?=$dizionario['coupon']['tipo_coupon']?></label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="tipo-label coupon-radio-option selected">
                                                    <input type="radio" name="tipo_coupon" value="personale" checked>
                                                    <i class="fa fa-user"></i>
                                                    <?=$dizionario['coupon']['uso_personale']?>
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="tipo-label coupon-radio-option">
                                                    <input type="radio" name="tipo_coupon" value="regalo">
                                                    <i class="fa fa-gift"></i>
                                                    <?=$dizionario['coupon']['buono_regalo']?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Informazioni destinatario -->
                                    <div id="infoDestinatario" class="coupon-info-destinatario">
                                        <h4 class="coupon-info-destinatario-title"><?=$dizionario['coupon']['info_destinatario']?></h4>
                                        <div class="coupon-form-input-group">
                                            <label class="coupon-form-label"><?=$dizionario['coupon']['email_destinatario']?></label>
                                            <input type="email" 
                                                   name="email_destinatario" 
                                                   id="emailDestinatario" 
                                                   class="coupon-form-input"
                                                   placeholder="<?=$dizionario['coupon']['email_destinatario_placeholder']?>">
                                        </div>
                                        <div class="coupon-form-input-group">
                                            <label class="coupon-form-label"><?=$dizionario['coupon']['messaggio_destinatario']?></label>
                                            <textarea name="messaggio_destinatario" 
                                                      id="messaggioDestinatario" 
                                                      class="coupon-form-textarea"
                                                      placeholder="<?=$dizionario['coupon']['messaggio_destinatario_placeholder']?>"></textarea>
                                            <small class="coupon-form-small-text"><?=$dizionario['coupon']['massimo_caratteri']?></small>
                                        </div>
                                    </div>

                                    <!-- Selezione importo -->
                                    <div class="coupon-form-group">
                                        <label class="coupon-form-label"><?=$dizionario['coupon']['seleziona_importo']?></label>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="importo-label coupon-importo-option">
                                                    <input type="radio" name="importo" value="25">
                                                    25€
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="importo-label coupon-importo-option">
                                                    <input type="radio" name="importo" value="50">
                                                    50€
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="importo-label coupon-importo-option">
                                                    <input type="radio" name="importo" value="100">
                                                    100€
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="importo-label coupon-importo-option">
                                                    <input type="radio" name="importo" value="150">
                                                    150€
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <!-- Importo personalizzato -->
                                        <div style="margin-top: 15px;">
                                            <label class="coupon-form-label"><?=$dizionario['coupon']['importo_personalizzato']?></label>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="coupon-custom-amount-wrapper">
                                                        <input type="number" 
                                                               id="importoCustom" 
                                                               name="importo_custom" 
                                                               placeholder="<?=$dizionario['coupon']['importo_personalizzato_placeholder']?>" 
                                                               min="10" 
                                                               max="1000" 
                                                               step="1" 
                                                               class="coupon-custom-amount-input">
                                                        <span class="coupon-custom-amount-currency">€</span>
                                                    </div>
                                                    <small class="coupon-form-small-text"><?=$dizionario['coupon']['importo_range']?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Metodo di pagamento -->
                                    <div class="coupon-form-group">
                                        <label class="coupon-form-label"><?=$dizionario['coupon']['tipo_pagamento']?></label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="pagamento-label coupon-radio-option selected">
                                                    <input type="radio" name="pagamento" value="stripe" checked>
                                                    <i class="fa fa-credit-card"></i>
                                                    <?=$dizionario['coupon']['stripe_carta']?>
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="pagamento-label coupon-radio-option">
                                                    <input type="radio" name="pagamento" value="paypal">
                                                    <i class="fa fa-paypal"></i>
                                                    <?=$dizionario['coupon']['paypal']?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div style="text-align: center; margin-top: 30px;">
                                        <button type="submit" class="coupon-submit-button">
                                            <i class="fa fa-shopping-cart"></i> <?=$dizionario['coupon']['prenota_subito']?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Modalità di utilizzo -->
                            <div class="coupon-sidebar-box">
                                <h4 class="coupon-sidebar-title"><?=$dizionario['coupon']['modalita_utilizzo']?></h4>
                                <ol class="coupon-sidebar-list">
                                    <li><?=$dizionario['coupon']['utilizzo_1']?></li>
                                    <li><?=$dizionario['coupon']['utilizzo_2']?></li>
                                    <li><?=$dizionario['coupon']['utilizzo_3']?></li>
                                    <li><?=$dizionario['coupon']['utilizzo_4']?></li>
                                </ol>
                            </div>
                            
                            <!-- Sicurezza pagamento -->
                            <div class="coupon-security-box">
                                <h4 class="coupon-security-title">
                                    <i class="fa fa-shield"></i> <?=$dizionario['coupon']['sicurezza_pagamento']?>
                                </h4>
                                <p class="coupon-security-text"><?=$dizionario['coupon']['sicurezza_desc']?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Termini e condizioni -->
                <div class="coupon-terms-box">
                    <h4 class="coupon-terms-title"><?=$dizionario['coupon']['termini_condizioni']?></h4>
                    <ul class="coupon-terms-list">
                        <li><?=$dizionario['coupon']['termine_1']?></li>
                        <li><?=$dizionario['coupon']['termine_2']?></li>
                        <li><?=$dizionario['coupon']['termine_3']?></li>
                        <li><?=$dizionario['coupon']['termine_4']?></li>
                    </ul>
                </div>
                
                <!-- Assistenza -->
                <div class="coupon-support-box">
                    <h4 class="coupon-support-title"><?=$dizionario['coupon']['assistenza']?></h4>
                    <p class="coupon-support-text"><?=$dizionario['coupon']['assistenza_desc']?></p>
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

<script>
$(document).ready(function(){

    $('#importoCustom').on('input', function() {
        var value = this.value.replace(/[^0-9]/g, '');
        this.value = value;
    });

    $('#importoCustom').on('change', function() {
        var value = this.value;
        var min = parseInt($(this).attr('min'));
        var max = parseInt($(this).attr('max'));
        
        if (value !== '') {
            var numericValue = parseInt(value);
            if (numericValue < min) numericValue = min;
            if (numericValue > max) numericValue = max;
            value = numericValue;
        }
        this.value = value;
    });
    
    // Gestione tipo coupon
    $('.tipo-label').click(function(){
        $('.tipo-label').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        
        if ($(this).find('input[type="radio"]').val() === 'regalo') {
            $('#infoDestinatario').slideDown();
            $('#emailDestinatario').prop('required', true);
        } else {
            $('#infoDestinatario').slideUp();
            $('#emailDestinatario').prop('required', false);
            $('#emailDestinatario').val('');
            $('#messaggioDestinatario').val('');
        }
    });
    
    // Gestione selezione importo
    $('.importo-label').click(function(){
        $('.importo-label').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        
        $('#importoCustom').val('');
        $('.coupon-custom-amount-wrapper').removeClass('selected');
    });
    
    // Gestione importo personalizzato
    $('#importoCustom').on('input focus', function(){
        $('.importo-label').removeClass('selected');
        $('input[name="importo"]').prop('checked', false);
        $('.coupon-custom-amount-wrapper').addClass('selected');
    });
    
    // Gestione selezione metodo pagamento
    $('.pagamento-label').click(function(){
        $('.pagamento-label').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
    });
    
    // Limitazione caratteri messaggio
    $('#messaggioDestinatario').on('input', function(){
        var maxLength = 500;
        var currentLength = $(this).val().length;
        
        if (currentLength > maxLength) {
            $(this).val($(this).val().substring(0, maxLength));
            currentLength = maxLength;
        }
        
        $(this).next('small').text('<?=$dizionario['coupon']['caratteri_rimanenti']?>' + (maxLength - currentLength));
    });
    
    // Validazione form
    $('#couponForm').submit(function(e){
        var emailAcquirente = $('#emailAcquirente').val();
        var tipoCoupon = $('input[name="tipo_coupon"]:checked').val();
        var emailDestinatario = $('#emailDestinatario').val();
        var importo = $('input[name="importo"]:checked').val();
        var importoCustom = $('#importoCustom').val();
        var pagamento = $('input[name="pagamento"]:checked').val();
        
        if (!emailAcquirente) {
            e.preventDefault();
            alert('<?=$dizionario['coupon']['alert_email_acquirente']?>');
            $('#emailAcquirente').focus();
            return false;
        }
        
        if (tipoCoupon === 'regalo' && !emailDestinatario) {
            e.preventDefault();
            alert('<?=$dizionario['coupon']['alert_email_destinatario']?>');
            $('#emailDestinatario').focus();
            return false;
        }
        
        if (!importo && !importoCustom) {
            e.preventDefault();
            alert('<?=$dizionario['coupon']['alert_importo']?>');
            return false;
        }
        
        if (importoCustom) {
            var customValue = parseFloat(importoCustom);
            if (customValue < 10 || customValue > 1000) {
                e.preventDefault();
                alert('<?=$dizionario['coupon']['alert_importo_range']?>');
                $('#importoCustom').focus();
                return false;
            }
        }
        
        if (!pagamento) {
            e.preventDefault();
            alert('<?=$dizionario['coupon']['alert_pagamento']?>');
            return false;
        }
    });
});
</script>

</html>

