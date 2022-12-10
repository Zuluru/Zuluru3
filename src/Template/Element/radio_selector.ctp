<?php
/**
 * @type $this \App\View\AppView
 * @type $slug string
 * @type $title string
 * @type $options array
 */

use Cake\Utility\Text;

// Some things that we might group by don't have the thing we're grouping by,
// e.g. not all events have sports or seasons. Eliminate any blank option.
if (current($options) == '') {
	array_shift($options);
}

if (count($options) > 1):
?>
<form class="selector">
<span class="selector">
<?php
	$name = strtolower(Text::slug($title, '_'));
	$new_options = [];
	foreach ($options as $option) {
		$new_options[strtolower(Text::slug($option, '_'))] = $option;
	}

	$input_options = [
		'id' => "{$slug}_{$name}",
		'label' => false,
		'type' => 'radio',
		'options' => $new_options,
	];

	echo $this->Form->input($name, $input_options);
?>
</span>
</form>
<?php
endif;
