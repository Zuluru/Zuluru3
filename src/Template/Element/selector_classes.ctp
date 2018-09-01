<?php
use Cake\Utility\Text;

$id = strtolower(Text::slug($title, '_'));
$new_options = ['selector_' . $id];
if (!is_array($options)) {
	$options  = [$options];
}
foreach ($options as $option) {
	$new_options[] = $id . '_' . strtolower(Text::slug($option, '_'));
}
echo implode(' ', $new_options);
