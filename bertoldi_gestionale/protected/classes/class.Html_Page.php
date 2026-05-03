
<?php

class Html_Page
{
    public function __construct()
    {
    }

    public function html_header()
    {
        global $user, $dizionario;
        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <?php
            if ((browser_detection('browser') != 'moz') && (browser_detection('browser') != 'saf')) { ?>
                <script type="text/javascript">
                    window.location = "getfirefox.php";
                </script>
            <?php } ?>

            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php echo Config::$application_name; ?> Ver. <?php echo Config::$application_version; ?> - <?php echo Config::$application_company; ?></title>

            <link rel="stylesheet" type="text/css" href="/css/reset.css" />
            <link rel="stylesheet" type="text/css" href="/css/style.css?v=3" />
            <link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css" />
            <script type="text/javascript" src="/js/jquery.min.js"></script>
            <script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
            <script type="text/javascript" src="/js/jquery.maskedinput-1.1.4.js"></script>
            <script type="text/javascript" src="/js/menu_hover.js"></script>
            <script type="text/javascript" language="javascript" src="/js/dialogbox.js"></script>
            <script type="text/javascript" language="javascript" src="/js/common.js"></script>
            <script type="text/javascript" language="javascript" src="/js/validate/jquery.validate.js"></script>
            <script type="text/javascript" language="javascript" src="/js/jquery.form.track.changes.js"></script>
            <script type="text/javascript" language="javascript" src="/js/ui.multiselect.js"></script>

            <link href="/js/select2.min.css" rel="stylesheet" type="text/css" />
            <script type="text/javascript" src="/js/select2.min.js"></script>

            <!--[if lte IE 8]>
            <link rel="stylesheet" href="/css/styleIE.css" type="text/css" />
            <![endif]-->
            <!-- Quantcast Choice. Consent Manager Tag v2.0 (for TCF 2.0) -->
            <!-- RIMOSSO SCRIPT QUANTCAST CMP -->
            <!-- End Quantcast Choice. Consent Manager Tag v2.0 (for TCF 2.0) -->
        </head>
        <body>
            <div style="position: absolute; display: none;" id="tooltip">
                <!-- menu stuff in here -->
            </div>
            <!-- loading div tab -->

            <div id="brain_loading"><?php echo $dizionario['generale']['caricamento_dati']; ?></div>
            <div id="layer_nero2" class="notifica">
                <div id="loading_big_loading">
                    <p><i class='fa fa-spinner loading-white' aria-hidden='true' alt="<?php echo $dizionario['generale']['caricamento_attendere']; ?>"></i> <?php echo $dizionario['generale']['caricamento_attendere']; ?></p>
                </div>
            </div>
            <!-- loading div -->

            <div id="layer_nero" class="notifica">
                <div id="loading_big_loading">
                    <p><i class='fa fa-spinner loading-white' aria-hidden='true' alt="<?php echo $dizionario['generale']['caricamento_attendere']; ?>"></i> <?php echo $dizionario['generale']['caricamento_attendere']; ?></p>
                </div>
            </div>
            <div id="loading_big_ok" class="notifica">
                <div id="loading_big_loading">
                    <img src="/images/loading_ok.png" alt="<?php echo $dizionario['generale']['operazione_completata']; ?>" />
                    <p><?php echo $dizionario['generale']['operazione_completata']; ?></p>
                </div>
            </div>
            <div id="loading_big_no" class="notifica">
                <div id="loading_big_loading">
                    <img src="/images/loading_err.png" alt="<?php echo $dizionario['generale']['errore_operazione']; ?>" />
                    <p><?php echo $dizionario['generale']['errore_operazione']; ?></p>
                </div>
            </div>
            <!-- loading div -->

            <div id="brain_oscura" class="brain_oscura">
                <div id="brain_boxCentrato">
                    <div id="brain_listaSelezione" class="brain_dialogbox"></div>
                    <br style="clear:both;" />
                </div>
            </div>

            <div id="brain_oscura_pre" class="brain_oscura">
            </div>
        <?php
    }

    public function html_footer()
    {
        global $dizionario;
        ?>
        </div>
        <div class="hid">a</div>
        <div class="footer">Bertoldi Boats - Biglietteria Online &copy;<?php echo Date('Y'); ?> - All rights reserved powered by Brain Computing S.p.A</div>
        <footer></footer>

        </body>
        </html>
        <?php
    }

    public function html_titolo_pagina($titolo, $torna = null, $modulo = null, $pagina = null)
    {
        global $dizionario;
        ?>
        <div class="brain_titoloPagina">
            <?php if (isset($torna) && $torna == 1) { ?>
                <div id="back_list">
                    <a href="javascript:void(0);" onclick="loadMainContent('<?php echo $modulo; ?>','<?php echo $pagina; ?>',this);">
                        <i class="fa fa-arrow-left orange" aria-hidden="true" title="<?= $dizionario['generale']['torna_all_elenco']; ?>"></i>
                        <?php echo $dizionario['generale']['torna_all_elenco']; ?>
                    </a>
                </div>
            <?php } ?>
            <h2><?php echo $titolo; ?></h2>
        </div>
        <div id="brain_page-content">
        <?php
    }

    public function html_titolo_box($titolo)
    {
        global $dizionario;
        ?>
        <span class="brain_titleBox">
            <h1><?php echo $titolo; ?></h1>
            <a class="brain_buttonChiudi" href="javascript:void(0);" onclick="javascript:ChiudiBox();" title="chiudi"><?php echo $dizionario['generale']['chiudi']; ?></a>
        </span>
        <?php
    }

    public function html_tasto_lista($classe, $cartella, $page, $titolo, $icona = '')
    {
        ?>
        <div class="brain_servicebar">
            <a class="<?php echo $classe; ?>" href="javascript:void(0);" onclick="loadMainContent('<?php echo $cartella; ?>','<?php echo $page; ?>',this);" title="<?php echo $titolo; ?>"><?php echo $icona; ?><?php echo $titolo; ?></a>
        </div>
        <?php
    }
}
