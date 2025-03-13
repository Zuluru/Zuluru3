<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;
?>

<h4><?= __('Type: Data') ?></h4>
<p><?= __('The {0} rule extracts information from the player record and returns it. The name of the attribute to be returned must be in lower case and enclosed in quotes.', 'ATTRIBUTE') ?></p>
<p><?= __('The most common attributes to use in comparisons are {0} and {1}, but any field name in the "people" table is an option.', [Configure::read('gender.column'), 'birthdate']) ?></p>
<p><?= __('The complete list is as follows. Where there is a limited list of options, they are given in parentheses; note that these are case-sensitive.') ?></p>
<p><?php
$fields = [];
$people_table = TableRegistry::getTableLocator()->get('People');
foreach ($people_table->getSchema()->columns() as $key) {
	$include = false;

	// Check for entirely disabled features
	// TODO: Centralize checking of profile fields
	$feature_lookup = [
		'has_dog' => 'dog_questions',
		'show_gravatar' => 'gravatar',
	];
	if (!array_key_exists($key, $feature_lookup) || Configure::read("feature.{$feature_lookup[$key]}")) {
		// Deal with special cases
		$short_field = strtr($key, ['publish_' => '', 'alternate_' => '']);
		if (in_array($short_field, ['id', 'status', 'email'])) {
			$include = true;
		} else if ($short_field == 'work_ext') {
			$include = Configure::read('profile.work_phone');
		} else if ($short_field == 'roster_designation') {
			$include = (Configure::read('gender.column') == 'roster_designation');
		} else {
			$include = Configure::read("profile.$short_field");
		}
	}

	if ($include) {
		if (strpos($key, '_id') !== false) {
			$model = Inflector::classify(substr($key, 0, strlen($key) - 3));
			$list = $people_table->$model->find('list');
			$options = [];
			foreach ($list as $list_key => $list_value) {
				$options[] = "'$list_key' " . __('for') . ' ' . __($list_value);
			}
		} else {
			$options = Configure::read("options.$key");
		}
		if (!empty($options)) {
			$fields[] = $key . __(' ({0})', implode(', ', $options));
		} else {
			$fields[] = $key;
		}
	}
}
echo implode(', ', $fields);
?></p>
<p><?= __('Example:') ?></p>
<pre>ATTRIBUTE('<?= Configure::read('gender.column') ?>')</pre>
<p><?= __('will return either {0} or {1}.',
	[$this->Html->tag('strong', Configure::read('gender.woman')), $this->Html->tag('strong', Configure::read('gender.man'))]
) ?></p>
