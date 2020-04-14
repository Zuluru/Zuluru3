<?php
/**
 * Create a number of commonly-needed blocks that the various layouts can then use as required.
 * This element will not change any of the standard blocks: meta, css, script, content, etc.
 */

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\Routing\Router;

/**
 * Default `html` block.
 */
if (!$this->fetch('html')) {
	$this->start('html');
	echo $this->Html->doctype('html5');
	printf('<html lang="%s">', Configure::read('App.language'));
	$this->end();
}

/**
 * Default `title` block.
 * We don't check whether there is one already, because there is ALWAYS one already, from the default view.
 */
$this->start('title');
$crumbs = $this->Html->getCrumbs(' &raquo; ');
if (!empty($crumbs)) {
	echo $crumbs . ' : ';
}
echo Configure::read('site.name') . ' : ' . Configure::read('organization.name');
$this->end();

/**
 * Some useful info for the `meta` block.
 */
if (!$this->fetch('common_meta')) {
	$this->start('common_meta');
	echo $this->Html->meta('favicon.ico', '/favicon.ico', ['type' => 'icon']);
	echo $this->Html->meta('author', Configure::read('App.author'));
	echo $this->Html->meta('viewport', 'width=device-width, initial-scale=1', ['type' => 'viewport']);
	$this->end();
}

/**
 * Take care of CSS from various sources.
 * To remove some of these, pre-define the related block in your layout before calling this element.
 */
if (!$this->fetch('third_party_css')) {
	$this->start('third_party_css');
	echo $this->Html->css([
		'https://code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.css',
		'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css',
	]);
	$this->end();
}

if (!$this->fetch('zuluru_css')) {
	$this->start('zuluru_css');
	echo $this->Html->css([
		'zuluru/layout.css',
		'zuluru/look.css',
		'pace/themes/pace-theme-minimal',
	]);
	$this->end();
}

$this->start('prepend_css');
$language = Configure::read('personal.language');
if (Configure::read('feature.uls') && empty($language)) {
	echo $this->Html->css([
		'https://zuluru.net/css/uls/jquery.uls.css',
		'https://zuluru.net/css/uls/jquery.uls.grid.css',
		'https://zuluru.net/css/uls/jquery.uls.lcd.css',
	]);
}
echo $this->fetch('third_party_css');
echo $this->fetch('zuluru_css');
$this->end();

$this->start('append_css');
$css = Configure::read('App.additionalCss');
if (!empty($css)) {
	// These files are assumed to come from the normal location, not the Zuluru location.
	// A complete path can always be given, if required.
	echo $this->Html->css($css);
}

/**
 * Append the `html5Shim`.
 */
?>
<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<?php
$this->end();

/**
* Combine all the header pieces together.
 */
$this->start('common_header');
echo implode("\n", [
	$this->fetch('meta'),
	$this->fetch('common_meta'),
	$this->fetch('prepend_css'),
	$this->fetch('css'),
	$this->fetch('append_css'),
]) . "\n";
$this->end();

/**
 * Build the various scripts.
 */
if (!$this->fetch('ajax_scripts')) {
	$this->start('ajax_scripts');
	// Full Pace options at https://github.com/HubSpot/pace/blob/master/pace.coffee
	echo $this->Html->scriptBlock("
paceOptions = {
	startOnPageLoad: false,
	ajax: {
		trackMethods: ['GET', 'POST']
	}
};
");
	echo $this->Html->script(['pace.min.js']);
	$this->end();
}

if (!$this->fetch('jquery_scripts')) {
	$this->start('jquery_scripts');
	echo $this->Html->script([
		'https://code.jquery.com/jquery-1.10.2.js',
		'https://code.jquery.com/ui/1.10.3/jquery-ui.js',
	]);

	// Change jQueryUI plugin names to prevent name collision with Bootstrap.
	echo $this->Html->scriptBlock('
jQuery.noConflict();
jQuery.widget.bridge("uitooltip", jQuery.ui.tooltip);
jQuery.widget.bridge("uibutton", jQuery.ui.button);
');
	$this->end();
}

if (Configure::read('feature.ckeditor') && !$this->fetch('editor_scripts')) {
	$this->start('editor_scripts');
	echo $this->Html->script('https://cdn.ckeditor.com/4.8.0/full/ckeditor.js');
	$this->end();
}

if (!$this->fetch('javascript_variables') && method_exists($this->Html, 'iconImg')) {
	$this->start('javascript_variables');
	$img_path = Configure::read('App.imageBaseUrl');
	if (strpos($img_path, '://') === false) {
		$img_path = $this->Url->webroot(Configure::read('App.imageBaseUrl'));
	}
	$vars = [
		'zuluru_img_path' => $img_path,
		'zuluru_spinner' => $this->Html->iconImg('spinner.gif'),
		'zuluru_popup' => $this->Html->iconImg('popup_16.png', ['class' => 'tooltip_toggle']),
		'zuluru_base' => Router::url('/'),
		'zuluru_mobile' => $this->getRequest()->is('mobile') ? true : false,
		'zuluru_save' => addslashes(__('Save')),
		'zuluru_cancel' => addslashes(__('Cancel')),
		'zuluru_close' => addslashes(__('Close')),
		'zuluru_open_help' => addslashes(__('Open this help page in a new window')),
	];
	if ($this->getRequest()->getParam('_csrfToken')) {
		$vars['zuluru_csrf_token'] = $this->getRequest()->getParam('_csrfToken');
	}

	echo $this->Html->scriptBlock(implode("\n", array_map(function ($var, $value) {
		if (is_bool($value)) {
			return "var $var = " . ($value ? 'true' : 'false') . ';';
		} else if (is_int($value)) {
			return "var $var = $value;";
		}
		return "var $var = '$value';";
	}, array_keys($vars), $vars)));
	$this->end();
}

if (!$this->fetch('bootstrap_scripts')) {
	$this->start('bootstrap_scripts');
	echo $this->Html->script([
		'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js',
	]);
	$this->end();
}

if (!$this->fetch('zuluru_script')) {
	$this->start('zuluru_script');
	echo $this->Html->script([
		'zuluru.js',
	]);
	$this->end();
}

if (!$this->fetch('language_scripts')) {
	$this->start('language_scripts');
	if (Configure::read('feature.uls') && empty($language)) {
		echo $this->Html->script([
			'https://zuluru.net/js/uls/jquery.uls.data.js',
			'https://zuluru.net/js/uls/jquery.uls.data.utils.js',
			'https://zuluru.net/js/uls/jquery.uls.lcd.js',
			'https://zuluru.net/js/uls/jquery.uls.languagefilter.js',
			'https://zuluru.net/js/uls/jquery.uls.core.js',
		]);
		echo $this->Html->scriptBlock('
jQuery(".uls-trigger").uls({
	// Locate the dialog right-aligned with the trigger.
	// TODO: Likely needs to change with RTL languages.
	onVisible : function () {
		var trigger = jQuery(".uls-trigger").first();
		var right = jQuery(window).width() - (trigger.offset().left + trigger.outerWidth());
		jQuery(".uls-menu").css("left", "").css("right", right + "px");
	},
	onSelect : function (language) {
		window.location = "' . $this->Url->build(['controller' => 'All', 'action' => 'language', 'return' => AppController::_return()], true) . '&lang=" + language;
	},
	languages: {' . Configure::read('available_translation_strings') . '}
});
');
	}
	$this->end();
}

/**
 * Combine all the scripts together
 */
$this->start('zuluru_scripts');
echo $this->fetch('ajax_scripts');
echo $this->fetch('jquery_scripts');
echo $this->fetch('editor_scripts');
echo $this->fetch('bootstrap_scripts');
echo $this->fetch('language_scripts');
echo $this->fetch('javascript_variables');
echo $this->fetch('zuluru_script');
$this->end();

/**
 * Default `body` blocks.
 */
$this->prepend('body_attrs', ' class="' . implode(' ', [strtolower($this->getRequest()->getParam('controller')), $this->getRequest()->action]) . '" ');
if (!$this->fetch('body_start')) {
	$this->start('body_start');
	echo '<body' . $this->fetch('body_attrs') . '>';
	$this->end();
}
if (!$this->fetch('body_end')) {
	$this->start('body_end');
	echo '</body>';
	$this->end();
}

if (isset($menu_items)) {
	$this->start('zuluru_menu');
	echo $this->element("Menus/$menu_element", ['menu_items' => $menu_items]);
	$this->end();
}

// Error pages can't do some of these things
if (!isset($error)) {
	$this->start('session_options');

	$profiles = $this->UserCache->allActAs();
	if (!empty($profiles)) {
		echo $this->Jquery->inPlaceWidget($this->UserCache->read('Person.full_name'), [
			'type' => 'profile',
			'url' => [
				'controller' => 'People',
				'action' => 'act_as',
				'return' => AppController::_return(),
			],
		]);

		echo $this->Jquery->inPlaceWidgetOptions($profiles, [
			'type' => 'profile',
			'url' => [
				'controller' => 'People',
				'action' => 'act_as',
				'return' => AppController::_return(),
			],
			'url-param' => 'person',
		], __('Switch to:'));
	}

	if (Configure::read('feature.uls') && empty($language)) {
		echo $this->Html->tag('span', Configure::read('available_translations.' . I18n::getLocale()), ['class' => 'uls-trigger']);
	}
	$this->end();

	$this->start('zuluru_session');
	echo $this->Html->tag('span', $this->fetch('session_options'), ['class' => 'session-options', 'style' => 'float: right;']);
	echo $this->Html->tag('div', '', ['style' => 'clear:both;']);
	$this->end();
}

/**
 * Default `flash` block.
 */
if (!$this->fetch('common_flash')) {
	$this->start('common_flash');
	if (isset($this->Flash)) {
		echo $this->Flash->render();
		echo $this->Flash->render('email');
	}
	$this->end();
}

/**
 * Default `powered by` block.
 */
if (!$this->fetch('powered_by')):
	$this->start('powered_by');
?>
	<p class="small text-center"><?php
		echo __('Powered by {0}, version {1}.{2}.{3}',
			$this->Html->link(ZULURU, 'https://zuluru.org/'),
			ZULURU_MAJOR, ZULURU_MINOR, ZULURU_REVISION) . ' | ';
		$body = htmlspecialchars(__('I found a bug in {0}', Router::url(Router::normalize($this->getRequest()->getRequestTarget()), true)));
		echo __('{0} on this page',
			$this->Html->link(__('Report a bug'), 'mailto:' . Configure::read('email.support_email') . '?subject=' . ZULURU . "%20Bug&body=$body"));
		echo ' | ' . $this->Html->link($this->Html->image('facebook.png'), 'https://facebook.com/Zuluru', ['escape' => false, 'target' => 'facebook']) . ' ' .
			$this->Html->link(__('Follow Zuluru on Facebook'), 'https://facebook.com/Zuluru', ['target' => 'facebook']);
	?></p>
<?php
	$this->end();
endif;

/**
 * Default `footer` block.
 */
if (!$this->fetch('common_footer')):
	$this->start('common_footer');
?>
<footer class="clearfix">
<?php
	echo $this->element('Layout/footer');
	echo $this->fetch('powered_by');
?>
</footer>
<?php
	$this->end();
endif;

/**
 * Build the Zuluru content block.
 */
$this->start('zuluru_content');
echo $this->fetch('zuluru_menu');
echo $this->Html->getCrumbList();
if (!isset($error)) {
	echo $this->fetch('zuluru_session');
	echo $this->cell('Notices::next')->render();
}
echo $this->fetch('common_flash');
echo $this->fetch('content');
echo $this->fetch('common_footer');
$this->end();
