<div class="push-monkey push-monkey-bootstrap">
  <div class="container-fluid">
    <?php if ( ! $signed_in ) { ?>

    <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/log-in.php' ); ?>
    <?php } else { ?>

    <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/header.php' ); ?>


    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Push Monkey Light WooCommerce', 'push-monkey-light-woocommerce' ); ?></h3>
      </div>
      <div class="panel-body">
        <?php if ( ! $woocommerce_is_active ) { ?>

        <h3><?php _e( 'Did you know Push Monkey Light works seamlessly with WooCommerce?', 'push-monkey-light-woocommerce' ); ?></h3>
        <p>
          <?php _e( 'The', 'push-monkey-light-woocommerce' ); ?> <strong><?php _e( 'Abandoned Cart', 'push-monkey-light-woocommerce' ); ?></strong> <?php _e( 'feature reminds your visitor
          about shopping carts that they did not check out.', 'push-monkey-light-woocommerce' ); ?>
        </p>
        <p>
          <?php _e( 'Install and activate WooCommerce to take full advantage of this feature.', 'push-monkey-light-woocommerce' ); ?>
        </p>
        <?php } else { ?>

        <h3><?php _e( 'The Abandoned Shopping Cart feature is now active.', 'push-monkey-light-woocommerce' ); ?></h3>
        <p>
          <?php _e( 'This will remind your visitors if they did not check out their shopping cart.', 'push-monkey-light-woocommerce' ); ?>
        </p>     
        <?php }?>
      </div>
    </div>
    <?php if ( $woocommerce_is_active ) { ?>
    <form class="push_monkey_woo_settings" name="push_monkey_woo_settings" enctype="multipart/form-data" method="post" class="form-horizontal">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h3 class="panel-title"><?php _e( 'Abandoned Cart Options', 'push-monkey-light-woocommerce' ); ?></h3>
        </div>
        <div class="panel-body">

          <div class="form-group clearfix">
            <label class="col-md-3 control-label">
              <?php _e( 'Enable Abandoned Cart Notifications', 'push-monkey-light-woocommerce' ); ?>
            </label>
            <div class="col-md-3">
                <label class="switch">
                    <input type="checkbox" class="switch" name="pm_light_woocommerce_woo_enabled" <?php if ( $woo_enabled ) {?> checked="true" <?php } ?>
                    >
                    <span></span>
                </label>
                <span class="help-block"><?php _e( 'By default, this is enabled.', 'push-monkey-light-woocommerce' ); ?></span>
            </div>
          </div>

          <div class="form-group clearfix">
            <label class="col-md-3 col-xs-12 control-label" for="push-monkey-abandoned-delay">
              <?php _e( 'Abandoned Cart Delay', 'push-monkey-light-woocommerce' ); ?>
            </label>
            <div class="col-md-4 col-xs-12">
              <input type="text" value="<?php echo $woo_settings['abandoned_cart_delay']; ?>" name="abandoned_cart_delay" id="push-monkey-abandoned-delay" class="form-control"/>
              <span class="help-block">
                <?php _e( 'The number of', 'push-monkey-light-woocommerce' ); ?> <strong><?php _e( 'minutes', 'push-monkey-light-woocommerce' ); ?></strong> <?php _e( 'after which the abandoned cart reminder push notification is sent.', 'push-monkey-light-woocommerce' ); ?>
              </span>
            </div>
          </div>

          <div class="form-group clearfix">
            <label class="col-md-3 col-xs-12 control-label" for="push-monkey-abandoned-title">
              <?php _e( 'Abandoned Cart Title', 'push-monkey-light-woocommerce' ); ?>
            </label>
            <div class="col-md-6 col-xs-12">
              <input type="text" value="<?php echo esc_html( $woo_settings['abandoned_cart_title'] ); ?>" name="abandoned_cart_title" id="push-monkey-abandoned-title" class="form-control" maxlength="30"/>
              <span class="help-block">
                <?php _e( 'The title of the abandoned cart reminder push notifications.', 'push-monkey-light-woocommerce' ); ?>
              </span>
            </div>
          </div>

          <div class="form-group clearfix">
            <label class="col-md-3 col-xs-12 control-label" for="push-monkey-abandoned-message">
              <?php _e( 'Abandoned Cart Message', 'push-monkey-light-woocommerce' ); ?>
            </label>
            <div class="col-md-6 col-xs-12">
              <textarea name="abandoned_cart_message" id="push-monkey-abandoned-message" class="form-control" rows="3" maxlength="120"><?php echo esc_html( $woo_settings['abandoned_cart_message'] ); ?></textarea>
              <span class="help-block">
                <?php _e( 'The message of the abandoned cart reminder push notifications.', 'push-monkey-light-woocommerce' ); ?>
              </span>
            </div>
          </div>

          <div class="form-group clearfix">
            <label class="col-md-3 col-xs-12 control-label" for="push-monkey-abandoned-image">
              <?php _e( 'Abandoned Cart Image', 'push-monkey-light-woocommerce' ); ?>
            </label>
            <div class="col-md-6 col-xs-12">
              <input type="file" class="fileinput btn-primary"  value="24" name="abandoned_cart_image" id="push-monkey-abandoned-image"/>
              <span class="help-block">
                <?php _e( 'The image of the abandoned cart reminder push notifications. Recommended
                size 675px x 506px.', 'push-monkey-light-woocommerce' ); ?> 
              </span>
              <?php if ( $woo_settings['abandoned_cart_image'] ) {?>
                <br />
                <p><?php _e( 'Your current image:', 'push-monkey-light-woocommerce' ); ?></p>
                <img style="width: 337px; height: 253px" src="<?php echo esc_url( 'https://getpushmonkey.com/' . $woo_settings['abandoned_cart_image'] ); ?>" />
              <?php } ?>
            </div>
          </div>                            

        </div>

        <div class="panel-footer">
          <button type="submit" name="pm_light_woocommerce_woo_settings" class="btn btn-primary pull-right">Save</button>
        </div>      

      </div>
    </form>
    <?php }?>
    <?php require_once( plugin_dir_path( __FILE__ ) . '/parts/footer.php' ); ?>
    <?php } ?>

  </div>
</div>