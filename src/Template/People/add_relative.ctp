<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb(__('Add Child'));
?>

<div class="people add_relative form">
	<h2><?= __('Add Child') ?></h2>

	<p><?= __('This process is intended for parents to add player profiles for their children. This does not create a login account; the only access to the new profile will be through your account.') ?></p>

	<fieldset class="player">
		<legend><?= __('Player Profile') ?></legend>
<?php
echo $this->Form->create($person, ['align' => 'horizontal']);

// Assume any secondary profiles are players
echo $this->Form->hidden('groups.0.id', ['value' => GROUP_PLAYER]);

echo $this->Form->input('first_name', [
	'help' => __('First (and, if desired, middle) name.'),
]);
echo $this->Form->input('last_name');

echo $this->element('People/gender_inputs', ['prefix' => '', 'secure' => true, 'edit' => false]);

if (Configure::read('profile.birthdate')) {
	if (Configure::read('feature.birth_year_only')) {
		echo $this->Form->input('birthdate', [
			'templates' => [
				'dateWidget' => '{{year}}',
				// Change the input container template, removing the "date" class, so it doesn't get a date picker added
				'inputContainer' => '<div class="form-group {{required}}">{{content}}</div>',
				'inputContainerError' => '<div class="form-group {{required}} has-error">{{content}}</div>',
			],
			'minYear' => Configure::read('options.year.born.min'),
			'maxYear' => Configure::read('options.year.born.max'),
			'empty' => '---',
			'help' => __('Please enter a correct birthdate; having accurate information is important for insurance purposes.'),
		]);
		echo $this->Form->hidden('birthdate.month', ['value' => 1]);
		echo $this->Form->hidden('birthdate.day', ['value' => 1]);
	} else {
		echo $this->Form->input('birthdate', [
			'minYear' => Configure::read('options.year.born.min'),
			'maxYear' => Configure::read('options.year.born.max'),
			'empty' => '---',
			'help' => __('Please enter a correct birthdate; having accurate information is important for insurance purposes.'),
		]);
	}
}
if (Configure::read('profile.height')) {
	if (Configure::read('feature.units') == 'Metric') {
		$units = __('centimeters');
	} else {
		$units = __('inches (5 feet is 60 inches; 6 feet is 72 inches)');
	}
	echo $this->Form->input('height', [
		'size' => 6,
		'help' => __('Please enter your height in {0}. This is used to help build even teams from individual signups.', $units),
	]);
}
if (Configure::read('profile.shirt_size')) {
	echo $this->Form->input('shirt_size', [
		'type' => 'select',
		'empty' => '---',
		'options' => Configure::read('options.shirt_size'),
		'help' => __('This information may be used by the league or your team captain to order shirts/jerseys.'),
	]);
}
echo $this->element('People/skill_edit');
?>
	</fieldset>
<?php
if (Configure::read('feature.affiliates')):
?>
	<fieldset>
		<legend><?= __('Affiliate') ?></legend>
<?php
	if (Configure::read('feature.multiple_affiliates')) {
		echo $this->Form->input('affiliates._ids', [
			'help' => __('Select all affiliates you are interested in.'),
			'multiple' => 'checkbox',
			'hiddenField' => false,
		]);
	} else {
		echo $this->Form->input('affiliates.0.id', [
			'label' => __('Affiliate'),
			'options' => $affiliates,
			'type' => 'select',
			'empty' => '---',
		]);
	}
?>
	</fieldset>
<?php
endif;

echo $this->Form->button(__('Submit and save'), ['class' => 'btn-success', 'name' => 'action', 'value' => 'create']);
echo $this->Form->button(__('Save and add another child'), ['class' => 'btn-success', 'name' => 'action', 'value' => 'continue']);
echo $this->Form->end();
?>
</div>

<?php
if (Configure::read('profile.skill_level')) {
	$sports = Configure::read('options.sport');
	foreach (array_keys($sports) as $sport) {
		if (Configure::read("sports.{$sport}.rating_questions")) {
			echo $this->element('People/rating', ['sport' => $sport]);
		}
	}
}
