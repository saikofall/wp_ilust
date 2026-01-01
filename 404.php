<?php
/**
 * 404エラーページ用テンプレート
 *
 * @package My_Theme
 */

get_header(); ?>

<div class="container">
  <div class="site-content">
    <main id="main" class="content-area">
      <div class="site-main">
        <article class="error-404 not-found">
          <header class="entry-header">
            <h1 class="entry-title">
              <?php esc_html_e( '404 - ページが見つかりません', 'my-theme' ); ?>
            </h1>
          </header>

          <div class="entry-content">
            <p>
              <?php esc_html_e( '申し訳ありませんが、お探しのページは見つかりませんでした。', 'my-theme' ); ?>
            </p>
            <p>
              <?php esc_html_e( '削除されたか、URLが変更された可能性があります。', 'my-theme' ); ?>
            </p>

            <?php get_search_form(); ?>

            <h2><?php esc_html_e( '最近の投稿', 'my-theme' ); ?></h2>
            <ul>
              <?php
              wp_list_pages( array(
                'title_li' => '',
                'number'   => 5,
              ) );
              ?>
            </ul>

            <h2><?php esc_html_e( 'カテゴリー', 'my-theme' ); ?></h2>
            <ul>
              <?php
              wp_list_categories( array(
                'title_li' => '',
                'number'   => 10,
              ) );
              ?>
            </ul>
          </div>
        </article>
      </div>
    </main>

    <?php get_sidebar(); ?>
  </div>
</div>

<?php
get_footer();
