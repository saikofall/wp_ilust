<?php
/**
 * 単一投稿用テンプレート
 *
 * @package My_Theme
 */

get_header();
?>

<div class="container">
  <div class="site-content">
    <main id="main" class="content-area-single">
      <?php
      while ( have_posts() ) :
        the_post();
        
        // カスタムフィールドから画像を取得
        $artwork_images = get_post_meta( get_the_ID(), '_artwork_images', true );
        $artwork_type = get_post_meta( get_the_ID(), '_artwork_type', true );
        $artwork_description = get_post_meta( get_the_ID(), '_artwork_description', true );
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'artwork-single' ); ?>>

          <div class="artwork-images">
            <?php
            if ( ! empty( $artwork_images ) && is_array( $artwork_images ) ) :
              foreach ( $artwork_images as $image_id ) :
                $image_url = wp_get_attachment_image_url( $image_id, 'large' );
                if ( $image_url ) :
                  ?>
                  <div class="artwork-image-wrapper">
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                  </div>
                  <?php
                endif;
              endforeach;
            endif;
            ?>
          </div>

          <div class="single-footer">
            <header class="artwork-header">
              <h1 class="artwork-title-single"><?php the_title(); ?></h1>
            </header>
            
            <?php if ( ! empty( $artwork_description ) ) : ?>
              <div class="artwork-description">
                <?php echo wpautop( wp_kses_post( $artwork_description ) ); ?>
              </div>
            <?php endif; ?>
            
            <div class="artwork-meta">
              <?php echo esc_html( get_the_date() ); ?>
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
                $cat_names = array();
                foreach ( $filtered_categories as $category ) {
                  $cat_names[] = '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" rel="category tag">' . esc_html( $category->name ) . '</a>';
                }
                echo ' ' . implode( ', ', $cat_names );
              endif;
              
              if ( $artwork_type ) :
                echo ' <span class="artwork-type-badge ' . esc_attr( $artwork_type ) . '">';
                echo $artwork_type === 'manga' ? '漫画' : 'イラスト';
                echo '</span>';
              endif;
              ?>
            </div>
          </div>

          <nav class="artwork-navigation">
            <?php
            // 過去画像一覧の表示設定を取得
            $past_images_display = get_option( 'my_theme_past_images_display', 'all' );
            $current_post_id = get_the_ID();
            $current_artwork_type = get_post_meta( $current_post_id, '_artwork_type', true );
            
            // クエリ引数の設定
            $query_args = array(
              'post_type' => 'post',
              'posts_per_page' => 5,
              'post__not_in' => array( $current_post_id ),
              'orderby' => 'date',
              'order' => 'DESC',
            );
            
            // 表示範囲に応じてクエリを調整
            if ( $past_images_display === 'same_type' && $current_artwork_type ) {
              $query_args['meta_query'] = array(
                array(
                  'key' => '_artwork_type',
                  'value' => $current_artwork_type,
                  'compare' => '=',
                ),
              );
            } elseif ( $past_images_display === 'same_category' ) {
              $categories = wp_get_post_categories( $current_post_id );
              if ( ! empty( $categories ) ) {
                $query_args['category__in'] = $categories;
              }
            }
            
            $past_images_query = new WP_Query( $query_args );
            
            // 過去画像一覧を表示（1件以上ある場合のみ）
            if ( $past_images_query->have_posts() ) :
              ?>
              <div class="past-images-section">
                <div class="past-images-grid">
                  <?php
                  while ( $past_images_query->have_posts() ) :
                    $past_images_query->the_post();
                    $thumbnail_id = get_post_meta( get_the_ID(), '_thumbnail_image_id', true );
                    
                    if ( ! $thumbnail_id ) {
                      $artwork_images = get_post_meta( get_the_ID(), '_artwork_images', true );
                      if ( ! empty( $artwork_images ) && is_array( $artwork_images ) ) {
                        $thumbnail_id = $artwork_images[0];
                      }
                    }
                    ?>
                    <a href="<?php the_permalink(); ?>" class="past-image-item">
                      <?php if ( $thumbnail_id ) : ?>
                        <div class="past-image-thumbnail">
                          <?php echo wp_get_attachment_image( $thumbnail_id, 'medium' ); ?>
                        </div>
                      <?php endif; ?>
                    </a>
                    <?php
                  endwhile;
                  wp_reset_postdata();
                  ?>
                </div>
              </div>
              <?php
            endif;
            ?>
            
            <div class="nav-links">
              <div class="nav-home">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                  一覧へ戻る
                </a>
              </div>
            </div>
          </nav>
        </article>
        <?php
      endwhile;
      ?>
    </main>
  </div>
</div>

<?php
get_footer();
