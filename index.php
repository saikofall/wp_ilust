<?php
/**
 * テーマのメインテンプレートファイル
 *
 * @package My_Theme
 */

get_header(); ?>

<div class="container">
  <div class="site-content">
    <main id="main" class="content-area-full">
      <?php
      if ( have_posts() ) :
        ?>
        <div class="artwork-grid">
          <?php
          while ( have_posts() ) :
            the_post();
            
            // カスタムフィールドから画像を取得
            $artwork_images = get_post_meta( get_the_ID(), '_artwork_images', true );
            $artwork_type = get_post_meta( get_the_ID(), '_artwork_type', true );
            $thumbnail_image_id = get_post_meta( get_the_ID(), '_thumbnail_image_id', true );
            
            // サムネイル画像を取得（選択されているものを優先、なければ最初の画像）
            $thumbnail_id = $thumbnail_image_id;
            if ( ! $thumbnail_id && ! empty( $artwork_images ) && is_array( $artwork_images ) ) {
              $thumbnail_id = $artwork_images[0];
            }
            
            $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'medium' ) : '';
            ?>
            <article class="artwork-item">
              <a href="<?php the_permalink(); ?>" class="artwork-link">
                <?php if ( $thumbnail_url ) : ?>
                  <div class="artwork-thumbnail">
                    <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                  </div>
                <?php else : ?>
                  <div class="artwork-thumbnail no-image">
                    <span>No Image</span>
                  </div>
                <?php endif; ?>
                
                <div class="artwork-info">
                  <h2 class="artwork-title"><?php the_title(); ?></h2>
                  <?php if ( $artwork_type ) : ?>
                    <span class="artwork-type-badge <?php echo esc_attr( $artwork_type ); ?>">
                      <?php echo $artwork_type === 'manga' ? '漫画' : 'イラスト'; ?>
                    </span>
                  <?php endif; ?>
                  <?php
                  $categories = get_the_category();
                  $filtered_categories = array();
                  if ( $categories ) {
                    foreach ( $categories as $category ) {
                      if ( $category->slug !== 'uncategorized' ) {
                        $filtered_categories[] = $category;
                      }
                    }
                  }
                  if ( ! empty( $filtered_categories ) ) :
                  ?>
                    <div class="artwork-categories">
                      <?php
                      $cat_links = array();
                      foreach ( $filtered_categories as $category ) {
                        $cat_links[] = '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a>';
                      }
                      echo implode( ', ', $cat_links );
                      ?>
                    </div>
                  <?php endif; ?>
                </div>
              </a>
            </article>
            <?php
          endwhile;
          ?>
        </div>
        
        <?php
        // ページネーション
        the_posts_pagination( array(
          'mid_size'  => 2,
          'prev_text' => __( '← 前へ', 'my-theme' ),
          'next_text' => __( '次へ →', 'my-theme' ),
        ) );

      else :
        ?>
        <div class="no-results">
          <h1><?php esc_html_e( '投稿が見つかりませんでした', 'my-theme' ); ?></h1>
          <p><?php esc_html_e( 'まだ作品が投稿されていません。', 'my-theme' ); ?></p>
        </div>
        <?php
      endif;
      ?>
    </main>
  </div>
</div>

<?php
get_footer();
