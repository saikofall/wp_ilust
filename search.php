<?php
/**
 * 検索結果ページ用テンプレート
 *
 * @package My_Theme
 */

get_header(); ?>

<div class="container">
  <div class="site-content">
    <main id="main" class="content-area">
      <div class="site-main">
        <header class="page-header">
          <h1 class="page-title">
            <?php
            printf(
              esc_html__( '検索結果: %s', 'my-theme' ),
              '<span>' . get_search_query() . '</span>'
            );
            ?>
          </h1>
        </header>

        <?php
        if ( have_posts() ) :
          while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
              <header class="entry-header">
                <h2 class="entry-title">
                  <a href="<?php the_permalink(); ?>">
                    <?php the_title(); ?>
                  </a>
                </h2>
                <div class="entry-meta">
                  <span class="posted-on">
                    <?php echo esc_html( get_the_date() ); ?>
                  </span>
                </div>
              </header>

              <div class="entry-summary">
                <?php the_excerpt(); ?>
              </div>

              <footer class="entry-footer">
                <a href="<?php the_permalink(); ?>" class="read-more">
                  続きを読む →
                </a>
              </footer>
            </article>
            <?php
          endwhile;

          the_posts_pagination();

        else :
          ?>
          <article class="no-results">
            <header class="entry-header">
              <h2 class="entry-title">
                <?php esc_html_e( '検索結果が見つかりませんでした', 'my-theme' ); ?>
              </h2>
            </header>
            <div class="entry-content">
              <p>
                <?php esc_html_e( '別のキーワードで検索してみてください。', 'my-theme' ); ?>
              </p>
              <?php get_search_form(); ?>
            </div>
          </article>
          <?php
        endif;
        ?>
      </div>
    </main>

    <?php get_sidebar(); ?>
  </div>
</div>

<?php
get_footer();
