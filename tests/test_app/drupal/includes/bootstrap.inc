<?php
function drupal_settings_initialize() {
	// Set up dummy values
	$GLOBALS['databases']['default']['default']['prefix'] = 'd_';
	$GLOBALS['cookie_domain'] = $_SERVER['HTTP_HOST'];
}
