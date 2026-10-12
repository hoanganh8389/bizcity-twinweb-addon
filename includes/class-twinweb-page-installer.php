<?php
/**
 * TwinWeb — /gpt/ page installer (add-on half of the former BizCity_TwinWeb_Installer).
 *
 * Creates the WordPress page `gpt` holding [bizcity_twin height="100vh"] and migrates the managed legacy slug /twin-ai/ → /gpt/.
 * The tables (threads, artifact jobs) stay in core/twinweb-axis/includes/class-twinweb-installer.php.
 *
 * [2026-10-12 11:35 AM Johnny Chu - Chu Hoàng Anh] PHASE-0.102 S102-L24 — split out of class-twinweb-installer.php; bodies copied unchanged.
 *
 * PHP 7.4 compatible.
 *
 * @package Bizcity_Twin_AI
 * @subpackage Modules\TwinWeb
 */
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'BizCity_TwinWeb_Page_Installer' ) ) { return; }

class BizCity_TwinWeb_Page_Installer {

	/**
	 * Create a WordPress page titled "Twin AI" with [bizcity_twin] shortcode
	 * if no such page exists yet.
	 *
	 * If option already set, keeps idempotency and only migrates managed
	 * legacy slug /twin-ai/ -> /gpt/.
	 */
	public static function maybe_create_page() {
		$target_slug = 'gpt';

		// [2026-07-15 Johnny Chu] PHASE-TWINWEB — keep option idempotent but migrate
		// managed legacy slug /twin-ai/ -> /gpt/ when page_id already exists.
		$page_id = (int) get_option( 'bizcity_twinweb_page_id', 0 );
		if ( $page_id > 0 ) {
			$page = get_post( $page_id );
			if ( $page && 'page' === $page->post_type && 'trash' !== (string) $page->post_status ) {
				self::maybe_migrate_page_slug( $page_id, $target_slug );
				return;
			}

			// [2026-07-15 Johnny Chu] PHASE-TWINWEB — stale option recovery.
			// If saved page_id points to deleted/trash/non-page, clear and recreate.
			delete_option( 'bizcity_twinweb_page_id' );
		}

		// [2026-07-15 Johnny Chu] PHASE-TWINWEB — prefer canonical /gpt/ page if it exists.
		$target_page = get_page_by_path( $target_slug, OBJECT, 'page' );
		if ( $target_page ) {
			update_option( 'bizcity_twinweb_page_id', (int) $target_page->ID );
			return;
		}

		// Check if any published page already has the shortcode
		$existing = get_pages( array(
			's'          => '[bizcity_twin]',
			'post_status' => 'publish',
		) );
		if ( ! empty( $existing ) ) {
			update_option( 'bizcity_twinweb_page_id', $existing[0]->ID );
			// [2026-07-15 Johnny Chu] PHASE-TWINWEB — migrate only managed page slug.
			self::maybe_migrate_page_slug( (int) $existing[0]->ID, $target_slug );
			return;
		}

		$page_id = wp_insert_post( array(
			'post_title'   => 'Twin GPT', // [2026-07-15 Johnny Chu] PHASE-TWIN-GPT-CP TG-0 — canonical product name.
			// [2026-07-15 Johnny Chu] PHASE-TWINWEB — default public URL is /gpt/.
			'post_name'    => $target_slug,
			'post_content' => '[bizcity_twin height="100vh"]',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => 1,
			'meta_input'   => array( '_bizcity_twinweb_page' => '1' ),
		) );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_option( 'bizcity_twinweb_page_id', (int) $page_id );
		}
	}

	/**
	 * Migrate managed TwinWeb page slug from legacy /twin-ai/ to /gpt/.
	 *
	 * Only applies to page marked by _bizcity_twinweb_page meta.
	 * This avoids changing admin-customized pages unexpectedly.
	 *
	 * @param int    $page_id Target page ID.
	 * @param string $target_slug Target slug.
	 */
	private static function maybe_migrate_page_slug( $page_id, $target_slug ) {
		$post = get_post( $page_id );
		if ( ! $post || 'page' !== $post->post_type ) {
			return;
		}

		$managed_flag = (string) get_post_meta( $page_id, '_bizcity_twinweb_page', true );
		if ( $managed_flag !== '1' ) {
			return;
		}

		// [2026-07-15 Johnny Chu] PHASE-TWIN-GPT-CP TG-0 — migrate only known legacy managed titles.
		$legacy_titles = array( 'Twin AI', 'TwinWeb', 'Twin Web' );
		if ( in_array( (string) $post->post_title, $legacy_titles, true ) ) {
			wp_update_post( array(
				'ID'         => (int) $page_id,
				'post_title' => 'Twin GPT',
			) );
		}

		$current_slug = sanitize_title( (string) $post->post_name );
		if ( $current_slug === $target_slug ) {
			return;
		}

		// [2026-07-15 Johnny Chu] PHASE-TWINWEB — migrate only known legacy slug.
		// Keep admin-customized slug untouched.
		if ( $current_slug !== 'twin-ai' ) {
			return;
		}

		$conflict = get_page_by_path( $target_slug, OBJECT, 'page' );
		if ( $conflict && (int) $conflict->ID !== (int) $page_id ) {
			return;
		}

		wp_update_post( array(
			'ID'        => (int) $page_id,
			'post_name' => $target_slug,
		) );
	}
}
