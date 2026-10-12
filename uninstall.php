<?php
/**
 * BizCity TwinWeb — uninstall.
 *
 * [2026-10-12 12:11 PM Johnny Chu - Chu Hoàng Anh] PHASE-0.102 S102-L20 (D102-7) — deleting this plugin deletes NOTHING by default. The conversation tables and
 * the options belong to Zalo Brain (core/twinweb-axis), and the WordPress page `gpt` is the site owner's content. Re-installing the plugin picks everything up again.
 * Only when wp-config.php defines BIZCITY_TWINWEB_UNINSTALL_DROP does it forget the id of the `gpt` page (the page itself stays).
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( defined( 'BIZCITY_TWINWEB_UNINSTALL_DROP' ) && BIZCITY_TWINWEB_UNINSTALL_DROP ) {
	delete_option( 'bizcity_twinweb_page_id' );
}
