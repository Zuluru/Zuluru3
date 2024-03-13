<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var array $payment
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Registrations'));
$this->Html->addCrumb($event->name);
$this->Html->addCrumb(__('Summary'));
?>

<div class="registrations summary">
	<h2><?= __('Registration Summary') . ': ' . $event->name ?></h2>

<?php
$rows = [];

if (isset($gender_split)) {
	$title = __('By gender: (does not include cancelled registrations)');
	$column = Configure::read('gender.column');
	foreach ($gender_split as $value) {
		$rows[] = [
			[$title, ['colspan' => 2]],
			$value->$column,
			$value->count,
		];
		$title = '';
	}
}

$title = __('By payment:');
$last_payment = null;
$total = 0;
foreach ($payment as $value) {
	if ($last_payment == $value->payment) {
		$row = [
			$title,
			null,
		];
	} else {
		if ($total != 0 && isset($gender_split)) {
			$rows[] = [
				null,
				null,
				__('Total'),
				$total,
			];
			$total = 0;
		}

		$row = [
			$title,
			$value->payment,
		];
		$last_payment = $value->payment;
	}

	if (isset($gender_split)) {
		$row[] = $value->$column;
	}
	$row[] = $value->count;

	$rows[] = $row;
	$total += $value->count;
	$title = '';
}

if ($total != 0 && isset($gender_split)) {
	$rows[] = [
		null,
		null,
		__('Total'),
		$total,
	];
	$total = 0;
}

echo $this->Html->tag('table', $this->Html->tableCells($rows), ['class' => 'list']);
echo $this->element('Questionnaires/summary');
?>
</div>
<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'format' => 'list']) ?>
</div>
