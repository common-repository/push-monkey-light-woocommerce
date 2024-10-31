<div class="row" id="review-notice">
	<div class="col-md-4 text-center" >
		<img src="<?php echo esc_url( $icon_src ); ?>" title="Review Notice Icon" alt="Review Notice Icon">	
	</div>
	<div class="col-md-8 col">
		<p><?php _e( 'You\'ve been using Push Monkey for quite some time now. If you love the experience, we\'d greatly appreciate if you could
			leave us a review or rating. ', 'push-monkey-light-woocommerce' ); ?></p>
		<p>
			<a class="btn btn-default" href="<?php echo $query_string; ?>"><?php _e( 'No thanks.', 'push-monkey-light-woocommerce' ); ?></a>			
			<a class="btn btn-success pull-right" href="<?php echo esc_url(  $review_url ); ?>"><?php _e( 'Yes, show me how!', 'push-monkey-light-woocommerce' ); ?></a>
			<br /><br />
		</p>
	</div>
</div>