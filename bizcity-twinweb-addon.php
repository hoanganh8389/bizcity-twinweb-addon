<?php
/**
 * Plugin Name:       BizCity TwinWeb (Trợ lý /gpt/)
 * Plugin URI:        https://github.com/hoanganh8389/bizcity-twinweb-addon
 * Description:       Trang /gpt/ kiểu ChatGPT cho thành viên: chat, kênh của tôi, CRM, việc, nội dung. Cần plugin Zalo Brain (bizcity-twin-ai) đang bật — tên thư mục không quan trọng.
 * Version:           1.0.0
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * Network:           false
 * Author:            Biz Central Brain — Johnny Chu (Chu Hoàng Anh)
 * License:           GPL-2.0-or-later
 * Text Domain:       bizcity-twin-ai
 * Zalo Brain Addon:  twinweb
 *
 * [2026-10-12 12:11 PM Johnny Chu - Chu Hoàng Anh] PHASE-0.102 S102-L20/L23 — the /gpt/ half of TwinWeb as its own plugin (R-ADDON-HOST v1, standards/ADDON-HOST-STANDARD.md):
 *   - no `Requires Plugins` header (it would tie the host to the folder name bizcity-twin-ai); the host is recognised by BIZCITY_TWIN_AI_VERSION;
 *   - activation without Zalo Brain is refused (AH-3); running without it shows one notice and loads nothing (AH-4);
 *   - this file loads nothing by itself: Zalo Brain finds this folder through BizCity_Addon_Locator::file( 'twinweb/bootstrap.php' ) (by file name +
 *     the `Zalo Brain Addon:` header above, in ANY folder) and requires bootstrap.php at the same point the bundled copy used to load.
 *     Load mode: define( 'BIZCITY_TWINWEB_LOAD', 'active' | 'auto' | 'off' ) in wp-config.php — default 'active' (the plugin must be activated).
 *
 * Without this plugin Zalo Brain is complete: Ask Brain, the REST axis bizcity-twinweb/v1 (members' Zalo set-up, Guru workspaces, document tools)
 * and the Channel Gateway home all live in the main plugin (core/twinweb-axis). This plugin adds the /gpt/ page and its UI, nothing else.
 */

defined( 'ABSPATH' ) || exit;

define( 'BIZCITY_TWINWEB_ADDON_FILE', __FILE__ );

add_action( 'admin_notices', static function () {
	if ( defined( 'BIZCITY_TWIN_AI_VERSION' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p><strong>BizCity TwinWeb</strong> cần plugin <strong>Zalo Brain</strong> (bizcity-twin-ai) đang hoạt động.</p></div>';
} );

register_activation_hook( __FILE__, static function () {
	if ( defined( 'BIZCITY_TWIN_AI_VERSION' ) ) {
		return;
	}
	deactivate_plugins( plugin_basename( __FILE__ ) );
	wp_die(
		'BizCity TwinWeb cần plugin Zalo Brain (bizcity-twin-ai) được kích hoạt trước. Cách sửa: kích hoạt Zalo Brain rồi kích hoạt lại plugin này.',
		'BizCity TwinWeb',
		array( 'back_link' => true )
	);
} );
