  <footer id="colophon" class="site-footer">
    <div class="container">
      <?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
        <div class="footer-widgets">
          <div class="footer-widget-area">
            <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
              <div class="footer-widget">
                <?php dynamic_sidebar( 'footer-1' ); ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="footer-widget-area">
            <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
              <div class="footer-widget">
                <?php dynamic_sidebar( 'footer-2' ); ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="footer-widget-area">
            <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
              <div class="footer-widget">
                <?php dynamic_sidebar( 'footer-3' ); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="site-info">
        <p>
          <?php
          $copyright_text = get_option( 'my_theme_copyright_text', '' );
          if ( ! empty( $copyright_text ) ) {
            echo wp_kses_post( $copyright_text );
          } else {
            $display_name = get_option( 'my_theme_display_name', get_bloginfo( 'name' ) );
            echo '&copy; ' . date( 'Y' ) . ' ' . esc_html( $display_name );
          }
          ?>
        </p>
      </div>
    </div>
  </footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
