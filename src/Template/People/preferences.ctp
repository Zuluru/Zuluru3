<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$this->Html->addCrumb(__('Preferences'));
$this->Html->addCrumb($person->full_name);
?>

<div class="settings form">
<?= $this->Form->create($person, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Preferences') ?></legend>
<?php
echo $this->element('Settings/input', [
	'person_id' => $id,
	'category' => 'personal',
	'name' => 'enable_ical',
	'options' => [
		'label' => __('Enable Personal iCal Feed'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => $this->Html->tag('span', __('NOTE: By enabling this, you agree to make your personal schedule in iCal format available as public information (required for Google Calendar, etc. to be able to access the data.)'), ['class' => 'highlight-message']),
	],
]);

echo $this->element('Settings/input', [
	'person_id' => $id,
	'category' => 'personal',
	'name' => 'attendance_emails',
	'options' => [
		'label' => __('Always Send Attendance Reminder Emails'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Turn this on if you want to receive reminder emails (with game information) for games that you have already indicated your attendance for. Turn off if you only want emails when you have not yet set your attendance.') . ' ' .
				$this->Html->tag('span', __('NOTE: This applies only to teams with attendance tracking enabled.'), ['class' => 'highlight-message']),
	],
]);

$now = FrozenTime::now();

$options = ['' => __('use system default')];
foreach (Configure::read('options.date_formats') as $format) {
	$options[$format] = $this->Time->format($now, $format);
}
echo $this->element('Settings/input', [
	'person_id' => $id,
	'category' => 'personal',
	'name' => 'date_format',
	'options' => [
		'label' => __('Date Format'),
		'type' => 'radio',
		'options' => $options,
		'help' => __('Select your preferred date format'),
	],
]);

$options = ['' => __('use system default')];
foreach (Configure::read('options.day_formats') as $format) {
	$options[$format] = $this->Time->format($now, $format);
}
echo $this->element('Settings/input', [
	'person_id' => $id,
	'category' => 'personal',
	'name' => 'day_format',
	'options' => [
		'label' => __('Day Format'),
		'type' => 'radio',
		'options' => $options,
		'help' => __('Select your preferred day format'),
	],
]);

$options = ['' => __('use system default')];
foreach (Configure::read('options.time_formats') as $format) {
	$options[$format] = $this->Time->format($now, $format);
}
echo $this->element('Settings/input', [
	'person_id' => $id,
	'category' => 'personal',
	'name' => 'time_format',
	'options' => [
		'label' => __('Time Format'),
		'type' => 'radio',
		'options' => $options,
		'help' => __('Select your preferred time format'),
	],
]);

$languages = Configure::read('available_translations');
if (Configure::read('feature.language') && count($languages) > 1) {
	echo $this->element('Settings/input', [
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'language',
		'options' => [
			'label' => __('Preferred Language'),
			'type' => 'select',
			'options' => $languages,
			'empty' => __('use system default'),
		],
	]);
}

if (Configure::read('feature.twitter')):
?>
		<fieldset>
			<legend><?= __('Twitter') ?></legend>
<?php
	if (!empty($person['twitter_token'])) {
		echo $this->Html->para(null, __('You have authorized your account to post updates to Twitter. You can {0} if you no longer want to tweet updates.',
			$this->Html->link(__('revoke this authorization'), ['action' => 'revoke_twitter'])
		));
	} else {
		echo $this->Html->para(null, __('This system can post certain updates to Twitter on your behalf. To enable this, you must {0}. Note that nothing will ever be tweeted automatically; this authorization enables you to tweet directly from this site.',
			$this->Html->link(__('authorize Twitter to accept these tweets'), ['action' => 'authorize_twitter'])
		));
	}
?>
		</fieldset>
<?php
endif;
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
