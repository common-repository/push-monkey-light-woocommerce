<div class="error push-monkey-us-notice push-monkey-bootstrap" style="background-image:url('<?php echo esc_url( $image_url ); ?>') !important;"> 
	<div class="button-wrapper">
		<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" class="btn btn-success btn-lg"><?php _e( 'Upgrade Now', 'push-monkey-light-woocommerce' ); ?></a>
	</div>

	<div class="text-wrapper">
		<h4><?php _e( 'Congrats! Your website is popular. Continue using Push Monkey in the best way.', 'push-monkey-light-woocommerce' ); ?></h4>
		<p><?php _e( 'It\'s time to upgrade your Push Monkey to a higher plan. See our', 'push-monkey-light-woocommerce' );?> <a target="_blank" href="<?php echo $price_plans; ?>"><?php _e( 'other great price plans', 'push-monkey-light-woocommerce' ); ?></a>.  
		<a href="http://blog.getpushmonkey.com/2014/12/readers-will-ready-desktop-push-notifications/?source=plugin-upsell" target="_blank"><?php _e( 'More info', 'push-monkey-light-woocommerce' ); ?> &#8594;</a>
		</p>
	</div>

	<div class="close-btn">
			<a href="javascript:void(0);"><img src="<?php echo esc_url( $close_url ); ?>" /></a>
	</div>
</div>