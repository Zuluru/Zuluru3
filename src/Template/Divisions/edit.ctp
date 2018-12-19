<?php
use Cake\Core\Configure;
use App\Core\ModuleRegistry;

$this->Html->addCrumb(__('Divisions'));
if ($division->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($division->league->name));
	if (!empty($division->name)) {
		$this->Html->addCrumb($division->name);
	}
	$this->Html->addCrumb(__('Edit'));
}

// Set up the templates to use for advanced options. Note that these are based on the horizontal
// template set from the Bootstrap FormHelper, not the default templates from Bootstrap FormHelper
// or default templates from CakePHP FormHelper.
$advanced = [
	'inputContainer' => '<div class="form-group advanced {{type}}{{required}}">{{content}}</div>',
	'inputContainerError' => '<div class="form-group advanced {{type}}{{required}} has-error">{{content}}</div>',
];
?>

<div class="divisions form">
	<?= $this->Form->create($division, ['align' => 'horizontal']) ?>
	<p><?= $this->Jquery->toggleLinkPair(
		$this->Html->iconImg('gears_32.png', ['style' => 'vertical-align:middle; padding-right: 5px;']) . ' ' . __('Show advanced configuration'),
		'basic',
		$this->Html->iconImg('gear_32.png', ['style' => 'vertical-align:middle; padding-right: 5px;']) . ' ' . __('Show basic configuration'),
		'advanced',
		[ 'escape' => false ]
	) ?></p>
	<fieldset>
		<legend><?= __('Division Information') ?></legend>
<?php
echo $this->Form->hidden('league_id');
echo $this->Form->input('name', [
	'size' => 70,
	'help' => __('The name of the division.'),
]);
echo $this->Form->input('coord_list', [
	'templates' => $advanced,
	'label' => __('Coordinator Email List'),
	'size' => 70,
	'help' => __('An email alias for all coordinators of this division (can be a comma separated list of individual email addresses).'),
]);
echo $this->Form->input('capt_list', [
	'templates' => $advanced,
	'label' => __('Coach/Captain Email List'),
	'size' => 70,
	'help' => __('An email alias for all coaches and captains of this division.'),
]);
echo $this->Form->input('header', [
	'templates' => $advanced,
	'cols' => 70,
	'rows' => 5,
	'help' => __('A short blurb to be displayed at the top of schedule and standings pages, HTML is allowed.'),
	'class' => 'wysiwyg_advanced',
]);
echo $this->Form->input('footer', [
	'templates' => $advanced,
	'cols' => 70,
	'rows' => 5,
	'help' => __('A short blurb to be displayed at the bottom of schedule and standings pages, HTML is allowed.'),
	'class' => 'wysiwyg_advanced',
]);
?>
	</fieldset>
	<fieldset>
		<legend><?= __('Dates') ?></legend>
<?php
echo $this->Form->input('open', [
	'label' => __('First Game'),
	'empty' => '---',
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => !$division->isNew(),
	'help' => __('Date of the first game in the schedule. Will be used to determine open/closed status.'),
]);
echo $this->Form->input('close', [
	'label' => __('Last Game'),
	'empty' => '---',
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => !$division->isNew(),
	'help' => __('Date of the last game in the schedule. Will be used to determine open/closed status.'),
]);
echo $this->Form->input('roster_deadline', [
	'empty' => '---',
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => !$division->isNew(),
	'help' => __('The date after which teams are no longer allowed to edit their rosters. Leave blank for no deadline (changes can be made until the division is closed).'),
]);
?>
	</fieldset>
	<fieldset>
		<legend><?= __('Specifics') ?></legend>
<?php
echo $this->Form->input('days._ids', [
	'label' => __('Day(s) of play'),
	'multiple' => 'checkbox',
	'hiddenField' => false,
	'help' => __('Day, or days, on which this division will play.'),
]);
$this->Form->unlockField('days._ids');
echo $this->Form->input('ratio_rule', [
	'options' => Configure::read("sports.{$division->league->sport}.ratio_rule"),
	'hide_single' => true,
	'empty' => '---',
	'help' => __('Gender format for the division.'),
]);
echo $this->Form->input('roster_rule', [
	'templates' => $advanced,
	'cols' => 70,
	'help' => __('Rules that must be passed to allow a player to be added to the roster of a team in this division.') .
		' ' . $this->Html->help(['action' => 'rules', 'rules']),
]);
echo $this->Form->input('roster_method', [
	'templates' => $advanced,
	'options' => Configure::read('options.roster_methods'),
	'default' => 'invite',
	'help' => __('Do players need to accept invitations, or can they just be added? The latter has privacy policy implications and should be used only when necessary.'),
]);
if (Configure::read('feature.registration')) {
	echo $this->Form->input('flag_membership', [
		'templates' => $advanced,
		'options' => Configure::read('options.enable'),
		'default' => false,
	]);
}
echo $this->Form->input('flag_roster_conflict', [
	'templates' => $advanced,
	'options' => Configure::read('options.enable'),
	'default' => true,
]);
echo $this->Form->input('flag_schedule_conflict', [
	'templates' => $advanced,
	'options' => Configure::read('options.enable'),
	'default' => true,
]);
?>
	</fieldset>
	<fieldset>
		<legend><?= __('Scheduling') ?></legend>
<?php
echo $this->Jquery->ajaxInput('schedule_type', [
	'selector' => '#SchedulingFields',
	'url' => ['controller' => 'Divisions', 'action' => 'scheduling_fields'],
], [
	'options' => Configure::read('options.schedule_type'),
	'hide_single' => true,
	'default' => 'none',
	'help' => __('What type of scheduling to use. This affects how games are scheduled and standings displayed.'),
]);
?>
		<div id="SchedulingFields">
<?php
$can_scheduling_fields = $this->Authorize->can('scheduling_fields', \App\Controller\DivisionsController::class);
if (!empty($division->schedule_type)) {
	$league_obj = ModuleRegistry::getInstance()->load("LeagueType:{$division->schedule_type}");
	$fields = $league_obj->schedulingFields($can_scheduling_fields);
} else {
	$fields = [];
}
$unlock_fields = [];
foreach (ModuleRegistry::getModuleList('LeagueType') as $type) {
	$other = ModuleRegistry::getInstance()->load("LeagueType:{$type}");
	$other_fields = $other->schedulingFields($can_scheduling_fields);
	foreach (array_keys($other_fields) as $field) {
		if (!array_key_exists($field, $fields)) {
			$unlock_fields[] = $field;
		}
	}
}
echo $this->element('Divisions/scheduling_fields', compact('fields', 'unlock_fields'));
?>
		</div>
<?php
echo $this->Form->input('exclude_teams', [
	'templates' => $advanced,
	'options' => Configure::read('options.enable'),
	'default' => false,
	'help' => __('Allows coordinators to exclude teams from schedule generation.'),
]);
echo $this->Form->input('double_booking', [
	'templates' => $advanced,
	'options' => Configure::read('options.enable'),
	'default' => false,
	'help' => __('Allows coordinators to schedule multiple games in a single game slot.'),
]);
?>
	</fieldset>
	<fieldset>
		<legend><?= __('Scoring') ?></legend>
<?php
echo $this->Form->input('rating_calculator', [
	'options' => Configure::read('options.rating_calculator'),
	'hide_single' => true,
	'default' => 'none',
	'help' => __('What type of ratings calculation to use.'),
]);
echo $this->Form->input('email_after', [
	'size' => 5,
	'default' => 0,
	'help' => __('Email coaches and captains who haven\'t scored games after this many hours, no reminder if 0.'),
]);
echo $this->Form->input('finalize_after', [
	'size' => 5,
	'default' => 0,
	'help' => __('Games which haven\'t been scored will be automatically finalized after this many hours, no finalization if 0.'),
]);
if (Configure::read('scoring.allstars')) {
	echo $this->Form->input('allstars', [
		'templates' => $advanced,
		'options' => Configure::read('options.allstar'),
		'default' => 'never',
		'help' => __('When to ask coaches and captains for allstar nominations.'),
	]);
	echo $this->Form->input('allstars_from', [
		'templates' => $advanced,
		'options' => Configure::read('options.allstar_from'),
		'default' => 'opponent',
		'help' => __('Which team will allstar nominations come from? Ignored if the above field is set to "never".'),
	]);
}
if (Configure::read('scoring.most_spirited')) {
	echo $this->Form->input('most_spirited', [
		'templates' => $advanced,
		'options' => Configure::read('options.most_spirited'),
		'default' => 'never',
		'help' => __('When to ask coaches and captains for "most spirited player" nominations.'),
	]);
}
?>
	</fieldset>
	<p><?= $this->Jquery->toggleLinkPair(
		$this->Html->iconImg('gears_32.png', ['style' => 'vertical-align:middle; padding-right: 5px;']) . ' ' . __('Show advanced configuration'),
		'basic',
		$this->Html->iconImg('gear_32.png', ['style' => 'vertical-align:middle; padding-right: 5px;']) . ' ' . __('Show basic configuration'),
		'advanced',
		[ 'escape' => false ]
	) ?></p>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<?php
if (!$division->isNew()):
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'division' => $division->id],
	['alt' => __('Delete'), 'title' => __('Delete Division')],
	['confirm' => __('Are you sure you want to delete this division?')]));
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('New'), 'title' => __('New Division')]));
?>
	</ul>
</div>
<?php
endif;

$this->Html->scriptBlock('jQuery(".advanced").hide();', ['buffer' => true]);
