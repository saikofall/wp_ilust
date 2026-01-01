<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
  <a class="skip-link screen-reader-text" href="#main">
    <?php esc_html_e( 'コンテンツへスキップ', 'my-theme' ); ?>
  </a>

  <?php
  $hide_header_on_single = get_option( 'my_theme_hide_header_on_single', 0 );
  $should_hide_header = is_single() && $hide_header_on_single;
  
  if ( ! $should_hide_header ) :
    $header_image = get_option( 'my_theme_header_image', 0 );
  ?>
  <header id="masthead" class="site-header<?php echo $header_image ? ' has-header-image' : ''; ?>">
    <div class="site-header-container">
      <div class="site-branding">
        <?php
        $user_icon = get_option( 'my_theme_user_icon', 0 );
        $display_name = get_option( 'my_theme_display_name', get_bloginfo( 'name' ) );
        $sns_items = get_option( 'my_theme_sns_items', array() );
        ?>
        
        <div class="user-header">
          <?php if ( $user_icon ) : ?>
            <div class="user-icon">
              <img src="<?php echo esc_url( wp_get_attachment_image_url( $user_icon, 'thumbnail' ) ); ?>" alt="<?php echo esc_attr( $display_name ); ?>">
            </div>
          <?php endif; ?>
          
          <div class="user-info">
            <h1 class="site-title">
              <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                <?php echo esc_html( $display_name ); ?>
              </a>
            </h1>
            
            <?php if ( ! empty( $sns_items ) ) : ?>
              <div class="sns-links">
                <?php foreach ( $sns_items as $sns ) : 
                  $image_id = isset( $sns['image_id'] ) ? $sns['image_id'] : 0;
                ?>
                  <a href="<?php echo esc_url( $sns['url'] ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $sns['name'] ); ?>">
                    <?php if ( $image_id ) : ?>
                      <img src="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'thumbnail' ) ); ?>" alt="<?php echo esc_attr( $sns['name'] ); ?>" class="sns-icon-image">
                    <?php else : ?>
                      <span class="sns-icon-default">#</span>
                    <?php endif; ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <nav id="site-navigation" class="main-navigation">
        <?php
        wp_nav_menu( array(
          'theme_location' => 'primary',
          'menu_id'        => 'primary-menu',
          'container'      => false,
          'fallback_cb'    => false,
        ) );
        ?>
      </nav>
    </div>
  </header>
  <?php endif; ?>
