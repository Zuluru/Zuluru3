<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;

$this->Html->addCrumb(__('Game Slots'));
$this->Html->addCrumb(__('Confirm'));
?>

<div class="gameSlots form">
	<?= $this->Form->create($game_slot, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Confirm Game Slots') ?></legend>
<?php
// The last form's fields need to be carried through as hidden fields
$hidden = $this->request->data;
// ...and one new field
$hidden['confirm'] = true;
echo $this->element('hidden', ['fields' => $hidden]);

if (!empty($skipped)) {
	echo $this->Html->para(null, __('Game slots will not be created on the following holidays:') .
		$this->Html->nestedList(
			array_map(function ($key, $val) { return \App\View\Helper\ZuluruTimeHelper::date(new FrozenDate($key)) . ': ' . $val; }, array_keys($skipped), $skipped)
		)
	);
}

if (isset($field)):
	echo $this->element('GameSlots/confirm', ['facility' => $field->facility, 'field' => $field, 'weeks' => $weeks, 'times' => $times, 'expanded' => true]);
else:
?>
		<p><?= __('Click a {0} name below to edit the list of game slots that will be created for that {0}.', Configure::read('UI.field')) ?></p>
		<ul>
<?php
	foreach ($regions as $region) {
		foreach ($region->facilities as $facility) {
			foreach ($facility->fields as $field) {
				if (array_key_exists($field->id, $game_slot->fields)) {
					echo $this->element('GameSlots/confirm', compact('facility', 'field', 'weeks', 'times'));
				}
			}
		}
	}
?>
		</ul>
<?php
endif;
?>
	</fieldset>
<?php
echo $this->Form->button(__('Create Slots'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
