<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person[] $officials
 */

$output = [];
foreach ($officials as $official) {
	$output[] = $this->Html->link($official->full_name, ['controller' => 'People', 'action' => 'officiating_schedule', '?' => ['official' => $official->id]]);
}

echo implode (', ', $output);
