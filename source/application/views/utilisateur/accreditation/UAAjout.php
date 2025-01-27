<script language="JavaScript">

    $(document).ready(function(){
        $('#waitOverlay').hide();
    });
    
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

        $('#doAjout').attr('disabled', 'disabled');
        $('#waitOverlay').show();

        var tries = 10;
        var myInterval = setInterval(
            function(){

                console.log('Tentative sur ' + msg + ' : ' + tries);

                $.ajax({
                    url: "<?php echo base_url(); ?>assets/js/jpegcam/exists.php?file=" + msg
                }).success(function(res) {
                        if(res == 1)
                        {
                            var reg = /^photos\/tmp\/[0-9a-zA-Z]{13}[-][0-9]*.jpg$/;
                            if( reg.test( msg ))
                            {
                                $('.input_image_upload').attr('src', '<?php echo base_url(); ?>assets/images/' + msg);
                                $('#photo_webcam').val(msg);
                                $('#doAjout').removeAttr('disabled');
                            }
                            clearInterval(myInterval);
                            $('#waitOverlay').hide();
                        }
                    });

                tries--;
                if(tries == 0)
                {
                    clearInterval(myInterval);
                    $('#waitOverlay').hide();
                    alert('Problème durant le transfert :)');
                }

            }, 1000
        );

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

<div id="waitOverlay">
    <div class="box">
        <span>Transfert en cours</span>
        <?php echo img('wait.gif'); ?>
    </div>
</div>

<h1>Accréditations</h1>

<div class="wrap">

    <div class="tabs">
		<a href="<?php echo site_url('accreditation/index'); ?>" >Liste</a>
		<a href="<?php echo site_url('accreditation/rechercher'); ?>" class="current" >Individuelles</a>
		<a href="<?php echo site_url('accreditation/ajouterGroupe'); ?>">Groupe</a>
    </div>

    <div class="box-full">

        <aside>
			<a href="<?php echo site_url('accreditation/index'); ?>">Retour</a>
        </aside>
		
		<div id="main" class="accred">
        	
			<div class="client nouveau">
				
				<form class="infos nouveau" method="post" action="<?php echo site_url('accreditation/exeAjouter'); ?>" enctype="multipart/form-data">
					
					<input type="hidden" name="evenement" value="<?php echo $this->session->userdata('idEvenementEnCours'); ?>"/>

					<input type="file" name="photo_file" id="photo_file" accept="image/jpeg" />
					<input type="hidden" name="photo_webcam" id="photo_webcam" />
				
					<div class="photo">

						<div style="float: left">

							<div class="simulPhoto" id="simulPhoto">
						
								<div class="webcamWrapper">
									<a href="#" class="closeCam">x</a>
									<br/>
									<div class="webcam"></div>
									<br/>
									<a href="#" class="captureCam">Prendre une photo</a>
								</div>

								<div class="photoMessage"></div>

								<img class="input_image_upload" src="" />

							</div>

							<div class="optionPhoto visible">
								<a href="#" class="uploadFichier">FICHIER</a>
								<a href="#" class="startWebcam">WEBCAM</a>
							</div>

						</div>
					</div>

						<div class="inputs">

							<h2>Personne</h2>

							<div>
								<label>Nom : </label>
								<input type="text" 
									name="nom" 
									class="nom" 
									value="<?php if(isset($re->client['nom'])) echo $re->client['nom']; else echo $nom; ?>" 
									style="text-transform: uppercase" 
								/>
								<?php if(isset($re->erreurNom)) echo '<label>-</label><span class="erreurMessage" >* ' . $re->erreurNom . '</span>'; ?>
							</div>

							<div>
								<label>Prénom : </label>
								<input type="text" 
									name="prenom" 
									class="prenom" 
									value="<?php if(isset($re->client['prenom'])) echo $re->client['prenom']; else echo $prenom; ?>" 
								/>
								<?php if(isset($re->erreurPrenom)) echo '<label>-</label><span class="erreurMessage" >* ' . $re->erreurPrenom . '</span>'; ?>
							</div>

							<div>
								<label>Pays : 
									<?php foreach($pays as $p): ?>
										<span id="<?php echo $p->idpays; ?>" class="drapeau" style="display:none;" ><?php echo img('drapeaux/' . strtolower($p->idpays) . '.gif'); ?></span>
									<?php endforeach; ?>
								</label>
								<select class="pays" name="pays" style="padding-left: 0px;">

								<?php foreach($pays as $p): ?>
									<option value="<?php echo $p->idpays; ?>" <?php echo ($p->idpays == 'FRA')? 'selected' : '' ;?> >
										<?php echo $p->nompays; ?>
									</option>
								<?php endforeach; ?>

								</select>
							</div>

							<div>
								<label>Organisme : </label>
								<input type="text" name="organisme" value="<?php if(isset($re->accred['organisme'])) echo $re->accred['organisme']; ?>" />
							</div>

							<div>
								<label>Mail : </label>
								<input type="text" name="mail" class="email" value="<?php if(isset($re->client['mail'])) echo $re->client['mail']; ?>" >
								<?php if(isset($re->erreurMail)) echo '<label>-</label><span class="erreurMessage" >* ' . $re->erreurMail . '</span>'; ?>
							</div>

							<div>
								<label>Tel : </label>
								<input type="text" name="tel" class="tel" value="<?php if(isset($re->client['tel'])) echo $re->client['tel']; ?>" >
							</div>
							
						
							<h2>Accréditation</h2>

							<div>
								<label>Fonction : </label>
								<input type="text" name="fonction" value="<?php if(isset($re->accred['fonction'])) echo $re->accred['fonction']; ?>" />
							</div>

							<div>
								<label>Catégorie : </label>
								<select id="categorieSimple" name="categorie">
									<option value="" <?php if(empty($re->accred['categorie'])) echo 'selected'; ?> >---</option>
									<?php foreach($categories as $cate): ?>
									<option
										value="<?php echo $cate['cat']['db']->idcategorie; ?>"
										zone="<?php echo $cate['zones']; ?>"
										<?php if(isset($re->accred['idcategorie']) && $re->accred['idcategorie'] == $cate['cat']['db']->idcategorie) echo 'selected'; ?>
									>
										<?php for($i=0; $i<$cate['cat']['depth']; $i++) echo '&#160;&#160;'; ?>
										<?php echo $cate['cat']['db']->libellecategorie; ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="contientZones">
								<label>Zones : </label>
								<div>
									<?php foreach($zones as $zone): ?>
									<div class="checkzone <?php if(isset($re->zones[$zone->idzone])) echo 'on'; ?>" id="<?php echo $zone->idzone; ?>">
										<?php echo $zone->codezone; ?>
										<input type="checkbox" 
											name="zone[<?php echo $zone->idzone; ?>]"
											value="<?php echo $zone->idzone; ?>"
											<?php if(isset($re->zones[$zone->idzone])) echo 'checked'; ?>
										/>
									</div>
									<?php endforeach; ?>
								</div>
							</div>

							<div class="allaccess" >
								<label>All-Access : </label>
								<input type="checkbox" id="all" name="allAccess" value="1" <?php if(isset($re->accred['allaccess']) && $re->accred['allaccess'] == 1) echo 'checked'; ?> />
							</div>


							<div class="clear"></div>
						
						</div>

						<input type="submit" class="button" id="doAjout" value="Enregistrer" />

					</form>

			</div>
			
        </div>

        <div class="clear"></div>

    </div>

</div>