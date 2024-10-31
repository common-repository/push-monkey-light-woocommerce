<div class="push-monkey-bootstrap">
	<div class="error clearfix push-monkey-expired-notice" style="background-image:url('<?php echo esc_url(  $image_url ) ?>')"> 
		<div class="button-wrapper text-center">
			<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" class="btn btn-danger btn-lg"><?php _e( 'Upgrade Now', 'push-monkey-light-woocommerce' ); ?></a>
		</div>
		<div class="text-wrapper">
			<h4><?php _e( 'Your Trial Plan Expired', 'push-monkey-light-woocommerce' ); ?></h4>
			<p>
				<strong><?php _e( 'Sad News:', 'push-monkey-light-woocommerce' ); ?> </strong><?php _e( 'Because your trial plan is over, push notifications will 
				not be sent to any of your', 'push-monkey-light-woocommerce' ); ?> <strong><?php echo $subscribers; ?><?php _e( 'subscribers., push notifications will 
				not be sent to any of your', 'push-monkey-light-woocommerce' ); ?> </strong>. 
			</p>
			<p>
				<strong><?php _e( 'Good News:', 'push-monkey-light-woocommerce' ); ?> </strong><?php _e( 'You can upgrade your account by', 'push-monkey-light-woocommerce' ); ?> <a href="<?php echo esc_url( $upgradegrade_url ); ?>" target="_blank"><?php _e( 'clicking here', 'push-monkey-light-woocommerce' ); ?></a><?php _e( '. If you\'re still not sure, ', 'push-monkey-light-woocommerce' ); ?>
				<a href="<?php echo esc_url( 'http://blog.getpushmonkey.com/2014/10/why-safari-desktop-push-notifications-matter/?source=big_expiration_notice' ); ?>">
				<?php _e( 'this article might help ', 'push-monkey-light-woocommerce' ); ?>&#8594;</a>.
			</p>
		</div>
	</div>
</div>
