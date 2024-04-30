<?php
/**
 * @var \App\View\AppView $this
 */
?>
<h1><?= __('Credits') ?></h1>
<p><?= __('{0} is written and maintained by {1}.',
	$this->Html->link(ZULURU, 'https://zuluru.org/'),
	$this->Html->link('Greg Schmidt', 'https://zuluru.net/')
) ?></p>
<?php
$plugins = [
	'Anuj Sharma' => [
		'CakePHP-App-Installer' => 'https://github.com/anuj9196/CakePHP-App-Installer',
	],
	'Commerceguys' => [
		'addressing' => 'https://github.com/commerceguys/addressing',
		'intl' => 'https://github.com/commerceguys/intl',
	],
	'Firebase' => [
		'php-jwt' => 'https://github.com/firebase/php-jwt',
	],
	'Flavien Beninca' => [
		'cakephp-cors' => 'https://github.com/ozee31/cakephp-cors',
	],
	'Frank de Jonge' => [
		'flysystem-aws-s3-v3' => 'https://github.com/thephpleague/flysystem-aws-s3-v3',
	],
	'Friends of Cake' => [
		'bootstrap-ui' => 'https://github.com/friendsofcake/bootstrap-ui',
	],
	'Jad Bitar' => [
		'Footprint' => 'https://github.com/UseMuffin/Footprint',
	],
	'Jevon Wright' => [
		'html2text' => 'https://github.com/soundasleep/html2text',
	],
	'Jose Diaz-Gonzalez' => [
		'cakephp-upload' => 'https://github.com/FriendsOfCake/cakephp-upload',
		'php-dotenv' => 'https://github.com/josegonzalez/php-dotenv',
	],
	'Joshua Gigg' => [
		'libphonenumber-for-php' => 'https://github.com/giggsey/libphonenumber-for-php',
	],
	'Mark Scherer' => [
		'cakephp-ajax' => 'https://github.com/dereuromark/cakephp-ajax',
		'cakephp-calendar' => 'https://github.com/dereuromark/cakephp-calendar',
	],
	'Òscar Casajuana' => [
		'twbs-cake-plugin' => 'https://github.com/elboletaire/twbs-cake-plugin',
	],
	'Serban Ghita' => [
		'mobile-detect' => 'https://github.com/serbanghita/Mobile-Detect',
	],
	'Symfony' => [
		'validator' => 'https://github.com/symfony/validator',
		'yaml' => 'https://github.com/symfony/yaml',
	],
	'Timothy Boronczyk' => [
		'localization-middleware' => 'https://github.com/tboronczyk/localization-middleware',
	],
	'Trent Richardson' => [
		'cakephp-scheduler' => 'https://github.com/trentrichardson/cakephp-scheduler',
	],
];
$plugin_links = array_map(function ($name, $urls) {
	$name_links = array_map(function ($name, $url) {
		return $this->Html->link($name, $url);
	}, array_keys($urls), $urls);
	return $name . __(' ({0})', implode(', ', $name_links));
}, array_keys($plugins), $plugins);
?>
<p><?= __('It is written in {0}, built on the {1}, and uses plugins from {2}. Unit testing is done with Sebastian Bergmann\'s {3} using the {4} plugin by Juan Pablo Ramirez and Nicolas Masson.',
	$this->Html->link('PHP', 'https://php.net/'),
	$this->Html->link(__('CakePHP framework'), 'https://cakephp.org/'),
	\Cake\Utility\Text::toList($plugin_links),
	$this->Html->link('PHPUnit', 'https://phpunit.de/'),
	$this->Html->link('CakePHP Fixture Factories', 'https://github.com/vierge-noire/cakephp-fixture-factories')
) ?></p>
<p><?= __('Zuluru is based in part on {0}, originally written by Dave O\'Neill, with contributions from Mackenzie King (bug fixes), Tony Argentina (player rating system, ladder-based scheduling), Dan Cardamore, Greg Schmidt (registration system, Google Maps layout editor) and Richard Krueger (iCal integration).',
	$this->Html->link('Leaguerunner', 'https://github.com/dave0/leaguerunner')
) ?></p>
<p><?= __('Contributions to the Zuluru codebase have been made by Mateusz Bocian (user interface), Rizwan Jiwan (unit testing), and Rick St. Jean and R.T. Lechow (Docker setup).') ?></p>
<p><?= __('Feature suggestions and other inspiration have come from sources too numerous to remember.') ?></p>
