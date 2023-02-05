<?php
/**
 * Title: Footer Credits
 * Slug: wecodeart/footer-credits
 * Categories: wecodeart
 * Inserter: false
 */
?>
<!-- wp:group {"align":"full","textColor":"dark","className":"py-3","layout":{"inherit":true,"type":"constrained"},"className":"py-3"} -->
<div class="wp-block-group alignfull py-3 has-dark-color has-text-color">
	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center"><?php
	
	echo wp_kses_post( 
		sprintf(
			__( 'Copyright %s - All rights reserved. Built on %s.', 'wecodeart' ),
			'[copy] [year]',
			'[theme]'
		)
	);

	?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->