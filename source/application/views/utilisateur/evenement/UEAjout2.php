<h1>Evènements</h1>

<div class="wrap">
	
	<div class="tabs">
        <a href="<?php echo site_url('evenement/liste'); ?>" >Liste</a>
        <a href="<?php echo site_url('evenement/ajouter'); ?>" class="current">Ajouter</a>
    </div>
	
	<div class="box-full">

		<div id="main" class="nomargin">			
			
			<table class="listeCategorieEvent">
				<thead>
					<th></th>
					<?php foreach($listeZones as $zone): ?>
					
						<th class="rotate">
							<div class="itemRotate" zone="<?php echo $zone->idzone; ?>"><?php echo $zone->libellezone; ?></div>
						</th>
						
					<?php endforeach; ?>

						<th class="rotate">
							<div class="itemRotate"></div>
						</th>
				
				</thead>
				
				<tbody>
					
					<tr class="ligneCodeZone">
						<td class="titreCodeDeLaZone">code de la zone :</td>
						<?php foreach($listeZones as $zone): ?>
							<td>
								<input type="text" maxlength="3" name="<?php echo 'code_' . $zone->idzone; ?>" zone="<?php echo $zone->idzone; ?>" class="codeZone" />
							</td>
						<?php endforeach; ?>
					</tr>
					
					<?php if(isset($listeCategorie)): ?>
						<?php foreach ($listeCategorie as $categorie): ?>
						<tr class="ligneChoixZoneCat">

							<td>
								<?php echo $categorie->libellecategorie?>
							</td>

							<?php foreach($listeZones as $zone): ?>
								
								<?php if(isset($listeCatgorieZone[$categorie->idcategorie][$zone->idzone])): ?>
									
									<td>
										<input type="checkbox"
											   name="<?php echo $categorie->idcategorie . '_' . $zone->idzone; ?>"
											   cat="<?php echo $categorie->idcategorie; ?>"
											   zone="<?php echo $zone->idzone; ?>"
											   checked="checked"/>
									</td>
								
								<?php else: ?>
								
									<td>
										<input type="checkbox"
											   name="<?php echo $categorie->idcategorie . '_' . $zone->idzone; ?>"
											   cat="<?php echo $categorie->idcategorie; ?>"
											   zone="<?php echo $zone->idzone; ?>" />
									</td>
								
								<?php endif; ?>

							<?php endforeach; ?>

						</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					
				</tbody>
			</table>
			
			
			
			
			
			
			
			
			
			
		</div>

		<div class="clear"></div>

	</div>

</div>