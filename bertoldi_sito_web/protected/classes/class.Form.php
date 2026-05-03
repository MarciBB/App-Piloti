<?php
    class Form 
    {
		public function create_orizontal_checkbox_esito_mediazione($ArrObject,$ValueToCheck,$name,$label,$field1,$field2,$additionalParams) {
		?>
			<div class="brain_campoForm">
				<span class="brain_tipo"><?=$label?>:</span>
			    <div class="radio_group">
                <?php
				$ArrObjectSize=count($ArrObject);
				$i=0;
				while ($i< $ArrObjectSize) {
					$value=$ArrObject[$i][$field1];
					$label=$ArrObject[$i][$field2];
					$check="";
					if ($value==$ValueToCheck)
						$check="checked";       
					?>  
					<input type="radio" name="<?=$name?>" id="<?=$name."_".$value?>" value="<?=$value?>" <?=$check?> 
						<?= $this->add_paramms($additionalParams); ?>              
                    />
					<label for="<?=$name?>" class="brain_esitomediazione<?=$value?>"><?=$label?></label>
					<?php
					$i++;
				} ?>
			</div>
		</div>
		<?php 
		}

        
        
                        public function create_orizontal_checkbox($ArrObject,$ValueToCheck,$name,$label,$field1,$field2,$additionalParams)
                        {
                            ?>
                            <div class="brain_rowBig">
                            <span class="brain_tipo"><?=$label?>:</span>
                            <?php 
                            $ArrObjectSize=count($ArrObject);
                            $i=0;
                            while ($i< $ArrObjectSize)
                            {
                                $value=$ArrObject[$i][$field1];
                                $label=$ArrObject[$i][$field2];
                                $check="";
                                if ($value==$ValueToCheck)
                                 $check="checked";       
                                ?>  
                                <input type="radio" name="<?=$name?>" id="<?=$name."_".$value?>" value="<?=$value?>" <?=$check?> 
                                  <?= $this->add_paramms($additionalParams); ?>              
                                       
                                       
                                />
                                <label for="<?=$name?>" class="brain_tipoanagrafica<?=$value?>"><?=$label?></label>

                                <?php 

                                $i++;
                            }?>
                                <br style="clear:both;"/>
			    </div>
                                <?php 
                        }
                        
                        
                        
                         public function create_select($label,$name,$id,$class,$ArrObject,$ValueToCheck,$field1,$field2,$additionalParams,$required=0,$commento=null)
                        {                       
                           
                        ?>
                           <div class="<?=$class?>">
                           <label for="<?=$name?>">
                             <?php  if ($required) echo ("<span class=\"required\"><span class=\"hidden\">*</span>".$label." </span>");
                                else 
                                echo($label);  
                             
                            ?>
                           </label>
                                <?php  if ($class=="brain_campoForm") print("<br />");?>
			  

                            <select name="<?=$name?>" id="<?=$id?>"
                               <?= $this->add_paramms($additionalParams); ?>        
                                    
                                    
                             > 
                           <option  value="">- seleziona -</option>  
                           <?php 
                        if ($ArrObject)
                        {    
                         $ArrObjectSize=count($ArrObject);
                        $i=0;
                        
                        
                        
                            while ($i< $ArrObjectSize)
                            {
                                $value=$ArrObject[$i][$field1];
                                $label=$ArrObject[$i][$field2];
                                $check="";
                                if (trim($value)==trim($ValueToCheck))
                                 $check="selected";       
                                ?>  
                                <option <?=$check?> value="<?=$value?>"><?=$label?></option>
                                <?php 

                                $i++;
                            }
                            ?>
                       
                        <?php 
                        }
                        ?>
                                  </select>
                               <?php 
                               if($commento!=null) echo(" <span class=\"corrsipondenza\">$commento</span>");
                               ?>
                           </div>
                           <?php 
                        
                        }
                        
                        public function create_select_group($label,$name,$id,$class,$ArrObject,$ValueToCheck,$field1,$field2,$additionalParams,$required=0)
                        {
                       
                           
                        ?>
                           <div class="<?=$class?>">
                           <label for="<?=$name?>">
                             <?php  if ($required) echo ("<span class=\"required\"><span class=\"hidden\">*</span>".$label." </span>");
                                else 
                                echo($label);  
                             
                            ?>
                           </label><br />
			  
                            <select name="<?=$name?>" id="<?=$id?>"
                               <?= $this->add_paramms($additionalParams); ?>        
                                    
                                    
                             > 
                           <option  value="">- seleziona -</option>  
                           <?php 
                           $aperto1=false;
                           $aperto2=false;
                        if ($ArrObject)
                        {    
                         $ArrObjectSize=count($ArrObject);
                        $i=0;
                        
                        
                        
                            while ($i< $ArrObjectSize)
                            {
                                
                                if (($ArrObject[$i]['MateriaTipoId']=="1") and ($aperto1==false))
                                {
                                    $aperto1=true;
                                 ?>  
                                
                                 <optgroup label="Materie obbligatorie">
                                 
                                 <?php  
                                }
                               if (($ArrObject[$i]['MateriaTipoId']=="2") and ($aperto2==false))
                                {
                                   $aperto2=true;
                                   ?>   
                                 </optgroup>    
                                 <optgroup label="Materie opzionali">
                                   
                                 <?php   
                                    
                                }    
                                
                                $value=$ArrObject[$i][$field1];
                                $label=$ArrObject[$i][$field2];
                                $check="";
                                if (trim($value)==trim($ValueToCheck))
                                 $check="selected";       
                                ?>  
                                   <option <?=$check?> value="<?=$value?>"><?=$label?></option>
                                <?php 

                                $i++;
                            
                                
                                
                            }
                            ?>
                                 </optgroup>
                        <?php 
                        }
                        ?>
                                  </select>
                           </div>
                           <?php 
                        
                        }
                        
                        private function add_paramms($additionalParams1)
                        {
                            
                            
                            if(!empty($additionalParams1)) {
				foreach($additionalParams1 as $key => $value)
                                {
					
                                         echo($key."=".$value." ");
                                }        
                            } 
                            
                        }
                        
                        
                        
                         public function create_textbox($label,$id,$name,$value="",$required=0,$class,$additionalParams,$br="<br />",$inputsize="20", $maxlength="255",$commento="")
                        {
                             
                             
                           ?>
                            <div class="<?=$class?>">
                            <label for="<?=$name?>">
                             <?php  if ($required) echo ("<span class=\"required\"><span class=\"hidden\">*</span>".$label." </span>");
                                else 
                                echo($label);
                                 
                                 
                                 ?>
                            
                            
                            
                            </label>
                             <?php  echo($br); ?>
                                
                            <input size="<?=$inputsize?>" maxlength="<?=$maxlength;?>" type="text" name="<?=$name?>" id="<?=$id?>" value="<?=$value?>" 
                             <?= $this->add_paramms($additionalParams); ?>
                                   
                                   
                             />&nbsp;
                            <?=$commento?>
                            </div>
                            <?php 
                            
                            
                        }
                        
                         public function create_input_file($label,$id,$name,$value="",$required=0,$class,$additionalParams)
                        {
                             
                             
                           ?>
                            <div class="<?=$class?>">
                            <label for="<?=$name?>">
                             <?php  if ($required) echo ("<span class=\"required\"><span class=\"hidden\">*</span>".$label." </span>");
                                else 
                                echo($label);
                                 
                                 
                                 ?>
                            
                            
                            
                            </label>
                                 <?php  if ($class=="brain_campoForm") print("<br />");?>
                            <input type="file" name="<?=$name?>" id="<?=$id?>" value="<?=$value?>" 
                             <?= $this->add_paramms($additionalParams); ?>
                                   
                                   
                             />
                            </div>
                            <?php 
                            
                            
                        }
                        
                         public function create_input_checkbox($label,$id,$name,$value="",$required=0,$class,$additionalParams,$br="<br />")
                        {
                             
                             
                           ?>
                            <div class="<?=$class?>">
                            <label for="<?=$name?>">
                             <?php  if ($required) echo ("<span class=\"required\"><span class=\"hidden\">*</span>".$label." </span>");
                                else 
                                echo($label);
                                 
                                 
                                 ?>
                            
                            
                            
                            </label>
                                 <?php  print ($br); ?>
                            <input type="checkbox" name="<?=$name?>" id="<?=$id?>" value="<?=$value?>" 
                             <?php  $this->add_paramms($additionalParams); ?>
                                   
                                   
                             />
                            </div>
                            <?php 
                            
                            
                        }
                        
                        
                         public function create_textbox_with_sel($label,$id,$name,$value="",$required=0,$class,$additionalParams,$sel,$br="<br />",$inputsize="20")
                        {
                             
                             
                           ?>
                            <div class="<?=$class?> brainConSelector">
                            
                                <label for="<?=$name?>">
                             <?php  if ($required) echo ("<span class=\"required\"><span class=\"hidden\">*</span>".$label." </span>");
                                else 
                                echo($label);
                                 
                                 
                                 ?>
                            
                            
                            
                            </label>
                                 <?php  print ($br); ?>
                                <div class="brainSelector"><?=$sel?></div>
                            <input size="<?=$inputsize?>" type="text" name="<?=$name?>" id="<?=$id?>" value="<?=$value?>" 
                             <?= $this->add_paramms($additionalParams); ?>
                                   
                                   
                             />
                            
                            </div>
                            <?php 
                            
                            
                        }
                        
                          public function create_textbox_password($label,$id,$name,$value="",$required=0,$class,$additionalParams,$br="<br />")
                        {
                             
                             
                           ?>
                            <div class="<?=$class?>">
                            <label for="<?=$name?>">
                             <?php  if ($required) echo ("<span class=\"required\"><span class=\"hidden\">*</span>".$label." </span>");
                                else 
                                echo($label);                                 
                             ?>
                            </label>
                                 <?php  print ($br); ?>
                            <input type="password" name="<?=$name?>" id="<?=$id?>" value="<?=$value?>" 
                             <?= $this->add_paramms($additionalParams); ?>
                             />
                            </div>
                            <?php 
                            
                            
                        }
                        
                         public function create_texarea($label,$id,$name,$value="",$required=0,$class,$additionalParams,$br="<br />")
                        {
                           ?>
                            <div class="<?=$class?>">
                            <label for="<?=$name?>">
                             <?php  if ($required) echo ("<span class=\"required\"><span class=\"hidden\">*</span>".$label." </span>");
                                else 
                                echo($label);
                                 
                                 
                                 ?>
                            
                            
                            
                            </label>
                                <?php  print ($br); ?>
                            <textarea name="<?=$name?>" id="<?=$id?>" 
                             <?=  $this->add_paramms($additionalParams); ?>
                                   
                                   
                             ><?=$value?></textarea>
                            </div>
                            <?php 
                            
                            
                        }
                        
			
                       
                          public function create_textbox_hidden($name,$value)
                        {
                           ?>
                            <input type="hidden" name="<?=$name?>" id="<?=$name?>" value="<?=$value?>" />
                          
                            <?php 
                            
                            
                        }
                        
                          public function create_textbox_hidden_differentId($id,$name,$value)
                        {
                           ?>
                            <input type="hidden" name="<?=$name?>" id="<?=$id?>" value="<?=$value?>" />
                          
                            <?php 
                            
                            
                        }
                        
                        
                        
                        
                        public function create_button ($label,$name,$value,$class,$type)
                        {
                           ?>
                            <input type="<?=$type?>" name="<?=$name?>" class="<?=$class?>" id="<?=$name?>" value="<?=$value?>" />
                            <?php 
                            
                            
                        }        
                                
                                

}		
		

?>	