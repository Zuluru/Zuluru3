<?php
/**
 * @var \App\View\AppView $this
 * @var array $item
 * @var bool $in_sub
 */

$item += ['opts' => [], 'url' => false, 'items' => []];
$linkOpts = $item['opts'];
$linkOpts['escape'] = false;
$divider = $this->Html->tag('li', $this->Html->tag('hr', null, ['class' => 'dropdown-divider']));

$name = $item['name'];

if ($item['url']) {
	$url = $this->Url->build($item['url']);
	$link = $item['url'];
} else {
	$url = false;
	$link = '#';
}

$itemClasses = [];
$pre_content = $post_content = '';
if (!empty($item['items'])) {
    $subs = '';
    foreach ($item['items'] as $sub_item) {
        $subs .= $this->element('Menus/bootstrap_item', ['item' => $sub_item, 'in_sub' => true]);
    }

    if (!isset($in_sub)) {
        $itemClasses[] = 'nav-item';
        $itemClasses[] = 'dropdown';
        $id = 'navbar' . \Cake\Utility\Inflector::camelize($name);
        //$name .= ' ' . $this->Html->tag('span', '', ['class' => 'caret']);
        $linkOpts += [
            'class' => 'nav-link dropdown-toggle',
            'id' => $id,
            'data-bs-toggle' => 'dropdown',
            'role' => 'button',
            'aria-expanded' => 'false',
        ];

        $post_content = $this->Html->tag('ul', $subs, ['class' => 'dropdown-menu', 'aria-labelledby' => $id]);
    } else {
        $linkOpts['class'] = 'dropdown-item';
        $pre_content = $divider;
        $post_content = $subs;
    }
} else if (!isset($in_sub)) {
    $itemClasses[] = 'nav-item';
    $linkOpts['class'] = 'nav-link';
} else {
    $linkOpts['class'] = 'dropdown-item';
}

if ($this->getRequest()->getRequestTarget() === $url) {
    $linkOpts['class'] .= ' active';
    $name .= ' ' . $this->Html->tag('span', '(current)', ['class' => 'visually-hidden']);
}

$content = $this->Html->link($name, $link, $linkOpts) . $post_content;

$options = [];
if (!empty($itemClasses)) {
	$options['class'] = implode(' ', $itemClasses);
}

echo $pre_content . $this->Html->tag('li', $content, $options) . "\n";
