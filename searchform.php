<?php
/**
 * 検索フォーム用テンプレート
 *
 * @package My_Theme
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
  <label>
    <span class="screen-reader-text"><?php echo _x( '検索:', 'label', 'my-theme' ); ?></span>
    <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'キーワードを入力...', 'placeholder', 'my-theme' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
  </label>
  <button type="submit" class="search-submit">
    <?php echo esc_html_x( '検索', 'submit button', 'my-theme' ); ?>
  </button>
</form>
