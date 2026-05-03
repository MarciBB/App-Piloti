<div class="ricerca">
    
                                                <form name="form_search" id="thm-tk-advancedsearch-form form_search" class="clearfix" action="/splash_search.php" method="post">
                                                    <input name="TipoViaggioId" id="TipoViaggioId" value="1" type="hidden">
                                                    <div class="thm-tk-input-4-1">
                                                        <label for="ComuneFermataPickup"><?=$dizionario['Partenza']?></label>
											            <input required id="ComuneFermataPickup" name="ComuneFermataPickup" type="text" onClick="chiudiContenitoreAndata()" style="float: left;" placeholder="<?php echo $dizionario['citta_partenza'];?>">
											            <a href="javascript: void(0)" tabindex="-1" onClick="mostraFermateAndata()" id="ArrowAndataApri"><img class="down_arrow" src="/images/down_arrow.png" alt="<?=$dizionario['Tutte-Le-Fermate']?>" title="<?=$dizionario['Tutte-Le-Fermate']?>" /></a>
											            <a href="javascript: void(0)" onClick="chiudiContenitoreAndata()" id="ArrowAndataChiudi" style="display:none"><img class="down_arrow" src="/images/up_arrow.png" alt="<?=$dizionario['Tutte-Le-Fermate']?>" title="<?=$dizionario['Tutte-Le-Fermate']?>" /></a>
											            <div class="clear"></div>
											            <div id="fermateAndataContainer" class="contenitoreFermate" style="display: none;">
											                <a href="javascript: void(0)" onClick="chiudiContenitoreAndata()" ><img src="/images/close.png" alt="<?=$dizionario['Chiudi']?>" title="<?=$dizionario['Chiudi']?>" class="chiudiContenitoreFermate" /></a>
											                <div class="clear"></div>
											                <ul id="fermateAndata">
											                    
											                </ul>
											            </div>
											            <input name="FermataPickup" id="FermataPickup" value="" type="hidden">
											            <input name="ComuneIdPickup" id="ComuneIdPickup" value="" type="hidden">
                                                    </div>
                                                    <div class="thm-tk-input-4-1">
                                                        <label for="ComuneFermataPickup"><?=$dizionario['Arrivo']?></label>                               
											            <input required id="ComuneFermataDropOff" name="ComuneFermataDropOff" onClick="chiudiContenitoreRitorno()" type="text" style="float: left;" placeholder="<?php echo $dizionario['citta_arrivo'];?>">
											            <a href="javascript: void(0)" tabindex="-1"  onClick="mostraFermateRitorno()" id="ArrowRitornoApri"><img class="down_arrow" src="/images/down_arrow.png" alt="<?=$dizionario['Tutte-Le-Fermate']?>" title="<?=$dizionario['Tutte-Le-Fermate']?>" /></a>
											            <a href="javascript: void(0)" onClick="chiudiContenitoreRitorno()" id="ArrowRitornoChiudi" style="display:none"><img class="down_arrow" src="/images/up_arrow.png" alt="<?=$dizionario['Tutte-Le-Fermate']?>" title="<?=$dizionario['Tutte-Le-Fermate']?>" /></a>
											            <div class="clear"></div>
											            <div id="fermateRitornoContainer" class="contenitoreFermate" style="display: none;">
											                <a href="javascript: void(0)" onClick="chiudiContenitoreRitorno()" ><img src="/images/close.png" alt="<?=$dizionario['Chiudi']?>" title="<?=$dizionario['Chiudi']?>" class="chiudiContenitoreFermate" /></a>
											                <div class="clear"></div>
											                <ul id="fermateRitorno">
											                    
											                </ul>
											            </div>
											            <input name="FermataDropOff" id="FermataDropOff" value="" type="hidden">
											            <input name="ComuneIdDropOff" id="ComuneIdDropOff" value="" type="hidden">
                                                    </div>
                                                    <div class="thm-tk-input-6-1">
                                                    	<label for="tipoviaggioid"><?php echo $dizionario['tipo_viaggio'];?></label>
                                                    	<select id="tipoviaggioid" name="tipoviaggioid" class="select2">
                                                            <option value="1"><?=$dizionario['Solo-Andata']?></option>
                                                            <option value="2"><?=$dizionario['Andata-Ritorno']?></option>
                                                            <option value="3"><?=$dizionario['Ritorno-Open']?></option>
                                                        </select>
                                                    </div>

                                                    <div class="thm-tk-input-6-1">
                                                        <label for="datadiandata"><?=$dizionario['Andata']?></label>
                                                        <input required id="datadiandata" class="thm-date-picker" name="datadiandata" value="" title="<?php echo $dizionario['data_partenza'];?>" type="text" placeholder="<?php echo $dizionario['data_partenza'];?>">
            											<span class="data_andata error"></span>
                                                    </div>
                                                    <div class="thm-tk-input-6-1">
                                                        <label for="datadiritorno"><?=$dizionario['Ritorno']?></label>
                                                        <input id="datadiritorno" class="thm-date-picker" name="datadiritorno" value="" title="<?php echo $dizionario['data_arrivo'];?>" type="text" placeholder="<?php echo $dizionario['data_arrivo'];?>">
            											<span class="fata_ritorno error"></span>
                                                    </div>
                                                    <input type="hidden" name="post_type" value="#">
                                                    <button id="submit" class="btn btn-primary thm-tk-search-btn" type="submit"><?php echo $dizionario['prenota_ricerca'];?></button>
                                                </form>
                                            
</div>