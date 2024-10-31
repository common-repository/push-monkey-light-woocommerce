<div class="header">
	<div class="header-row">
		<img src="<?php echo $pluginPath . 'img/logo@2x.png' ?>" alt="Push Monkey Logo" class="header-logo" />
		<div class="header-box text-right">
			<div class="header-info">
				<?php if ( $plan_name ) { ?>
				<p><?php _e( 'You\'re rocking the', 'push-monkey-light-woocommerce' ); ?> <strong><?php echo $plan_name; ?></strong> <?php _e( 'plan.', 'push-monkey-light-woocommerce' ); ?></p>
				<?php if ( $plan_can_upgrade ) { ?>
				<a class="btn btn-success" href="<?php echo $upgrade_url; ?>" target="_blank"><?php _e( 'Upgrade Now?', 'push-monkey-light-woocommerce' ); ?></a>
				<?php } ?>
				<?php } else if ( $plan_expired ) { ?>
				<p class="text-danger"><?php _e( 'Your plan expired.', 'push-monkey-light-woocommerce' ); ?></p>
				<?php if ( $plan_can_upgrade ) { ?>
				<a class="btn btn-danger" href="<?php echo $upgrade_url; ?>" target="_blank"><?php _e( 'Upgrade Now', 'push-monkey-light-woocommerce' ); ?></a>
				<?php } ?>
				<?php } ?>
				<a class="btn btn-default" href="<?php echo $logout_url; ?>"><?php _e( 'Sign Out', 'push-monkey-light-woocommerce' ); ?></a>
			</div>
		</div>
	</div>

	<?php if ( $registered ) { ?>
	<div class="header-row">
		<div class="alert alert-success" role="alert">
			<p><?php _e( 'Welcome to Push Monkey Light WooCommerce! May your push notifications be merry and your readers happy!', 'push-monkey-light-woocommerce' ); ?></p>
		</div>
	</div>
	<?php } ?>

	<?php if ( $notice ) { ?>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="panel panel-default">
					<div class="panel-body">
						<br>
						<?php $notice->pm_light_woocommerce_render(); ?>
					</div>
				</div>
			</div><!-- .col -->
		</div><!-- .row -->
	</div>
	<?php } ?>
</div>