<?php
/**
 * @var \App\Model\Entity\Badge $badge
 */

$id = "badges_badge_{$badge->id}";

if (isset($options)) {
	$options = array_merge(['id' => $id, 'class' => 'trigger'], $options);
} else {
	$options = ['id' => $id, 'class' => 'trigger'];
}
if (isset($max_length)) {
	$options['max_length'] = $max_length;
}
if (!isset($use_name) || !$use_name) {
	if (!isset($size)) {
		$size = '24';
	}
	$link = $this->Html->iconImg("{$badge->icon}_$size.png");
} else {
	$link = $badge->name;
}

echo $this->Html->link($link,
	['controller' => 'Badges', 'action' => 'view', 'badge' => $badge->id],
	$options);
