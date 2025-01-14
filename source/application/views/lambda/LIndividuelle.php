<!--
<script language="JavaScript">
    
	<?php $key = uniqid() . '-' . rand() * 10; ?>
	webcam.set_api_url( '<?php echo base_url(); ?>/assets/js/jpegcam/test.php?key=<?php echo $key; ?>');
	webcam.set_key('<?php echo $key; ?>');
	webcam.set_swf_url( '<?php echo base_url(); ?>/assets/js/jpegcam/webcam.swf' );
	webcam.set_stealth( true ); // enable stealth mode
	
	webcam.set_hook( 'onComplete', 'my_completion_handler' );

	function take_snapshot() {
	    webcam.snap();
	}

	function my_completion_handler(msg) {
	    $('#photo_webcam').val(msg);
	}
	
	$(document).ready(function(){
	
	    $('.webcam').html(webcam.get_html(272, 362));
	
	    $('.startWebcam').click(function(){
		$('.webcamWrapper').show();
	    });
	    
	    $('.captureCam').click(function(){
		take_snapshot();
		$('.webcamWrapper').hide();
	    });
	    
	    $('.closeCam').click(function(){
		$('.webcamWrapper').hide();
	    });
	    
	});

</script>
-->


<div class="wrap">
	
	<h1><?php echo lang('demandeAccred'); ?></h1>
	
	<input id="lang" type="hidden" value="<?php echo $lang; ?>" />
	
	<div class="box-small">
	
	<span class="info"><h4><?php echo lang('inscription'); ?></h4> <?php echo lang('individuelle'); ?></span><br>
	<span class="info"><h4><?php echo lang('evenement'); ?></h4> <?php echo $event_info[0]->libelleevenement; ?></span><br>
	
	<br><br>
	<span class="info">* <?php echo lang('mentionChampObligatoire'); ?></span><br>
	<form action="<?php echo site_url('inscription/ajouter/' . $event_id); ?>" method="POST" enctype="multipart/form-data">
		
		<input type="hidden" id="evenement" name="evenement" value="<?php echo $event_id; ?>" />
		
		<label><?php echo lang('nom'); ?>*</label>
		<input type="text" value="<?php echo set_value('nom'); ?>" id="nom" name="nom" style="text-transform: uppercase"/>
		<?php echo form_error('nom'); ?>
		
		<label><?php echo lang('prenom'); ?>*</label>
		<input type="text" value="<?php echo set_value('prenom'); ?>" id="prenom" name="prenom" />
		<?php echo form_error('prenom'); ?>
		
		<label><?php echo lang('pays'); ?>*
			<?php foreach($listePays as $p): ?>
				<span id="<?php echo $p->idpays; ?>" class="drapeau" style="display:none;" ><?php echo img('drapeaux/' . strtolower($p->idpays) . '.gif'); ?></span>
			<?php endforeach; ?>
		</label>
		<select  id="pays" name="pays" class="select">
			<?php foreach($listePays as $pays): ?>
            <option value="<?php echo $pays->idpays; ?>" <?php echo ($pays->idpays == 'FRA')? 'selected' : '' ;?> ><?php echo $pays->nompays; ?></option><?php endforeach; ?> 
		</select>
		
		<label><?php echo lang('tel'); ?></label>
		
		<input type="text" value="<?php echo set_value('tel'); ?>" id="tel" name="tel" />
		
		<label><?php echo lang('mail'); ?>*</label>
		<input type="text" value="<?php echo set_value('mail'); ?>" id="mail" name="mail" />
		<?php echo form_error('mail'); ?>
		
		<label><?php echo lang('societe'); ?>*</label>
		<input type="text" value="<?php echo set_value('titre'); ?>" id="titre" name="titre" />
		<?php echo form_error('titre'); ?>
		
		<div>
		<label><?php echo lang('categorie'); ?></label>
		<select  id="categorie" name="categorie[]" class="select dyn-selector">
			<option value="-1"><?php echo lang('neSaisPas'); ?></option>
			<?php foreach($listeCategorie as $cate): ?>
			<option value="<?php echo $cate['db']->idcategorie; ?>" >
				<?php for($i=0; $i<$cate['depth']; $i++) echo '&#160;&#160;'; ?>
				<?php echo $cate['db']->libellecategorie; ?>
			</option>
			<?php endforeach; ?>
		</select>
		</div>
		
		<div class="sous-categories"></div>	
		
		<label><?php echo lang('demandeAjoutFonction'); ?></label>

		<input type="text" value="<?php echo set_value('fonction'); ?>" id="fonction" name="fonction" />
				
		<input type="file" name="photo_file" id="photo_file" />
		<input type="hidden" name="photo_webcam" id="photo_webcam" />
		
		<div class="photo">
			
			<div class="webcamWrapper">
			    <a href="#" class="closeCam">x</a>
			    <br>
			    <div class="webcam"></div>
			    <br>
			    <a href="#" class="captureCam">Prendre une photo</a>
			</div>
			
			<fieldset class="encadrePhoto">
				<legend><?php echo lang('photo'); ?></legend>
				<div class="optionPhoto">
					<span class="uploadFichier"><?php echo lang('fichier'); ?></span>
				</div>
                <!--
				<div class="optionPhoto">
					<span class="startWebcam"><?php echo lang('camera'); ?></span>
				</div>
				-->
			</fieldset>

		</div>

		<div class="clear"></div>
		<input type="submit" name="valider" id="valider" value="<?php echo lang('valider'); ?>"/>
		<div class="clear"></div>
		</div>	
		
		<div class="clear"></div>
	</form>

	</div>
	
</div>