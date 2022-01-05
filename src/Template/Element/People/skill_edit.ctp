<?php
use Cake\Core\Configure;

// If neither year started nor skill level are enabled, we don't want any output from this.
if (!Configure::read('profile.year_started') && !Configure::read('profile.skill_level')) {
	return;
}

$sports = Configure::read('options.sport');
$admin = Configure::read('email.admin_email');
if (!isset($access)) {
	// New accounts can update all fields
	$access = [1,2];
}
if (!isset($prefix)) {
	$prefix = $id_prefix = '';
} else {
	$id_prefix = str_replace('.', '', $prefix) . '-';
	$prefix .= '.';
}

if (count($sports) > 1):
?>
	<div class="form-group">
		<div class="col-md-6 col-md-offset-2">
			<strong><?= __('Select all sports you will be playing in this league') ?></strong>
		</div>
	</div>

<?php
endif;

$i = 0;
foreach ($sports as $sport => $name):
	if (count($sports) > 1):
?>
	<fieldset>
<?php
		echo $this->Jquery->toggleInput("{$prefix}skills.{$i}.enabled", [
			'type' => 'checkbox',
			'label' => __($name),
			'secure' => false,
		], [
			'selector' => "#{$id_prefix}Skill{$i}Details",
		]);
	else:
		echo $this->Form->hidden("{$prefix}skills.{$i}.enabled", [
			'value' => true,
			'secure' => false,
		]);
		$this->Form->unlockField("{$prefix}skills.{$i}.enabled");
	endif;

	echo $this->Form->hidden("{$prefix}skills.{$i}.sport", [
		'value' => $sport,
	]);
	$this->Form->unlockField("{$prefix}skills.{$i}.sport");
?>
		<div id="<?= $id_prefix ?>Skill<?= $i ?>Details">
<?php
	if (in_array(Configure::read('profile.year_started'), $access)) {
		echo $this->Form->input("{$prefix}skills.{$i}.year_started", [
			'type' => 'year',
			'minYear' => Configure::read('options.year.started.min'),
			'maxYear' => Configure::read('options.year.started.max'),
			'orderYear' => 'desc',
			// The "year" type will interpret a number like 2010 as a UNIX timestamp, and default the selected value here to 1970.
			'value' => (isset($person) && isset($person->skills) && array_key_exists($i, $person->skills)) ? $person->skills[$i]->year_started : null,
			'empty' => '---',
			'help' => __('The year you started playing in <strong>this</strong> league.'),
			'secure' => false,
		]);
	} else if (Configure::read('profile.year_started')) {
		echo $this->Form->input("{$prefix}skills.{$i}.year_started", [
			'disabled' => true,
			'class' => 'disabled',
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('correct year started'), $this->Html->link($admin, "mailto:$admin")),
			'secure' => false,
		]);
	}

	if (in_array(Configure::read('profile.skill_level'), $access)) {
		if (Configure::read("sports.{$sport}.rating_questions")) {
			$after = $this->Html->para(null, __('Please use the questionnaire to ') . $this->Html->link(__('calculate your rating'), '#', ['onclick' => "dorating('$sport', '#{$id_prefix}skills-{$i}-skill-level'); return false;"]) . '.');
		} else {
			$after = $this->Html->para(null, __('This is used to help build teams from individual sign-ups and inform our skills development programming.'));
		}
		echo $this->Form->input("{$prefix}skills.{$i}.skill_level", [
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.skill'),
			'help' => $after,
			'secure' => false,
		]);
	} else if (Configure::read('profile.skill_level')) {
		echo $this->Form->input("{$prefix}skills.{$i}.skill_level", [
			'disabled' => true,
			'class' => 'disabled',
			'size' => 70,
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new skill level'), $this->Html->link($admin, "mailto:$admin")),
			'secure' => false,
		]);
	}
?>
		</div>

<?php
	if (count($sports) > 1):
?>

	</fieldset>
<?php
	endif;
	++ $i;
endforeach;
