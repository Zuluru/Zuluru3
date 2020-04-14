<?php
if (array_key_exists('opts', $item)) {
	$opts = $item['opts'];
} else {
	$opts = [];
}
$opts['escape'] = false;
$divider = $this->Html->tag('li', '', ['role' => 'separator', 'class' => 'divider']);

$name = $item['name'];
//$short_name = $this->Text->truncate($name, 18);
$short_name = $name;
if ($short_name != $name) {
	$opts['title'] = $name;
}

if (array_key_exists('url', $item)) {
	$url = $this->Url->build($item['url']);
	$link = $item['url'];
} else {
	$url = false;
	$link = '#';
}

$classes = [];
if ($this->getRequest()->getRequestTarget() == $url) {
	$classes[] = 'active';
	$short_name .= ' ' . $this->Html->tag('span', '(current)', ['class' => 'sr-only']);
}

if (array_key_exists('items', $item) && !empty($item['items'])) {
	$subs = '';
	foreach ($item['items'] as $sub_item)
	{
		$subs .= $this->element('Menus/bootstrap_item', ['item' => $sub_item, 'in_sub' => true]);
	}

	if (!isset($in_sub)) {
		$classes[] = 'dropdown';
		$short_name .= ' ' . $this->Html->tag('span', '', ['class' => 'caret']);
		$opts += [
			'class' => 'dropdown-toggle',
			'data-toggle' => 'dropdown',
			'role' => 'button',
			'aria-haspopup' => 'true',
			'aria-expanded' => 'false',
		];

		$pre_content = '';
		$post_content = $this->Html->tag('ul', $subs, ['class' => 'dropdown-menu']);
	} else {
		$classes[] = 'menu_section';
		$pre_content = $divider;
		$post_content = $subs;
	}
} else {
	$pre_content = $post_content = '';
}

$content = $this->Html->link($short_name, $link, $opts) . $post_content;

$options = [];
if (!empty($classes)) {
	$options['class'] = implode(' ', $classes);
}

echo $pre_content . $this->Html->tag('li', $content, $options) . "\n";
