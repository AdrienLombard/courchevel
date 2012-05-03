<h1>Accréditations</h1>

<div class="wrap">

    <div class="tabs">
		<a href="<?php echo site_url('accreditation/index'); ?>" >Liste</a>
		<a href="<?php echo site_url('accreditation/rechercher'); ?>" >Ajouter individuel</a>
		<a href="<?php echo site_url('accreditation/ajouterGroupe'); ?>" >Ajouter groupe</a>
		<a href="#" class="current">Personne</a>
    </div>

    <div class="box-full">

        <aside>
			<a href="<?php echo site_url('accreditation/index/'); ?>">Retour</a>
			<br>
            <a href="#" class="editClient">Modifier la personne</a>
			<a href="<?php echo site_url('accreditation/nouvelle/'.$client->idclient); ?>">Nouvelle accréditation</a>

        </aside>
		
		<div id="main" class="accred">
        	
			<div class="client">
				
				<form class="infos" method="post" action="<?php echo site_url('accreditation/exeModifierClient'); ?>" enctype="multipart/form-data">
					
				<input type="file" name="photo_file" id="photo_file" accept="image/jpeg" />
				<input type="hidden" name="photo_webcam" id="photo_webcam" />
				
				<div class="photo">

					<div class="simulPhoto">
						
						<div class="webcamWrapper">
							<a href="#" class="closeCam">x</a>
							<span>Placer votre visage au centre de l'image :</span>
							<div class="webcam"></div>
							<a href="#" class="captureCam">Prendre une photo</a>
						</div>
						
						<canvas id="canvas" width="160" height="204"></canvas> 
						
						<div class="photoMessage"></div>
						
						<?php if(img_url('photos/'.$client->idclient.'.jpg') != NULL): ?>
							<img src="<?php echo site_url('image/generate/' . $client->idclient); ?>" />
						<?php endif; ?>
						
					</div>
					
					<div class="clear"></div>
					
					<div class="optionPhoto">
						<a href="#" class="uploadFichier">FICHIER</a>
						<a href="#" class="startWebcam">WEBCAM</a>
					</div>
										
				</div>
					
					<input type="hidden" name="id" value="<?php echo $client->idclient; ?>" />
					
					<div>
						<input type="text" name="nom" style="text-transform: uppercase" class="nom" init="<?php echo $client->nom; ?>" value="<?php echo $client->nom; ?>" readonly>
					</div>
					
					<div>
						<input type="text" name="prenom" class="prenom" init="<?php echo $client->prenom; ?>" value="<?php echo $client->prenom; ?>" readonly>
					</div>
					
					<br>

					<div>
						<label class="short">Pays : 
							<?php foreach($pays as $p): ?>
								<span id="<?php echo $p->idpays; ?>" class="drapeau" style="display:none;" ><?php echo img('drapeaux/' . strtolower($p->idpays) . '.gif'); ?></span>
							<?php endforeach; ?>
						</label>
						<select class="pays" name="pays" init="<?php echo $client->pays; ?>" style="padding-left: 0px;" disabled="disabled">
						
						<?php foreach($pays as $p): ?>
							<option value="<?php echo $p->idpays; ?>" <?php echo ($p->idpays == $client->pays)? 'selected' : '' ?>><?php echo $p->nompays; ?></option>
						<?php endforeach; ?>

						</select>
					</div>
					
					<div>
						<label class="short">Tel : </label>
						<?php echo '+'.$indicatif; ?><input type="text" name="tel" class="tel" init="<?php echo $client->tel; ?>" value="<?php echo $client->tel; ?>" readonly>
					</div>
					
					<div>
						<label class="short">Mail : </label>
						<input type="text" name="mail" class="email" init="<?php echo $client->mail; ?>" value="<?php echo $client->mail; ?>" readonly>
					</div>
					
					<div>
						<label class="shortOrganisme">Organisme :</label>
						<input type="text" name="organisme" class="societe" init="<?php echo $client->organisme; ?>" value="<?php echo $client->organisme; ?>" readonly>
					</div>
					
					<!-- champ pour l'adresse du client -->
					<?php if(isset($client->adresse) && !empty($client->adresse)): ?>
					<br/>
					<div>
						<label class="shortAdresse">Adresse : </label>
						<textarea readonly name="adresse" cols="45" rows="3"><?php if(isset($client->adresse)) echo $client->adresse; ?></textarea>
					</div>
					<?php endif; ?>
				
					<input type="submit" class="valideInfos" value="Enregistrer les modifications" />
				</form>
				
				<div class="clear"></div>
				
			</div>
			
			<div class="listeAccred">
				
				<h3>Accréditation en cours</h3>
				
				<?php if(count($accredAttente)==0) echo '<br/>Aucune demande en cours.' ?>
				
				<?php foreach($accredAttente as $demande): ?>
				
				<div class="ligneAccred close">
					
					<a href="<?php echo site_url('accreditation/modifier/'.$demande['accred']->idaccreditation); ?>">
						<div class="fixe">
							<span class="date"><?php echo display_date($demande['accred']->dateaccreditation); ?></span>
							<span class="categorie">- <?php echo $demande['accred']->libellecategorie; ?></span>
							<span class="evenement">- <?php echo $demande['accred']->libelleevenement; ?></span>
							<span class="etat">- <?php if($demande['accred']->etataccreditation == 0) echo 'Val';
														else echo 'Dem'?></span>
						</div>
					</a>
					
				</div>
				
				<?php endforeach; ?>
				
				
				<h3>Historique des accréditations</h3>
				
				<?php if(count($accredValide)==0) echo '<br/>Aucune accréditation.' ?>
				
				<?php foreach($accredValide as $accred): ?>
					<div class="ligneAccred close">
						
						<div class="fixe">
							<span class="date"><?php echo display_date($accred['accred']->dateaccreditation); ?></span>
							<span class="categorie"><?php echo $accred['accred']->libellecategorie; ?></span>
							<span class="evenement"><?php echo $accred['accred']->libelleevenement; ?></span>
						</div>
						
						<div class="detailZones">
							Zones :
							
							<?php foreach($accred['zones'] as $z): ?>
								<?php echo $z->codezone; ?> 
							<?php endforeach; ?>
							
						</div>
					</div>
				<?php endforeach; ?>
				
			</div>
			
        </div>

        <div class="clear"></div>

    </div>

</div>