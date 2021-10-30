<?php
use Cake\Core\Configure;

if (isset($field)) {
	$this->Html->addCrumb($field->long_name);
}
$this->Html->addCrumb(__('Game Slots'));
$this->Html->addCrumb(__('Create'));
?>

<div class="gameSlots form">
	<?= $this->Form->create($game_slot, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?php
		echo __('Add Game Slots');
		if (isset($field)) {
			echo ': ' . $field->long_name;
		}
		?></legend>
<?php
if (isset($field)):
	echo $this->Form->hidden("fields.{$field->id}", ['value' => 1]);
	echo $this->Form->hidden('sport', ['id' => 'sport', 'value' => $field->sport]);
else:
?>
		<fieldset class="no-labels">
			<legend><?= __('{0} Selection', Configure::read('UI.field_cap')) ?></legend>
			<p class="warning-message"><?= __('NOTE: By default, checking a facility here will create game slots for ALL open {0} at that facility.', Configure::read('UI.fields')) . ' ' .
				__('If you want to create game slots for selected {0}, click the facility name to see the list of {0} at that facility.', Configure::read('UI.fields')) ?></p>
<?php
	$sports = Configure::read('options.sport');
	if (count($sports) > 1) {
		echo $this->element('selector', [
			'title' => 'Sport',
			'options' => $sports,
			'include_form' => false,
			'include_empty' => false,
			'data' => [
				'selector' => '#DivisionList',
				'url' => ['controller' => 'Divisions', 'action' => 'select', 'affiliate' => $affiliate],
				// The JavaScript will automatically pull in the month and year inputs along with the day
				'additional-inputs' => '[name="game_date[day]"], input:checked[name="days[]"]',
			],
		]);
	} else {
		echo $this->Form->hidden('sport', ['id' => 'sport', 'value' => current(array_keys($sports))]);
	}
?>
			<div class="actions columns clear-float">
				<ul class="nav nav-pills">
<?php
	foreach ($regions as $key => $region):
		$ids = collection($region->facilities)->extract('fields.{*}.id')->toList();
		if (empty($ids)) {
			unset($regions[$key]);
			continue;
		}
		$region_sports = array_unique(collection($region->facilities)->extract('fields.{*}.sport')->toList());

		echo $this->Html->tag('li',
			$this->Jquery->toggleLink($region->name, "#region{$region->id}", [
				'class' => $this->element('selector_classes', ['title' => 'Sport', 'options' => $region_sports]),
			], [
				'toggle_text' => true,
			])
		);
	endforeach;
?>
				</ul>
			</div>

<?php
	foreach ($regions as $region):
		$region_sports = array_unique(collection($region->facilities)->extract('fields.{*}.sport')->toList());
?>
			<fieldset id="region<?= $region->id ?>" class="<?= $this->element('selector_classes', ['title' => 'Sport', 'options' => $region_sports]) ?>">
				<legend><?= __($region->name) ?></legend>
				<div class="columns">
					<div class="actions">
						<ul class="nav nav-pills">
<?php
		// TODOBOOTSTRAP: This pushes the first facility checkbox to the right
		echo $this->Html->tag('li', $this->Jquery->selectAll("#region{$region->id}"));
?>
						</ul>
					</div>
<?php
		foreach ($region->facilities as $facility):
			$facility_sports = array_unique(collection($facility->fields)->extract('sport')->toList());
?>
					<div style="clear: both;" class="<?= $this->element('selector_classes', ['title' => 'Sport', 'options' => $facility_sports]) ?>">
<?php
			if (count($facility->fields) == 1):
				$field = current($facility->fields);
				echo $this->Form->input("fields.{$field->id}", [
					'label' => $facility->name,
					'type' => 'checkbox',
					'hiddenField' => false,
				]);
			else:
				echo $this->Form->input("facilities.{$facility->id}", [
					'label' => [
						'text' => $this->Jquery->toggleLink($facility->name, "#Facility{$facility->id}Fields"),
						'escape' => false,
					],
					'type' => 'checkbox',
					'hiddenField' => false,
					'class' => 'zuluru_select_all',
					'data-selector' => "#Facility{$facility->id}Fields",
				]);
?>
						<div id="<?= "Facility{$facility->id}Fields" ?>" style="display: none; margin-left: 25px;">
<?php
				foreach ($facility->fields as $field):
					echo $this->Html->tag('span',
						$this->Form->input("fields.{$field->id}", [
							'label' => $field->num,
							'class' => $this->element('selector_classes', ['title' => 'Sport', 'options' => $field->sport]),
							'type' => 'checkbox',
							'hiddenField' => false,
						]),
						['class' => $this->element('selector_classes', ['title' => 'Sport', 'options' => $field->sport])]
					);
				endforeach;
?>

						</div>
<?php
			endif;
?>

					</div>
<?php
		endforeach;
?>

				</div>
			</fieldset>
<?php
	endforeach;
	$this->Form->unlockField('facilities');
	$this->Form->unlockField('fields');
?>
		</fieldset>
<?php
endif;
?>
		<legend><?= __('Game slot details') ?></legend>
<?php
echo $this->Form->input('game_start', [
	'label' => __('Start Time'),
	'empty' => '---',
	'help' => __('Time for games to start.'),
]);
echo $this->Form->input('length', [
	'label' => __('Slot length'),
	'options' => Configure::read('options.game_lengths'),
	'help' => __('Length of game slot (in minutes), including buffer time below. If you want only a single game slot, leave this at 0 and just set start and end times.'),
]);
echo $this->Form->input('buffer', [
	'label' => __('Game Buffer'),
	'options' => Configure::read('options.game_buffers'),
	'help' => __('Buffer between games (in minutes). If slot length is 0 above, this is ignored.'),
]);
echo $this->Form->input('game_end', [
	'label' => __('End Time'),
	'empty' => '---',
	'help' => __('Time for games to end. Choose "---" to assign the default time cap (dark) for that week (not available if slot length is set above).'),
]);
echo $this->Jquery->ajaxInput('game_date', [
	'selector' => '#DivisionList',
	'url' => ['controller' => 'Divisions', 'action' => 'select', 'affiliate' => $affiliate],
	'additional-inputs' => '#sport, input:checked[name="days[]"]',
], [
	'label' => __('First Date'),
	'minYear' => Configure::read('options.year.gameslot.min'),
	'maxYear' => Configure::read('options.year.gameslot.max'),
	'help' => __('Date of the first game slot to add.'),
]);
// TODO: Include this only if there are existing divisions, open or opening in the future, which operate on multiple days
// TODO: Check that the JS works when this isn't the case.
echo $this->Form->input('days', [
	'label' => __('Days to Include', true),
	'multiple' => 'checkbox',
	'options' => $days,
	'val' => [\Cake\I18n\FrozenDate::now()->format('N')],
	'help' => __('Create the requested game slots on each of these days in each week.'),
]);
echo $this->Form->input('weeks', [
	'label' => __('Weeks to Repeat'),
	'options' => array_combine($r = range(1, 26), $r),
	'help' => __('Number of weeks to repeat this game slot.'),
]);
?>
		<fieldset>
			<legend><?= __('Make Game Slot Available To') ?></legend>
			<div id="DivisionList">
<?php
if (empty($divisions)) {
	echo __('No divisions operate on the selected night.');
} else {
	echo $this->Form->input('divisions._ids', [
		'label' => false,
		'multiple' => 'checkbox',
		'hiddenField' => false,
	]);
}
$this->Form->unlockField('divisions._ids');
?>
			</div>
		</fieldset>
	</fieldset>
<?php
echo $this->Form->button(__('Continue'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>

<?php
$this->Html->scriptBlock("
zjQuery('[name^=\"game_date\"]').on('change', function (){
	// If there's only a single day checkbox checked, change it to match the new date
	var d = new Date(zjQuery('[name=\"game_date[year]\"]').val(), zjQuery('[name=\"game_date[month]\"]').val() - 1, zjQuery('[name=\"game_date[day]\"]').val());
	if (zjQuery('input:checked[name=\"days[]\"]').length == 1) {
		zjQuery('input:checked[name=\"days[]\"]').prop('checked', false);
	}
	zjQuery('#days-' + ((d.getDay() + 6) % 7 + 1)).prop('checked', true);
});
", ['buffer' => true]);
