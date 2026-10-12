<?php
/**
 * TwinWeb — Module Bootstrap (the /gpt/ half: page, SPA, projects / CRM-tasks / CRM-pipeline REST, profile-grounding admin)
 *
 * [2026-10-12 11:36 AM Johnny Chu - Chu Hoàng Anh] PHASE-0.102 S102-L24 — the CORE half (class-twinweb-rest.php + the twelve classes it calls, the Ask Brain
 * tools, artifact jobs, the two tables, the profile templates) moved to core/twinweb-axis/ (loaded first by bizcity-twin-ai.php). This file is what becomes the
 * add-on bizcity-twinweb-addon at W3; it needs the axis (BIZCITY_TWINWEB_AXIS_DIR) and does nothing without it.
 *
 * Load order:
 *   1. Constants
 *   2. Page installer, Page (rewrite + template), Profile grounding
 *   3. The three add-on REST classes handed to the axis REST slice
 *
 * Gates:
 *   - Always-load (public page, REST) — NO admin gate (R-PERF).
 *   - [2026-10-11 11:01 AM Johnny Chu - Chu Hoàng Anh] CORE-REDUCTION WP-20 Z-6 P0-2/P2 — the REST slice (BizCity_TwinWeb_Rest_Slice, now in
 *     core/twinweb-axis) loads the REST classes only for bizcity-twinweb REST, REST index, admin/ajax, CLI, TwinWeb cron hooks; lazy elsewhere.
 *   - Page creation deferred to plugins_loaded (not file-scope).
 *
 * Rewrite flush: handled via BizCity_Rewrite_Flush_Registry (R-CR.1).
 *
 * PHP 7.4 compatible.
 *
 * @package Bizcity_Twin_AI
 * @subpackage Modules\TwinWeb
 * @since 2026-06-17 (PHASE-TWINWEB Wave 1)
 */
defined( 'ABSPATH' ) || exit;

// [2026-10-12 11:36 AM Johnny Chu - Chu Hoàng Anh] PHASE-0.102 S102-L24 — no axis, no /gpt/: never half-load.
if ( ! defined( 'BIZCITY_TWINWEB_AXIS_DIR' ) ) {
	return;
}

// ── Constants ─────────────────────────────────────────────────────────────────
define( 'BIZCITY_TWINWEB_DIR',     __DIR__ . '/' );
define( 'BIZCITY_TWINWEB_URL',     plugin_dir_url( __FILE__ ) );
define( 'BIZCITY_TWINWEB_VERSION', '1.0.0' );

// [2026-06-18 Johnny Chu] PHASE-TWINWEB — bumped to 1.0.1 to flush old ^twin(?:/.*)? rule
// that was hijacking /twin/ (twinshell's URL). Version bump triggers one-time flush via
// BizCity_Rewrite_Flush_Registry on next admin_init. (NEVER use time() here)
define( 'BIZCITY_TWINWEB_REWRITE_VERSION', '1.0.10' ); // [2026-09-30 Claude Opus 5.5] PHASE-0.84 D-84-13 — drop /gpt/twinchat/ rewrite. Previously: [2026-09-18] PHASE-0.52 — add /gpt/myspace/ + /gpt/mycustomers/ (1.0.8

// ── Includes ──────────────────────────────────────────────────────────────────
require_once BIZCITY_TWINWEB_DIR . 'includes/class-twinweb-page-installer.php';
require_once BIZCITY_TWINWEB_DIR . 'includes/class-twinweb-page.php';
require_once BIZCITY_TWINWEB_DIR . 'includes/class-twinweb-profile-grounding.php';

// [2026-06-22 Johnny Chu] PHASE-TWINWEB — projects REST (port from webchat, clean prefix)
// [2026-09-17 Johnny Chu - Chu Hoàng Anh] PHASE-0.50 W5 — member "Việc được giao" REST (C surface, current user only).
// [2026-09-18] PHASE-0.52 — member customer pipeline REST (Hôm nay, Khách của tôi, Inbox stage, Không gian của tôi).
// [2026-10-11 11:00 AM Johnny Chu - Chu Hoàng Anh] CORE-REDUCTION WP-20 Z-6 P0-2/P2 — projects / crm-tasks / crm-pipeline REST load with the REST slice.
// [2026-10-12 11:36 AM Johnny Chu - Chu Hoàng Anh] PHASE-0.102 S102-L24 — the slice lives in core/twinweb-axis now; these three classes are handed to it, same order as before.
BizCity_TwinWeb_Rest_Slice::register_addon_classes( array(
	'BizCity_TwinWeb_Projects_REST'     => BIZCITY_TWINWEB_DIR . 'includes/class-twinweb-projects-rest.php',
	'BizCity_TwinWeb_CRM_Tasks_REST'    => BIZCITY_TWINWEB_DIR . 'includes/class-twinweb-crm-tasks-rest.php',
	'BizCity_TwinWeb_CRM_Pipeline_REST' => BIZCITY_TWINWEB_DIR . 'includes/class-twinweb-crm-pipeline-rest.php',
) );

// ── /gpt/ page (deferred — avoid file-scope DB calls, R-PERF.2) ───────────────
add_action( 'plugins_loaded', function () {
	// [2026-07-31 Johnny Chu] R-PERF/R-MSDB — do not touch the DB on ordinary frontend HTML requests.
	$schema_context = is_admin()
		|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		|| ( ! empty( $_SERVER['REQUEST_URI'] ) && false !== strpos( (string) $_SERVER['REQUEST_URI'], '/wp-json/' ) )
		|| ( defined( 'DOING_CRON' ) && DOING_CRON )
		|| ( defined( 'WP_CLI' ) && WP_CLI );
	if ( ! $schema_context ) {
		return;
	}
	// [2026-07-15 Johnny Chu] PHASE-TWINWEB — ensure managed page slug migrates to /gpt/
	// even when DB schema version is already up-to-date.
	if ( class_exists( 'BizCity_TwinWeb_Page_Installer' ) ) {
		BizCity_TwinWeb_Page_Installer::maybe_create_page();
	}
	// NOTE: BizCity_TwinWeb_Projects_REST uses bizcity_webchat_projects (existing table).
	// No installer call needed — no new table created.
}, 20 );

// ── Public page ───────────────────────────────────────────────────────────────
if ( class_exists( 'BizCity_TwinWeb_Page' ) ) {
	BizCity_TwinWeb_Page::instance()->register();
}
if ( class_exists( 'BizCity_TwinWeb_Profile_Grounding' ) ) {
	BizCity_TwinWeb_Profile_Grounding::init();
}

// ── Twin Shell ActivityBar entry ──────────────────────────────────────────────
// [2026-09-30 Claude Opus 5.5] PHASE-0.84 W-02 — the site owner and staff open Twin GPT inside /twin/.
// The iframe still loads /gpt/, so every request stays the C surface (R-LM §1.1: the URL decides the role).
// [2026-10-12 11:47 AM Johnny Chu - Chu Hoàng Anh] PHASE-0.102 S102-L9 — the 'gpt' ActivityBar entry moved to modules/twinshell/includes/default-plugins.php (core), where it hides itself when this part is absent.

// ── REST routes (the add-on's classes; the axis registers BizCity_TwinWeb_REST on its own hook) ──
add_action( 'rest_api_init', function () {
	// [2026-06-22 Johnny Chu] PHASE-TWINWEB — projects REST
	if ( class_exists( 'BizCity_TwinWeb_Projects_REST', false ) ) {
		BizCity_TwinWeb_Projects_REST::instance()->register_routes();
	}
	// [2026-10-09 Johnny Chu - Chu Hoàng Anh] PHASE-0.96 Q96-7 — /gpt/crm/ stays in TwinWeb on the core/crm spine; the task and
	// pipeline projections exist only when the Zalo Brain CRM plugin registered the 'workspace' feature (BizCity_CRM_Spine::has).
	$_bizcity_twinweb_crm_workspace = class_exists( 'BizCity_CRM_Spine' ) && BizCity_CRM_Spine::has( 'workspace' )
		&& class_exists( 'BizCity_CRM_Task_Handoff' ) && class_exists( 'BizCity_CRM_Customer_Pipeline' );
	// [2026-09-17 Johnny Chu - Chu Hoàng Anh] PHASE-0.50 W5 — leader-task-handoff member projection.
	if ( $_bizcity_twinweb_crm_workspace && class_exists( 'BizCity_TwinWeb_CRM_Tasks_REST', false ) ) {
		BizCity_TwinWeb_CRM_Tasks_REST::instance()->register_routes();
	}
	// [2026-09-18] PHASE-0.52 — R-PIPE member projection (current user only).
	if ( $_bizcity_twinweb_crm_workspace && class_exists( 'BizCity_TwinWeb_CRM_Pipeline_REST', false ) ) {
		BizCity_TwinWeb_CRM_Pipeline_REST::instance()->register_routes();
	}
	unset( $_bizcity_twinweb_crm_workspace );
} );

// ── Rewrite flush registry (R-CR.1) ───────────────────────────────────────────
// [2026-06-17 Johnny Chu] PHASE-TWINWEB — register at file-load time (outside hooks)
if ( class_exists( 'BizCity_Rewrite_Flush_Registry' ) ) {
	BizCity_Rewrite_Flush_Registry::register( 'bizcity-twinweb', BIZCITY_TWINWEB_REWRITE_VERSION );
}

// [2026-07-14 Johnny Chu] PHASE-TWINWEB-SEARCH W1 — load DDV probes only in diagnostics contexts.
function bizcity_twinweb_load_diagnostics_probes() {
	static $loaded = false;
	if ( $loaded || ! class_exists( 'BizCity_Safe_Loader' ) ) {
		return;
	}
	$loaded = true;
	// [2026-09-01 Johnny Chu] PHASE-0.45-DIAGNOSTICS — include direct PHP CLI in the same lazy probe path as Diagnostics UI/REST.
	$probe_files = array(
		'class-probe-twinweb-channel.php',
		'class-probe-twinweb-document-search.php',
		'class-probe-twinweb-citation.php',
		'class-probe-twinweb-fb-connect.php',
		'class-probe-twinweb-control-plane-dashboards.php',
		'class-probe-twinweb-appearance.php',
		'class-probe-twinweb-skin-renderer.php',
		'class-probe-twinweb-shortcode-surfaces.php',
		'class-probe-twinweb-customer-profile-grounding.php',
	);
	foreach ( $probe_files as $fname ) {
		$path = BIZCITY_TWINWEB_DIR . 'includes/' . $fname;
		if ( is_file( $path ) && is_readable( $path ) ) {
			BizCity_Safe_Loader::require_file( $path, 'twinweb.diagnostics.' . sanitize_key( basename( $fname, '.php' ) ) );
		}
	}
}

if ( defined( 'BIZCITY_DIAGNOSTICS_CLI' ) && BIZCITY_DIAGNOSTICS_CLI ) {
	bizcity_twinweb_load_diagnostics_probes();
}
add_action( 'current_screen', function ( $screen ) {
	if ( $screen && false !== strpos( (string) $screen->id, 'bizcity-diagnostics' ) ) {
		bizcity_twinweb_load_diagnostics_probes();
	}
}, 1 );
add_action( 'rest_api_init', function () {
	if ( ! empty( $_SERVER['REQUEST_URI'] ) && false !== strpos( (string) $_SERVER['REQUEST_URI'], '/bizcity-diagnostics/' ) ) {
		bizcity_twinweb_load_diagnostics_probes();
	}
}, 1 );
