<?php
if (!isset($options)) {
	$options = [];
}

// Temporarily store an ID which is unlikely to ever be used in the
// configuration. This allows us to create multiple records.
$unused_id = \App\Lib\fake_id();

$id = false;
$setting = collection($settings)->firstMatch(['category' => $category, 'name' => $name]);
if ($setting) {
	$id = $setting->id;
	$options['value'] = $setting->value;
}

if ($id !== false) {
	echo $this->Form->hidden("$id.id", ['value' => $id]);
} else {
	$id = $unused_id;
	echo $this->Form->hidden("$id.category", ['value' => $category]);
	echo $this->Form->hidden("$id.name", ['value' => $name]);
	if (isset($affiliate) && $affiliate) {
		echo $this->Form->hidden("$id.affiliate_id", ['value' => $affiliate->id]);
	}
}
if (isset($person_id)) {
	echo $this->Form->hidden("$id.person_id", ['value' => $person_id]);
}

if (!array_key_exists('type', $options)) {
	$options['type'] = 'text';
}

if (isset($affiliate) && $affiliate && $options['type'] != 'textarea') {
	$default = collection($defaults)->firstMatch(['category' => $category, 'name' => $name]);
	if ($default) {
		$default_str = $default->value;
		if ($options['type'] == 'radio') {
			$default_str = $options['options'][$default_str];
			$options['options'][MIN_FAKE_ID] = 'Use default';
			$options['default'] = MIN_FAKE_ID;
		} else if ($options['type'] == 'select') {
			$default_str = $options['options'][$default_str];
		}
		if (!empty($default_str)) {
			$default_str = '(' . __('Default') . ": $default_str)";
		} else {
			$default_str = '(' . __('No default.') . ')';
		}
		if (array_key_exists('help', $options)) {
			$options['help'] .= " $default_str";
		} else {
			$options['help'] = $default_str;
		}
	}
}

if ($options['type'] == 'radio') {
	$options['legend'] = false;
} else if ($options['type'] == 'select') {
	if ($setting) {
		$options['selected'] = $setting->value;
	} else if (!empty($default)) {
		$options['selected'] = $default->value;
	}
}

$help_file = APP . 'Template' . DS . 'Element' . DS . 'Help' . DS . 'settings' . DS . $category . DS . $name . '.ctp';
if (file_exists($help_file)) {
	$help = ' ' . $this->Html->help(['action' => 'settings', $category, $name]);
	if (array_key_exists('help', $options)) {
		$options['help'] = $options['help'] . $help;
	} else {
		$options['help'] = $help;
	}
}

if ($options['type'] == 'textarea') {
	$options = array_merge(['cols' => 70, 'rows' => 10], $options);
} else if ($options['type'] == 'text' && !array_key_exists('size', $options)) {
	$options['size'] = 70;
}

if (isset($jquery)) {
	if (isset($affiliate) && $affiliate) {
		$jquery['url']['affiliate'] = $affiliate->id;
	}
	echo $this->Jquery->ajaxInput("$id.value", [
		'selector' => $jquery['selector'],
		'url' => $jquery['url'],
		'param-name' => $name,
	], $options) . "\n";
} else {
	echo $this->Form->input("$id.value", $options) . "\n";
}

// TODO: Is there a fix that could be done in Cake, based on the template, that would eliminate this necessity?
if ($options['type'] == 'date' && !empty($options['templates']['dateWidget'])) {
	foreach (['year', 'month', 'day'] as $subfield) {
		if (strpos($options['templates']['dateWidget'], '{{' . $subfield . '}}') === false) {
			$this->Form->unlockField("$id.value.$subfield");
		}
	}
}
