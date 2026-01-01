<?php
/**
 * テーマの機能とフックを定義
 *
 * @package My_Theme
 */

/**
 * テーマのセットアップ
 */
function my_theme_setup() {
  // 翻訳ファイルの読み込み
  load_theme_textdomain( 'my-theme', get_template_directory() . '/languages' );

  // RSS フィードリンクを <head> に追加
  add_theme_support( 'automatic-feed-links' );

  // <title> タグの管理をWordPressに任せる
  add_theme_support( 'title-tag' );

  // アイキャッチ画像は使用しない（カスタムフィールドで管理）

  // カスタムロゴのサポート
  add_theme_support( 'custom-logo', array(
    'height'      => 100,
    'width'       => 400,
    'flex-height' => true,
    'flex-width'  => true,
  ) );

  // HTML5マークアップのサポート
  add_theme_support( 'html5', array(
    'search-form',
    'gallery',
    'caption',
    'style',
    'script',
  ) );

  // カスタム背景のサポート
  add_theme_support( 'custom-background', array(
    'default-color' => 'f5f5f5',
  ) );

  // ナビゲーションメニューの登録
  register_nav_menus( array(
    'primary' => __( 'プライマリーメニュー', 'my-theme' ),
    'footer'  => __( 'フッターメニュー', 'my-theme' ),
  ) );
}
add_action( 'after_setup_theme', 'my_theme_setup' );

/**
 * SVGアップロードを許可
 */
function my_theme_allow_svg_upload( $mimes ) {
  $mimes['svg'] = 'image/svg+xml';
  $mimes['svgz'] = 'image/svg+xml';
  return $mimes;
}
add_filter( 'upload_mimes', 'my_theme_allow_svg_upload' );

/**
 * SVGのMIMEタイプとファイル拡張子のチェックを修正
 */
function my_theme_fix_svg_mime_check( $data, $file, $filename, $mimes ) {
  $filetype = wp_check_filetype( $filename, $mimes );
  
  if ( 'svg' === $filetype['ext'] ) {
    $data['ext'] = 'svg';
    $data['type'] = 'image/svg+xml';
  }
  
  return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'my_theme_fix_svg_mime_check', 10, 4 );

/**
 * SVGファイルのサムネイル表示を修正
 */
function my_theme_fix_svg_thumb_display() {
  echo '<style>
    .attachment-266x266, .thumbnail img {
      width: 100% !important;
      height: auto !important;
    }
  </style>';
}
add_action( 'admin_head', 'my_theme_fix_svg_thumb_display' );

/**
 * コンテンツ幅の設定
 */
if ( ! isset( $content_width ) ) {
  $content_width = 1200;
}

/**
 * コメント機能を無効化
 */
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

/**
 * 管理画面メニューから不要な項目を削除
 */
function my_theme_remove_admin_menus() {
  remove_menu_page( 'edit-comments.php' );  // コメント
  remove_menu_page( 'edit.php?post_type=page' );  // 固定ページ
  remove_menu_page( 'themes.php' );  // 外観
  remove_menu_page( 'tools.php' );  // ツール
}
add_action( 'admin_menu', 'my_theme_remove_admin_menus' );

/**
 * サイト設定メニューを追加
 */
function my_theme_add_settings_menu() {
  add_menu_page(
    'サイト設定',
    'サイト設定',
    'manage_options',
    'my-theme-settings',
    'my_theme_settings_page',
    'dashicons-admin-generic',
    30
  );
}
add_action( 'admin_menu', 'my_theme_add_settings_menu' );

/**
 * サイト設定ページの表示
 */
function my_theme_settings_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  // 設定の保存
  if ( isset( $_POST['my_theme_settings_submit'] ) && check_admin_referer( 'my_theme_settings_save', 'my_theme_settings_nonce' ) ) {
    // 色設定
    update_option( 'my_theme_bg_color', sanitize_hex_color( $_POST['bg_color'] ) );
    update_option( 'my_theme_text_color', sanitize_hex_color( $_POST['text_color'] ) );
    update_option( 'my_theme_link_color', sanitize_hex_color( $_POST['link_color'] ) );
    
    // 基本設定
    update_option( 'my_theme_display_name', sanitize_text_field( $_POST['display_name'] ) );
    
    // 表示設定
    $grid_columns_pc = intval( $_POST['grid_columns_pc'] );
    if ( $grid_columns_pc < 1 ) $grid_columns_pc = 3;
    if ( $grid_columns_pc > 8 ) $grid_columns_pc = 8;
    update_option( 'my_theme_grid_columns_pc', $grid_columns_pc );
    
    $grid_columns_mobile = intval( $_POST['grid_columns_mobile'] );
    if ( $grid_columns_mobile < 1 ) $grid_columns_mobile = 2;
    if ( $grid_columns_mobile > 4 ) $grid_columns_mobile = 4;
    update_option( 'my_theme_grid_columns_mobile', $grid_columns_mobile );
    
    update_option( 'my_theme_hide_header_on_single', isset( $_POST['hide_header_on_single'] ) ? 1 : 0 );
    
    // 画像設定
    update_option( 'my_theme_user_icon', intval( $_POST['user_icon'] ) );
    update_option( 'my_theme_header_image', intval( $_POST['header_image'] ) );
    
    // SNS設定
    if ( isset( $_POST['sns_items'] ) && is_array( $_POST['sns_items'] ) ) {
      $sns_items = array();
      foreach ( $_POST['sns_items'] as $item ) {
        if ( ! empty( $item['name'] ) && ! empty( $item['url'] ) ) {
          $sns_items[] = array(
            'image_id' => intval( $item['image_id'] ),
            'name' => sanitize_text_field( $item['name'] ),
            'url' => esc_url_raw( $item['url'] ),
          );
        }
      }
      update_option( 'my_theme_sns_items', $sns_items );
    } else {
      update_option( 'my_theme_sns_items', array() );
    }
    
    update_option( 'my_theme_enable_share_links', isset( $_POST['enable_share_links'] ) ? 1 : 0 );
    
    // その他設定
    update_option( 'my_theme_ga_tracking_id', sanitize_text_field( $_POST['ga_tracking_id'] ) );
    update_option( 'my_theme_past_images_display', sanitize_text_field( $_POST['past_images_display'] ) );
    update_option( 'my_theme_copyright_text', wp_kses_post( $_POST['copyright_text'] ) );
    update_option( 'my_theme_custom_css', wp_strip_all_tags( $_POST['custom_css'] ) );
    update_option( 'my_theme_block_ai_crawlers', isset( $_POST['block_ai_crawlers'] ) ? 1 : 0 );
    
    // OGP設定
    update_option( 'my_theme_ogp_title', sanitize_text_field( $_POST['ogp_title'] ) );
    update_option( 'my_theme_ogp_description', sanitize_textarea_field( $_POST['ogp_description'] ) );
    update_option( 'my_theme_ogp_image', intval( $_POST['ogp_image'] ) );
    update_option( 'my_theme_twitter_card', sanitize_text_field( $_POST['twitter_card'] ) );
    
    echo '<div class="notice notice-success"><p>設定を保存しました。</p></div>';
  }

  // 現在の設定を取得
  $bg_color = get_option( 'my_theme_bg_color', '#f5f5f5' );
  $text_color = get_option( 'my_theme_text_color', '#333333' );
  $link_color = get_option( 'my_theme_link_color', '#0073aa' );
  $display_name = get_option( 'my_theme_display_name', get_bloginfo( 'name' ) );
  $user_icon = get_option( 'my_theme_user_icon', 0 );
  $header_image = get_option( 'my_theme_header_image', 0 );
  $sns_items = get_option( 'my_theme_sns_items', array() );
  $enable_share_links = get_option( 'my_theme_enable_share_links', 1 );
  $grid_columns_pc = get_option( 'my_theme_grid_columns_pc', 3 );
  $grid_columns_mobile = get_option( 'my_theme_grid_columns_mobile', 2 );
  $hide_header_on_single = get_option( 'my_theme_hide_header_on_single', 0 );
  $ga_tracking_id = get_option( 'my_theme_ga_tracking_id', '' );
  $past_images_display = get_option( 'my_theme_past_images_display', 'all' );
  $copyright_text = get_option( 'my_theme_copyright_text', '' );
  $custom_css_additional = get_option( 'my_theme_custom_css', '' );
  $block_ai_crawlers = get_option( 'my_theme_block_ai_crawlers', 0 );
  $ogp_title = get_option( 'my_theme_ogp_title', '' );
  $ogp_description = get_option( 'my_theme_ogp_description', '' );
  $ogp_image = get_option( 'my_theme_ogp_image', 0 );
  $twitter_card = get_option( 'my_theme_twitter_card', 'summary_large_image' );
  
  ?>
  <div class="wrap my-theme-settings">
    <h1>サイト設定</h1>
    <form method="post" action="">
      <?php wp_nonce_field( 'my_theme_settings_save', 'my_theme_settings_nonce' ); ?>
      
      <h2>色設定</h2>
      <div class="settings-section">
        <div class="settings-row">
          <label for="bg_color">背景色</label>
          <input type="text" id="bg_color" name="bg_color" value="<?php echo esc_attr( $bg_color ); ?>" class="color-picker">
        </div>
        <div class="settings-row">
          <label for="text_color">文字色</label>
          <input type="text" id="text_color" name="text_color" value="<?php echo esc_attr( $text_color ); ?>" class="color-picker">
        </div>
        <div class="settings-row">
          <label for="link_color">リンク色</label>
          <input type="text" id="link_color" name="link_color" value="<?php echo esc_attr( $link_color ); ?>" class="color-picker">
        </div>
      </div>
      
      <h2>画像設定</h2>
      <div class="settings-section">
        <div class="settings-row">
          <label>ユーザーアイコン</label>
          <div class="settings-input">
            <div class="image-preview-wrapper">
              <?php if ( $user_icon ) : ?>
                <img src="<?php echo esc_url( wp_get_attachment_image_url( $user_icon, 'thumbnail' ) ); ?>" style="max-width: 100px; display: block; margin-bottom: 10px;" id="user-icon-preview">
              <?php else : ?>
                <img src="" style="max-width: 100px; display: none; margin-bottom: 10px;" id="user-icon-preview">
              <?php endif; ?>
            </div>
            <input type="hidden" id="user_icon" name="user_icon" value="<?php echo esc_attr( $user_icon ); ?>">
            <button type="button" class="button upload-image-button" data-target="user_icon">画像を選択</button>
            <button type="button" class="button remove-image-button" data-target="user_icon">削除</button>
          </div>
        </div>
        <div class="settings-row">
          <label>ヘッダー画像</label>
          <div class="settings-input">
            <div class="image-preview-wrapper">
              <?php if ( $header_image ) : ?>
                <img src="<?php echo esc_url( wp_get_attachment_image_url( $header_image, 'medium' ) ); ?>" style="max-width: 300px; display: block; margin-bottom: 10px;" id="header-image-preview">
              <?php else : ?>
                <img src="" style="max-width: 300px; display: none; margin-bottom: 10px;" id="header-image-preview">
              <?php endif; ?>
            </div>
            <input type="hidden" id="header_image" name="header_image" value="<?php echo esc_attr( $header_image ); ?>">
            <button type="button" class="button upload-image-button" data-target="header_image">画像を選択</button>
            <button type="button" class="button remove-image-button" data-target="header_image">削除</button>
          </div>
        </div>
      </div>
      
      <h2>基本設定</h2>
      <div class="settings-section">
        <div class="settings-row">
          <label for="display_name">表示するユーザー名</label>
          <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $display_name ); ?>" class="regular-text">
        </div>
      </div>
      
      <h2>表示設定</h2>
      <div class="settings-section">
        <div class="settings-row">
          <label for="grid_columns_pc">一覧で表示する列数（PC）</label>
          <input type="number" id="grid_columns_pc" name="grid_columns_pc" value="<?php echo esc_attr( $grid_columns_pc ); ?>" min="1" max="8" class="small-text">
          <span class="description">1〜8の数値を入力してください（デフォルト: 3）</span>
        </div>
        <div class="settings-row">
          <label for="grid_columns_mobile">一覧で表示する列数（スマホ）</label>
          <input type="number" id="grid_columns_mobile" name="grid_columns_mobile" value="<?php echo esc_attr( $grid_columns_mobile ); ?>" min="1" max="4" class="small-text">
          <span class="description">1〜4の数値を入力してください（デフォルト: 2）</span>
        </div>
        <div class="settings-row">
          <label for="hide_header_on_single">
            <input type="checkbox" id="hide_header_on_single" name="hide_header_on_single" value="1" <?php checked( $hide_header_on_single, 1 ); ?>>
            投稿詳細でヘッダーを隠す
          </label>
        </div>
      </div>
      
      <h2>SNS設定</h2>
      <div class="settings-section">
        <div class="settings-row">
          <label>SNSリンク</label>
          <div class="settings-input">
            <div id="sns-items-container">
              <?php
              if ( ! empty( $sns_items ) ) {
                foreach ( $sns_items as $index => $item ) {
                  $image_id = isset( $item['image_id'] ) ? $item['image_id'] : 0;
                  ?>
                  <div class="sns-item">
                    <p>
                      <label>アイコン画像:</label>
                      <div class="image-preview-wrapper">
                        <?php if ( $image_id ) : ?>
                          <img src="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'thumbnail' ) ); ?>" style="max-width: 50px; display: block; margin-bottom: 10px;" id="sns-icon-<?php echo $index; ?>-preview">
                        <?php else : ?>
                          <img src="" style="max-width: 50px; display: none; margin-bottom: 10px;" id="sns-icon-<?php echo $index; ?>-preview">
                        <?php endif; ?>
                      </div>
                      <input type="hidden" name="sns_items[<?php echo $index; ?>][image_id]" value="<?php echo esc_attr( $image_id ); ?>" class="sns-icon-input">
                      <button type="button" class="button upload-sns-icon-button" data-index="<?php echo $index; ?>">画像を選択</button>
                      <button type="button" class="button remove-sns-icon-button" data-index="<?php echo $index; ?>">画像削除</button>
                    </p>
                    <p>
                      <label>SNS名:</label>
                      <input type="text" name="sns_items[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $item['name'] ); ?>" placeholder="Twitter" style="width: 200px;">
                    </p>
                    <p>
                      <label>URL:</label>
                      <input type="url" name="sns_items[<?php echo $index; ?>][url]" value="<?php echo esc_attr( $item['url'] ); ?>" placeholder="https://twitter.com/username" style="width: 400px;">
                    </p>
                    <button type="button" class="button remove-sns-button">削除</button>
                  </div>
                  <?php
                }
              }
              ?>
            </div>
            <button type="button" id="add-sns-button" class="button">SNSリンクを追加</button>
          </div>
        </div>
      </div>
      
      <h2>ページ共有リンク</h2>
      <div class="settings-section">
        <div class="settings-row">
          <label for="enable_share_links">
            <input type="checkbox" id="enable_share_links" name="enable_share_links" value="1" <?php checked( $enable_share_links, 1 ); ?>>
            単一投稿ページにSNS共有リンクを表示する
          </label>
          <span class="description">Twitter、Facebook、はてなブックマーク、LINEへの共有ボタンが表示されます</span>
        </div>
      </div>
      
      <h2>その他</h2>
      <div class="settings-section">
        <div class="settings-row">
          <label for="ga_tracking_id">Google Analytics トラッキングID</label>
          <input type="text" id="ga_tracking_id" name="ga_tracking_id" value="<?php echo esc_attr( $ga_tracking_id ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
          <span class="description">トラッキングIDを入力してください（例: G-XXXXXXXXXX、UA-XXXXXXXXX-X）</span>
        </div>
        <div class="settings-row">
          <label for="past_images_display">過去画像一覧の表示</label>
          <select id="past_images_display" name="past_images_display">
            <option value="all" <?php selected( $past_images_display, 'all' ); ?>>全投稿</option>
            <option value="same_type" <?php selected( $past_images_display, 'same_type' ); ?>>同じ作品タイプ</option>
            <option value="same_category" <?php selected( $past_images_display, 'same_category' ); ?>>同じカテゴリ</option>
          </select>
          <span class="description">単一投稿ページで表示する過去画像一覧の範囲を選択してください</span>
        </div>
        <div class="settings-row">
          <label for="copyright_text">コピーライト表記</label>
          <input type="text" id="copyright_text" name="copyright_text" value="<?php echo esc_attr( $copyright_text ); ?>" class="regular-text" placeholder="©<?php echo date('Y'); ?> <?php echo esc_attr( $display_name ); ?>">
          <span class="description">空欄の場合は「©年度 ユーザー名」と表示されます</span>
        </div>
        <div class="settings-row">
          <label for="custom_css">CSS追加</label>
          <textarea id="custom_css" name="custom_css" rows="10" class="large-text code"><?php echo esc_textarea( $custom_css_additional ); ?></textarea>
          <span class="description">カスタムCSSを追加できます。ここに記述したCSSはサイトに直接適用されます。</span>
        </div>
        <div class="settings-row">
          <label for="block_ai_crawlers">
            <input type="checkbox" id="block_ai_crawlers" name="block_ai_crawlers" value="1" <?php checked( $block_ai_crawlers, 1 ); ?>>
            AIクローラーをブロックする
          </label>
          <span class="description">主要なAIクローラー（GPTBot、CCBot、Google-Extended等）のアクセスをブロックします。※すべてのAIクローラーを完全に防げるわけではありません。</span>
        </div>
      </div>
      
      <h2>OGP設定</h2>
      <div class="settings-section">
        <div class="settings-row">
          <label for="ogp_title">OGPタイトル</label>
          <input type="text" id="ogp_title" name="ogp_title" value="<?php echo esc_attr( $ogp_title ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $display_name ); ?>">
          <span class="description">SNSでシェアされた際のタイトル（空欄の場合は表示名が使用されます）</span>
        </div>
        <div class="settings-row">
          <label for="ogp_description">OGP説明文</label>
          <textarea id="ogp_description" name="ogp_description" rows="3" class="large-text"><?php echo esc_textarea( $ogp_description ); ?></textarea>
          <span class="description">SNSでシェアされた際の説明文</span>
        </div>
        <div class="settings-row">
          <label for="ogp_image">OGP画像</label>
          <div class="image-upload-wrapper">
            <input type="hidden" id="ogp_image" name="ogp_image" value="<?php echo esc_attr( $ogp_image ); ?>">
            <button type="button" class="button upload-image-button" data-target="ogp_image">画像を選択</button>
            <button type="button" class="button remove-image-button" data-target="ogp_image">削除</button>
            <?php if ( $ogp_image ) : ?>
              <img id="ogp-image-preview" src="<?php echo esc_url( wp_get_attachment_image_url( $ogp_image, 'medium' ) ); ?>" style="max-width: 300px; margin-top: 10px; display: block;">
            <?php else : ?>
              <img id="ogp-image-preview" src="" style="max-width: 300px; margin-top: 10px; display: none;">
            <?php endif; ?>
          </div>
          <span class="description">SNSでシェアされた際に表示される画像（推奨サイズ: 1200x630px）</span>
        </div>
        <div class="settings-row">
          <label for="twitter_card">Twitterカードタイプ</label>
          <select id="twitter_card" name="twitter_card">
            <option value="summary" <?php selected( $twitter_card, 'summary' ); ?>>Summary（小さい画像）</option>
            <option value="summary_large_image" <?php selected( $twitter_card, 'summary_large_image' ); ?>>Summary Large Image（大きい画像）</option>
          </select>
        </div>
      </div>
      
      <p class="submit">
        <input type="submit" name="my_theme_settings_submit" class="button button-primary" value="設定を保存">
      </p>
    </form>
  </div>
  
  <script>
  jQuery(document).ready(function($) {
    // カラーピッカー
    $('.color-picker').wpColorPicker();
    
    // 画像アップロード
    var mediaUploader;
    $('.upload-image-button').on('click', function(e) {
      e.preventDefault();
      var button = $(this);
      var targetInput = button.data('target');
      var targetPreview = $('#' + targetInput.replace('_', '-') + '-preview');
      
      mediaUploader = wp.media({
        title: '画像を選択',
        button: { text: '選択' },
        multiple: false
      });
      
      mediaUploader.on('select', function() {
        var attachment = mediaUploader.state().get('selection').first().toJSON();
        $('#' + targetInput).val(attachment.id);
        targetPreview.attr('src', attachment.url).show();
      });
      
      mediaUploader.open();
    });
    
    // 画像削除
    $('.remove-image-button').on('click', function(e) {
      e.preventDefault();
      var button = $(this);
      var targetInput = button.data('target');
      var targetPreview = $('#' + targetInput.replace('_', '-') + '-preview');
      
      $('#' + targetInput).val('');
      targetPreview.hide().attr('src', '');
    });
    
    // SNS追加
    var snsIndex = <?php echo count( $sns_items ); ?>;
    $('#add-sns-button').on('click', function() {
      var html = '<div class="sns-item">';
      html += '<p><label>アイコン画像:</label>';
      html += '<div class="image-preview-wrapper">';
      html += '<img src="" style="max-width: 50px; display: none; margin-bottom: 10px;" id="sns-icon-' + snsIndex + '-preview">';
      html += '</div>';
      html += '<input type="hidden" name="sns_items[' + snsIndex + '][image_id]" value="" class="sns-icon-input">';
      html += '<button type="button" class="button upload-sns-icon-button" data-index="' + snsIndex + '">画像を選択</button>';
      html += '<button type="button" class="button remove-sns-icon-button" data-index="' + snsIndex + '">画像削除</button></p>';
      html += '<p><label>SNS名:</label>';
      html += '<input type="text" name="sns_items[' + snsIndex + '][name]" placeholder="Twitter" style="width: 200px;"></p>';
      html += '<p><label>URL:</label>';
      html += '<input type="url" name="sns_items[' + snsIndex + '][url]" placeholder="https://twitter.com/username" style="width: 400px;"></p>';
      html += '<button type="button" class="button remove-sns-button">削除</button>';
      html += '</div>';
      
      $('#sns-items-container').append(html);
      snsIndex++;
    });
    
    // SNSアイコン画像アップロード
    $(document).on('click', '.upload-sns-icon-button', function(e) {
      e.preventDefault();
      var button = $(this);
      var snsIndex = button.data('index');
      
      var mediaUploader = wp.media({
        title: 'アイコン画像を選択',
        button: { text: '選択' },
        multiple: false
      });
      
      mediaUploader.on('select', function() {
        var attachment = mediaUploader.state().get('selection').first().toJSON();
        button.siblings('.sns-icon-input').val(attachment.id);
        button.siblings('.image-preview-wrapper').find('img').attr('src', attachment.url).show();
      });
      
      mediaUploader.open();
    });
    
    // SNSアイコン画像削除
    $(document).on('click', '.remove-sns-icon-button', function(e) {
      e.preventDefault();
      var button = $(this);
      button.siblings('.sns-icon-input').val('');
      button.siblings('.image-preview-wrapper').find('img').hide().attr('src', '');
    });
    
    // SNS削除
    $(document).on('click', '.remove-sns-button', function() {
      $(this).closest('.sns-item').remove();
    });
  });
  </script>
  <?php
}

/**
 * 管理画面でカラーピッカーとメディアアップローダーを読み込む
 */
function my_theme_enqueue_settings_scripts( $hook ) {
  if ( 'toplevel_page_my-theme-settings' === $hook ) {
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_media();
    wp_enqueue_style( 'my-theme-admin', get_template_directory_uri() . '/admin.css', array(), '1.0.0' );
  }
}
add_action( 'admin_enqueue_scripts', 'my_theme_enqueue_settings_scripts' );

/**
 * カスタムフィールドの追加
 */
function my_theme_add_custom_fields() {
  add_meta_box(
    'artwork_details',
    'イラスト・漫画の詳細',
    'my_theme_custom_fields_callback',
    'post',
    'normal',
    'high'
  );
}
add_action( 'add_meta_boxes', 'my_theme_add_custom_fields' );

/**
 * 管理画面でメディアアップローダーのスクリプトを読み込む
 */
function my_theme_enqueue_admin_scripts( $hook ) {
  if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
    wp_enqueue_media();
    wp_enqueue_style( 'my-theme-admin', get_template_directory_uri() . '/admin.css', array(), '1.0.0' );
  }
}
add_action( 'admin_enqueue_scripts', 'my_theme_enqueue_admin_scripts' );

/**
 * カスタムフィールドの表示
 */
function my_theme_custom_fields_callback( $post ) {
  wp_nonce_field( 'my_theme_save_custom_fields', 'my_theme_custom_fields_nonce' );
  
  $artwork_type = get_post_meta( $post->ID, '_artwork_type', true );
  $artwork_images = get_post_meta( $post->ID, '_artwork_images', true );
  $thumbnail_image_id = get_post_meta( $post->ID, '_thumbnail_image_id', true );
  $artwork_description = get_post_meta( $post->ID, '_artwork_description', true );
  
  if ( ! is_array( $artwork_images ) ) {
    $artwork_images = array();
  }
  
  // 初期選択は1枚目
  if ( empty( $thumbnail_image_id ) && ! empty( $artwork_images ) ) {
    $thumbnail_image_id = $artwork_images[0];
  }
  ?>
  
  <p>
    <label for="artwork_type"><strong>作品タイプ:</strong></label><br>
    <select name="artwork_type" id="artwork_type" style="width: 200px;">
      <option value="illustration" <?php selected( $artwork_type, 'illustration' ); ?>>イラスト</option>
      <option value="manga" <?php selected( $artwork_type, 'manga' ); ?>>漫画</option>
    </select>
  </p>
  
  <p>
    <label for="artwork_description"><strong>作品説明:</strong></label><br>
    <textarea name="artwork_description" id="artwork_description" rows="5" style="width: 100%; max-width: 600px;"><?php echo esc_textarea( $artwork_description ); ?></textarea>
  </p>
  
  <p>
    <label><strong>画像アップロード:</strong></label><br>
    <button type="button" class="button artwork-upload-button">画像を追加</button>
    <button type="button" class="button artwork-clear-button">すべてクリア</button>
  </p>
  
  <div id="artwork-images-container" style="margin-top: 15px;">
    <?php
    if ( ! empty( $artwork_images ) ) {
      foreach ( $artwork_images as $index => $image_id ) {
        $image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
        if ( $image_url ) {
          $is_checked = ( $thumbnail_image_id == $image_id ) ? 'checked' : '';
          $border_color = ( $thumbnail_image_id == $image_id ) ? '#0073aa' : '#ddd';
          echo '<div class="artwork-image-item" style="border-color: ' . $border_color . ';">';
          echo '<img src="' . esc_url( $image_url ) . '">';
          echo '<input type="hidden" name="artwork_images[]" value="' . esc_attr( $image_id ) . '">';
          echo '<label>';
          echo '<input type="radio" name="thumbnail_image_id" value="' . esc_attr( $image_id ) . '" ' . $is_checked . ' class="thumbnail-radio"> サムネイル';
          echo '</label>';
          echo '<button type="button" class="artwork-remove-image">×</button>';
          echo '</div>';
        }
      }
    }
    ?>
  </div>
  
  <script>
  jQuery(document).ready(function($) {
    var mediaUploader;
    
    $('.artwork-upload-button').on('click', function(e) {
      e.preventDefault();
      
      if (mediaUploader) {
        mediaUploader.open();
        return;
      }
      
      mediaUploader = wp.media({
        title: '画像を選択',
        button: {
          text: '画像を追加'
        },
        multiple: true
      });
      
      mediaUploader.on('select', function() {
        var attachments = mediaUploader.state().get('selection').toJSON();
        var container = $('#artwork-images-container');
        var isFirstImage = container.children().length === 0;
        
        attachments.forEach(function(attachment, index) {
          var isChecked = (isFirstImage && index === 0) ? 'checked' : '';
          var html = '<div class="artwork-image-item">';
          html += '<img src="' + attachment.sizes.thumbnail.url + '">';
          html += '<input type="hidden" name="artwork_images[]" value="' + attachment.id + '">';
          html += '<label>';
          html += '<input type="radio" name="thumbnail_image_id" value="' + attachment.id + '" ' + isChecked + ' class="thumbnail-radio"> サムネイル';
          html += '</label>';
          html += '<button type="button" class="artwork-remove-image">×</button>';
          html += '</div>';
          container.append(html);
          
          isFirstImage = false;
        });
      });
      
      mediaUploader.open();
    });
    
    $(document).on('click', '.artwork-remove-image', function() {
      var item = $(this).parent('.artwork-image-item');
      var wasChecked = item.find('input[type="radio"]').prop('checked');
      item.remove();
      
      // 削除した画像がサムネイルだった場合、最初の画像を選択
      if (wasChecked) {
        $('#artwork-images-container .thumbnail-radio:first').prop('checked', true).trigger('change');
      }
    });
    
    // サムネイル選択時にボーダーを変更
    $(document).on('change', '.thumbnail-radio', function() {
      $('.artwork-image-item').css('border-color', '#ddd');
      $(this).closest('.artwork-image-item').css('border-color', '#0073aa');
    });
    
    $('.artwork-clear-button').on('click', function(e) {
      e.preventDefault();
      $('#artwork-images-container').empty();
    });
  });
  </script>
  <?php
}

/**
 * カスタムフィールドの保存
 */
function my_theme_save_custom_fields( $post_id ) {
  if ( ! isset( $_POST['my_theme_custom_fields_nonce'] ) ) {
    return;
  }
  
  if ( ! wp_verify_nonce( $_POST['my_theme_custom_fields_nonce'], 'my_theme_save_custom_fields' ) ) {
    return;
  }
  
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }
  
  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }
  
  if ( isset( $_POST['artwork_type'] ) ) {
    update_post_meta( $post_id, '_artwork_type', sanitize_text_field( $_POST['artwork_type'] ) );
  }
  
  if ( isset( $_POST['artwork_description'] ) ) {
    update_post_meta( $post_id, '_artwork_description', wp_kses_post( $_POST['artwork_description'] ) );
  } else {
    delete_post_meta( $post_id, '_artwork_description' );
  }
  
  if ( isset( $_POST['artwork_images'] ) && is_array( $_POST['artwork_images'] ) ) {
    $images = array_map( 'intval', $_POST['artwork_images'] );
    update_post_meta( $post_id, '_artwork_images', $images );
  } else {
    delete_post_meta( $post_id, '_artwork_images' );
  }
  
  // サムネイル画像IDの保存
  if ( isset( $_POST['thumbnail_image_id'] ) ) {
    update_post_meta( $post_id, '_thumbnail_image_id', intval( $_POST['thumbnail_image_id'] ) );
  } else {
    // サムネイルが選択されていない場合は最初の画像を使用
    if ( isset( $_POST['artwork_images'] ) && is_array( $_POST['artwork_images'] ) && ! empty( $_POST['artwork_images'] ) ) {
      update_post_meta( $post_id, '_thumbnail_image_id', intval( $_POST['artwork_images'][0] ) );
    } else {
      delete_post_meta( $post_id, '_thumbnail_image_id' );
    }
  }
}
add_action( 'save_post', 'my_theme_save_custom_fields' );

/**
 * クラシックエディターを使用
 */
add_filter( 'use_block_editor_for_post', '__return_false', 10 );

/**
 * 管理バーからコメント関連を削除、投稿機能を調整
 */
function my_theme_remove_comment_support() {
  remove_post_type_support( 'post', 'comments' );
  remove_post_type_support( 'page', 'comments' );
  remove_post_type_support( 'post', 'editor' );  // メインコンテンツを削除
  remove_post_type_support( 'post', 'post-formats' );
  remove_post_type_support( 'post', 'thumbnail' );  // アイキャッチ画像を削除
}
add_action( 'init', 'my_theme_remove_comment_support' );

/**
 * タグ機能を無効化
 */
function my_theme_unregister_tags() {
  unregister_taxonomy_for_object_type( 'post_tag', 'post' );
}
add_action( 'init', 'my_theme_unregister_tags' );

/**
 * ダッシュボードウィジェットからコメント関連を削除
 */
function my_theme_remove_dashboard_widgets() {
  remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'my_theme_remove_dashboard_widgets' );

/**
 * スクリプトとスタイルシートの読み込み
 */
function my_theme_scripts() {
  // スタイルシートの読み込み
  wp_enqueue_style( 'my-theme-style', get_stylesheet_directory_uri() . '/style.css', array(), '1.0.0' );
  
  // Noto Sans JPフォントの読み込み（標準フォント）
  wp_enqueue_style( 'google-fonts-noto-sans-jp', 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap', array(), null );
  
  // Font Awesomeの読み込み
  wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0' );
  
  // カスタムCSSを出力
  $bg_color = get_option( 'my_theme_bg_color', '#f5f5f5' );
  $text_color = get_option( 'my_theme_text_color', '#333333' );
  $link_color = get_option( 'my_theme_link_color', '#0073aa' );
  $header_image = get_option( 'my_theme_header_image', 0 );
  $grid_columns_pc = get_option( 'my_theme_grid_columns_pc', 3 );
  $grid_columns_mobile = get_option( 'my_theme_grid_columns_mobile', 2 );
  
  $custom_css = "
    body {
      background-color: {$bg_color};
      color: {$text_color};
      font-family: 'Noto Sans JP', sans-serif;
    }
    a {
      color: {$link_color};
    }
    a:hover {
      opacity: 0.8;
    }
    .artwork-grid {
      grid-template-columns: repeat({$grid_columns_pc}, 1fr);
    }
    @media screen and (max-width: 768px) {
      .artwork-grid {
        grid-template-columns: repeat({$grid_columns_mobile}, 1fr);
      }
    }
  ";
  
  if ( $header_image ) {
    $header_image_url = wp_get_attachment_image_url( $header_image, 'full' );
    if ( $header_image_url ) {
      $custom_css .= "
        .site-header {
          background-image: url('{$header_image_url}');
          background-size: cover;
          background-position: center;
        }
        .site-header .container {
          background-color: rgba(255, 255, 255, 0.9);
          padding: 20px;
          border-radius: 8px;
        }
      ";
    }
  }
  
  wp_add_inline_style( 'my-theme-style', $custom_css );
  
  // カスタムCSS追加を出力
  $custom_css_additional = get_option( 'my_theme_custom_css', '' );
  if ( ! empty( $custom_css_additional ) ) {
    wp_add_inline_style( 'my-theme-style', $custom_css_additional );
  }
}
add_action( 'wp_enqueue_scripts', 'my_theme_scripts' );

/**
 * Google Analyticsトラッキングコードを出力
 */
function my_theme_add_google_analytics() {
  $ga_tracking_id = get_option( 'my_theme_ga_tracking_id', '' );
  
  if ( empty( $ga_tracking_id ) ) {
    return;
  }
  
  // GA4 (G-XXXXXXXXXX) または Universal Analytics (UA-XXXXXXXXX-X) に対応
  if ( strpos( $ga_tracking_id, 'G-' ) === 0 ) {
    // GA4
    ?>
    <!-- Google Analytics (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_tracking_id ); ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?php echo esc_js( $ga_tracking_id ); ?>');
    </script>
    <?php
  } elseif ( strpos( $ga_tracking_id, 'UA-' ) === 0 ) {
    // Universal Analytics
    ?>
    <!-- Google Analytics (Universal) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_tracking_id ); ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?php echo esc_js( $ga_tracking_id ); ?>');
    </script>
    <?php
  }
}
add_action( 'wp_head', 'my_theme_add_google_analytics' );

/**
 * OGPタグを出力
 */
function my_theme_add_ogp_tags() {
  $ogp_title = get_option( 'my_theme_ogp_title', '' );
  $ogp_description = get_option( 'my_theme_ogp_description', '' );
  $ogp_image = get_option( 'my_theme_ogp_image', 0 );
  $twitter_card = get_option( 'my_theme_twitter_card', 'summary_large_image' );
  $display_name = get_option( 'my_theme_display_name', get_bloginfo( 'name' ) );
  
  // タイトル
  $title = ! empty( $ogp_title ) ? $ogp_title : $display_name;
  if ( is_singular() ) {
    $title = get_the_title();
  }
  
  // 説明文
  $description = $ogp_description;
  if ( is_singular() ) {
    // 作品説明があれば優先
    $artwork_description = get_post_meta( get_the_ID(), '_artwork_description', true );
    if ( ! empty( $artwork_description ) ) {
      $description = wp_strip_all_tags( $artwork_description );
      // 最大200文字に制限
      if ( mb_strlen( $description ) > 200 ) {
        $description = mb_substr( $description, 0, 200 ) . '...';
      }
    } elseif ( has_excerpt() ) {
      $description = get_the_excerpt();
    }
  }
  
  // 画像
  $image_url = '';
  if ( is_singular() ) {
    // カスタムフィールドのサムネイル画像IDを優先
    $custom_thumbnail_id = get_post_meta( get_the_ID(), '_thumbnail_image_id', true );
    if ( $custom_thumbnail_id ) {
      $image_url = wp_get_attachment_image_url( $custom_thumbnail_id, 'large' );
    }
    
    // カスタムサムネイルがない場合は通常のアイキャッチを使用
    if ( empty( $image_url ) ) {
      $thumbnail_id = get_post_thumbnail_id();
      if ( $thumbnail_id ) {
        $image_url = wp_get_attachment_image_url( $thumbnail_id, 'large' );
      }
    }
  }
  if ( empty( $image_url ) && $ogp_image ) {
    $image_url = wp_get_attachment_image_url( $ogp_image, 'large' );
  }
  
  // URL
  $url = is_singular() ? get_permalink() : home_url( '/' );
  
  ?>
  <!-- OGP Tags -->
  <meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
  <meta property="og:type" content="<?php echo is_singular() ? 'article' : 'website'; ?>">
  <meta property="og:url" content="<?php echo esc_url( $url ); ?>">
  <?php if ( ! empty( $description ) ) : ?>
  <meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
  <?php endif; ?>
  <?php if ( ! empty( $image_url ) ) : ?>
  <meta property="og:image" content="<?php echo esc_url( $image_url ); ?>">
  <?php endif; ?>
  <meta property="og:site_name" content="<?php echo esc_attr( $display_name ); ?>">
  
  <!-- Twitter Card Tags -->
  <meta name="twitter:card" content="<?php echo esc_attr( $twitter_card ); ?>">
  <meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
  <?php if ( ! empty( $description ) ) : ?>
  <meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
  <?php endif; ?>
  <?php if ( ! empty( $image_url ) ) : ?>
  <meta name="twitter:image" content="<?php echo esc_url( $image_url ); ?>">
  <?php endif; ?>
  <?php
}
add_action( 'wp_head', 'my_theme_add_ogp_tags' );

/**
 * AIクローラーをブロック
 */
function my_theme_block_ai_crawlers() {
  $block_ai_crawlers = get_option( 'my_theme_block_ai_crawlers', 0 );
  
  if ( ! $block_ai_crawlers ) {
    return;
  }
  
  ?>
  <!-- AI Crawler Blocking -->
  <meta name="robots" content="noai, noimageai">
  <meta name="googlebot" content="noai, noimageai">
  <?php
}
add_action( 'wp_head', 'my_theme_block_ai_crawlers' );

/**
 * 画像の右クリック禁止
 */
function my_theme_disable_right_click_on_images() {
  ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // 画像の右クリックを禁止
    document.querySelectorAll('img').forEach(function(img) {
      img.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
      });
    });
    
    // ドラッグ禁止
    document.querySelectorAll('img').forEach(function(img) {
      img.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
      });
    });
  });
  </script>
  <?php
}
add_action( 'wp_footer', 'my_theme_disable_right_click_on_images' );
