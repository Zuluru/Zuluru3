<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Teams'));
if ($team->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($team->name));
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="teams form">
	<?= $this->Form->create($team, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Team Details') ?></legend>
<?php
// Unique name validation requires division details
echo $this->Form->hidden('division_id');

echo $this->Form->control('name', [
	'help' => __('The full name of your team.'),
]);
echo $this->Form->control('short_name', [
	'help' => __('A short name for your team, if you have one.'),
]);

if ($team->isNew()) {
	echo $this->Form->control('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}

if (Configure::read('feature.shirt_colour')) {
	echo $this->Form->control('shirt_colour', [
		'help' => __('Shirt colour of your team. If you don\'t have team shirts, pick \'light\' or \'dark\'.'),
	]);
}

echo $this->Jquery->toggleInput('track_attendance', [
	'type' => 'checkbox',
	'help' => __('If selected, the system will help you to monitor attendance on a game-to-game basis.'),
], [
	'selector' => '#AttendanceDetails',
]);
?>
		<fieldset id="AttendanceDetails">
			<legend><?= __('Attendance') ?></legend>
<?php
echo $this->Form->control('attendance_reminder', [
	'size' => 1,
	'help' => __('Reminder emails will be sent to players that have not finalized their attendance this many days before the game. 0 means the day of the game, -1 will disable these reminders.'),
	'secure' => false,
]);
echo $this->Form->control('attendance_summary', [
	'size' => 1,
	'help' => __('Attendance summary emails will be sent to coaches/captains this many days before the game. 0 means the day of the game, -1 will disable these summaries.'),
	'secure' => false,
]);
echo $this->Form->control('attendance_notification', [
	'size' => 1,
	'help' => __('Emails notifying coaches/captains about changes in attendance status will be sent starting this many days before the game. 0 means the day of the game, -1 will disable these notifications. You will never receive notifications about any changes that happen before this time.'),
	'secure' => false,
]);
?>
		</fieldset>
<?php
$options = ($this->Authorize->can('edit_home_field', $team)) + Configure::read('feature.facility_preference') + Configure::read('feature.region_preference');
if ($options):
?>
		<fieldset>
			<legend><?= __('Location') ?></legend>
			<p><?= __('When scheduling games, {0} will look for {1} that match the criteria specified below for the home team{2}. Note that the options available here may change through the season if {1} are added to, or removed from, circulation.',
				ZULURU, Configure::read('UI.fields'), $options > 1 ? __(', from top to bottom') : '') ?></p>

<?php
	if ($this->Authorize->can('edit_home_field', $team)) {
		$fields = [];
		foreach ($facilities as $facility) {
			if (count($facility->fields) > 1) {
				$fields[$facility->name] = [];
				foreach ($facility->fields as $field) {
					$fields[$facility->name][$field->id] = $field['num'];
				}
			} else {
				$fields[$facility->fields[0]->id] = "{$facility->name} {$facility->fields[0]->num}";
			}
		}

		if ($team->division_id) {
			$sport = $team->division->league->sport;
		} else {
			$sport = current(array_keys(Configure::read('options.sport')));
		}
		echo $this->Form->control('home_field_id', [
			'label' => __('Home {0}', __(Configure::read("sports.{$sport}.field_cap"))),
			'help' => __('Home {0}, if applicable.', __(Configure::read("sports.{$sport}.field"))),
			'options' => $fields,
			'empty' => __('No home {0}', __(Configure::read("sports.{$sport}.field"))),
		]);
	}

	if (Configure::read('feature.facility_preference')) {
?>
			<p><?= __('Select the facilities your team would prefer to play at.') ?></p>
<?php
		$facility_options = [];
		foreach ($facilities as $facility) {
			$facility_options[$facility->id] = [
				'value' => $facility->id,
				'text' => $facility->name,
			];
		}
		if (!empty($team->facilities)) {
			foreach ($team->facilities as $facility) {
				if (array_key_exists($facility->id, $facility_options)) {
					$facility_options[$facility->id]['id'] = sprintf("option_%04d", $facility->_joinData->rank);
				}
			}
		}

		echo $this->Form->control('facilities._ids', [
			'label' => __('Facility Preference'),
			'options' => $facility_options,
			'multiple' => true,
			'hiddenField' => false,
			'title' => __('Select your preferred facilities'),
			'secure' => false,
		]);
		$this->Form->unlockField('asmSelect0');
		$this->Form->unlockField('facilities._ids');
		$this->Html->css('jquery.asmselect.css', ['block' => true]);
		$this->Html->script('jquery.asmselect.js', ['block' => true]);
		$this->Html->scriptBlock('zjQuery("select[multiple]").asmSelect({sortable:true});', ['buffer' => true]);
	}

	if (Configure::read('feature.region_preference')) {
?>
			<p><?= __('Select the region where your team would prefer to play.') ?></p>
<?php
		echo $this->Form->control('region_preference', [
			'options' => $regions,
			'empty' => __('No preference'),
		]);
	}
?>
		</fieldset>
<?php
endif;

echo $this->Form->control('open_roster', [
	'help' => __('If the team roster is open, others can request to join; otherwise, only a coach or captain can add players.'),
]);

if (Configure::read('feature.urls')) {
	echo $this->Form->control('website', [
		'help' => __('Your team\'s website, if you have one.'),
	]);
}

if (Configure::read('feature.flickr')) {
	if ($this->Authorize->getIdentity()->isManagerOf($team)) {
		echo $this->Form->control('flickr_ban', [
			'help' => __('If selected, this team\'s Flickr slideshow will no longer be shown. This is for use if teams repeatedly violate this site\'s terms of service.'),
		]);
	} else if ($team->flickr_ban) {
		echo $this->Html->para('warning-message', __('Your team has been banned from using the Flickr slideshow. Contact an administrator if you believe this was done in error or would like to request a review.'));
	}
	if ($this->Authorize->getIdentity()->isManagerOf($team) || !$team->flickr_ban) {
		echo $this->Form->control('flickr_user', [
			'help' => __('The URL for your photo set will be something like https://www.flickr.com/photos/abcdef/sets/12345678901234567/. abcdef is your username.'),
		]);
		echo $this->Form->control('flickr_set', [
			'help' => __('The URL for your photo set will be something like https://www.flickr.com/photos/abcdef/sets/12345678901234567/. 12345678901234567 is your set number.'),
		]);
	}
}

if (Configure::read('feature.twitter')) {
	echo $this->Form->control('twitter_user', [
		'help' => __('Do NOT include the @; it will be automatically added for you.'),
	]);
}
?>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
