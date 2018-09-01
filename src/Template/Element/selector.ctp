<?php
use Cake\Utility\Text;

// Some things that we might group by don't have thing thing we're grouping by,
// e.g. not all events have sports or seasons. Eliminate any blank option.
if (current($options) == '') {
	array_shift($options);
}

if (count($options) > 1):
	if (!isset($include_form) || $include_form):
?>
<form class="selector form-inline">
<?php
	endif;
?>
<span class="selector">
<?php
	$id = strtolower(Text::slug($title, '_'));
	$new_options = [];
	foreach ($options as $option) {
		$new_options[strtolower(Text::slug($option, '_'))] = $option;
	}

	$input_options = [
		'id' => $id,
		'label' => __($title) . ':',
		'options' => $new_options,
	];
	if (!isset($include_empty) || $include_empty) {
		$input_options['empty'] = __('Show All');
	}
	if (isset($data)) {
		// Selectors might also be Ajax inputs
		echo $this->Jquery->ajaxInput($id, $data, $input_options);
	} else {
		echo $this->Form->input($id, $input_options);
	}
?>
</span>
<?php
	if (!isset($include_form) || $include_form):
?>
</form>
<?php
	endif;
endif;
