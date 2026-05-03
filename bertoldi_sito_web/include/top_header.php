<?php 
global $dizionario;

if(isset($userInfo)) {
	$user = $userInfo;
}

if(isset($user) && $user != "" ){
	$testoPulsante = $dizionario['area_clienti']['profilo_pulsante'];
} else {
	$testoPulsante = $dizionario['area_clienti']['accedi_pulsante'];
}
 ?>      
<div id="top-menu">
	<a href="/<?= $_SESSION['code_gestore'] ?>" title="Bertoldi Boats">
		<img src="/images/logo.png" class="logo" alt="Bertoldi Boats">
	</a>
	<div class="buttons-container">
	<button class="btn btn-outline" onclick="window.location.href='/area-clienti.php'">
		<span class="nowhitespace"><?=$testoPulsante?></span>
		<svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
			<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
		</svg>
	</button>
	<?php
    // Mappa lingue => file bandiera
    $langs = [
        'it' => 'bandiera_it.png',
        'en' => 'bandiera_uk.png',
        'de' => 'bandiera_de.png',
        'fr' => 'bandiera_fr.png',
        'es' => 'bandiera_es.png'
    ];
    $currentLang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
    ?>
    <div class="lang-dropdown">
        <button class="lang-btn" onclick="document.getElementById('lang-menu').classList.toggle('show'); event.stopPropagation();">
            <img src="/images/<?= $langs[$currentLang] ?>" alt="<?= $currentLang ?>" height="24">
        </button>
        <div class="lang-menu" id="lang-menu">
            <?php foreach ($langs as $code => $img): ?>
                <?php if ($code != $currentLang): ?>
                    <a href="/index.php?lang=<?= $code ?>">
                        <img src="/images/<?= $img ?>" alt="<?= $code ?>" height="24">
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
    // Chiudi il menu se clicchi fuori
    document.addEventListener('click', function() {
        var menu = document.getElementById('lang-menu');
        if(menu) menu.classList.remove('show');
    });
    </script>
    <style>
    .lang-dropdown {
        display: inline-block;
        position: relative;
        margin-left: 10px;
        vertical-align: middle;
    }
    .lang-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }
    .lang-menu {
        display: none;
        position: absolute;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        z-index: 100;
        min-width: 40px;
        padding: 4px 0;
    }
    .lang-menu.show {
        display: block;
    }
    .lang-menu a {
        display: block;
        padding: 2px 10px;
        text-align: left;
        text-decoration: none;
    }
    .lang-menu img {
        vertical-align: middle;
    }
    </style>
	<?php if(isset($user) && $user != "" ){ ?>
		<button class="btn btn-outline" id="logout">
			<span class="nowhitespace">LOG OUT</span>
			<svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
			</svg>
		</button>
	<?php } ?>
</div>
</div>