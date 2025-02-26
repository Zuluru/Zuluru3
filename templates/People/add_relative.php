<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add(__('Add Child'));
?>

<div class="people add_relative form">
	<h2><?= __('Add Child') ?></h2>

	<p><?= __('This process is intended for parents to add player profiles for their children. This does not create a login account; the only access to the new profile will be through your account.') ?></p>

	<fieldset class="player">
		<legend><?= __('Player Profile') ?></legend>
<?php
echo $this->Form->create($person, ['align' => 'horizontal']);

// Assume any secondary profiles are players
echo $this->Form->hidden('user_groups.0.id', ['value' => GROUP_PLAYER]);

echo $this->Form->control('first_name', [
	'help' => __('First (and, if desired, middle) name.'),
]);
echo $this->Form->control('last_name');

echo $this->element('People/gender_inputs', ['prefix' => '', 'secure' => true, 'edit' => false]);

if (Configure::read('profile.birthdate')) {
	if (Configure::read('feature.birth_year_only')) {
		echo $this->Form->control('birthdate', [
			'type' => 'year',
			'min' => Configure::read('options.year.born.min'),
			'max' => Configure::read('options.year.born.max'),
			'empty' => '---',
			'help' => __('Please enter a correct birthdate; having accurate information is important for insurance purposes.'),
		]);
	} else {
		echo $this->Form->control('birthdate', [
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
	echo $this->Form->control('height', [
		'size' => 6,
		'help' => __('Please enter your height in {0}. This is used to help build even teams from individual signups.', $units),
	]);
}
if (Configure::read('profile.shirt_size')) {
	echo $this->Form->control('shirt_size', [
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
		echo $this->Form->control('affiliates._ids', [
			'help' => __('Select all affiliates you are interested in.'),
			'multiple' => 'checkbox',
			'hiddenField' => false,
		]);
	} else {
		echo $this->Form->control('affiliates.0.id', [
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
