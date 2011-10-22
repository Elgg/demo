<?php
/**
 * Elgg demo plugin
 *
 * @todo - add admin and default widgets automatically to all users
 */

elgg_register_event_handler('init', 'system', 'demo_init');

function demo_init() {
	// elgg wasn't designed to allow you to hook into admin_gatekeeper() or
	// elgg_is_admin_loggedin() make it impossible to easily create a roles system
	elgg_register_page_handler('admin', 'demo_admin_page_handler');

	elgg_register_menu_item('topbar', array(
		'name' => 'administration',
		'href' => 'admin',
		'text' => elgg_view_icon('settings') . elgg_echo('admin'),
		'priority' => 100,
		'section' => 'alt',
	));

	// let demo users know they cannot change site settings or create admin users
	if (!elgg_is_admin_logged_in()) {
		elgg_extend_view('forms/admin/site/update_basic', 'demo/limit', 1);
		elgg_extend_view('forms/admin/site/update_advanced', 'demo/limit', 1);
		elgg_extend_view('forms/useradd', 'demo/limit', 1);
	}

	// let demo users change plugin settings
	elgg_register_action("plugins/settings/save");

	elgg_register_page_handler('reset', 'demo_reset_site');
}

/**
 * Override the default admin page handler
 *
 * @param array $page Array of URL segments
 */
function demo_admin_page_handler($page) {

	gatekeeper();

	elgg_admin_add_plugin_settings_menu();
	elgg_set_context('admin');

	elgg_unregister_css('elgg');
	elgg_load_js('elgg.admin');
	elgg_load_js('jquery.jeditable');

	// default to dashboard
	if (!isset($page[0]) || empty($page[0])) {
		$page = array('dashboard');
	}

	// was going to fix this in the page_handler() function but
	// it's commented to explicitly return a string if there's a trailing /
	if (empty($page[count($page) - 1])) {
		array_pop($page);
	}

	$vars = array('page' => $page);

	// special page for plugin settings since we create the form for them
	if ($page[0] == 'plugin_settings' && isset($page[1]) &&
		(elgg_view_exists("settings/{$page[1]}/edit") || elgg_view_exists("plugins/{$page[1]}/settings"))) {

		$view = 'admin/plugin_settings';
		$plugin = elgg_get_plugin_from_id($page[1]);
		$vars['plugin'] = $plugin;

		$title = elgg_echo("admin:{$page[0]}");
	} else {
		$view = 'admin/' . implode('/', $page);
		$title = elgg_echo("admin:{$page[0]}");
		if (count($page) > 1) {
			$title .= ' : ' . elgg_echo('admin:' .  implode(':', $page));
		}
	}

	// gets content and prevents direct access to 'components' views
	if ($page[0] == 'components' || !($content = elgg_view($view, $vars))) {
		$title = elgg_echo('admin:unknown_section');
		$content = elgg_echo('admin:unknown_section');
	}

	$body = elgg_view_layout('admin', array('content' => $content, 'title' => $title));
	echo elgg_view_page($title, $body, 'admin');
}

/**
 * Start the reset process for a demo site
 */
function demo_reset_site() {
	if ($_SERVER['SERVER_ADDR'] !== $_SERVER['REMOTE_ADDR']) {
		exit;
	}

	$db_prefix = elgg_get_config('dbprefix');

	$tables = get_data("SHOW TABLES LIKE '$db_prefix%'");
	foreach ($tables as $table) {
		foreach ($table as $name) {
			get_data("DROP TABLE IF EXISTS $name");
		}
	}

	$install_url = elgg_normalize_url('mod/demo/install.php');
	file_get_contents($install_url);
	exit;
}
