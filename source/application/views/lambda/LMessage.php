<div class="wrap">
	<h1><?php echo $titre; ?></h1>
	
	<input id="lang" type="hidden" value="<?php //echo $lang; ?>" />
	
	<div class="box-small">
		<?php echo $message; ?>
		
		<a id="lienLambda" href="<?php echo site_url('inscription'); ?>" class="button"><?php echo lang('continuer'); ?></a>
	</div>
</div>