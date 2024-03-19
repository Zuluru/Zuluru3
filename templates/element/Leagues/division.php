<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\League $league
 * @var int $index
 */

use Cake\Core\Configure;
use App\Core\ModuleRegistry;

if ($league->has('divisions') && array_key_exists($index, $league->divisions)) {
	$errors = $league->divisions[$index]->getErrors();
	$new = false;
} else {
	$new = true;
}
$collapsed = (empty($errors) && !$league->isNew());

// Set up the templates to use for advanced options. Note that these are based on the horizontal
// template set from the Bootstrap FormHelper, not the default templates from Bootstrap FormHelper
// or default templates from CakePHP FormHelper.
$advanced = [
	'inputContainer' => '<div class="form-group advanced {{type}}{{required}}">{{content}}</div>',
	'inputContainerError' => '<div class="form-group advanced {{type}}{{required}} has-error">{{content}}</div>',
];
?>

<div class="panel panel-default">
	<div class="panel-heading" role="tab" id="DivisionHeading<?= $index ?>">
		<h4 class="panel-title"><a role="button" class="accordion-toggle<?= $collapsed ? ' collapsed' : '' ?>" data-toggle="collapse" data-parent="#accordion" href="#DivisionDetails<?= $index ?>" aria-expanded="<?= $collapsed ? 'true' : 'false' ?>" aria-controls="DivisionDetails<?= $index ?>"><?= __('Division Details') ?>:</a>
			<?= $this->Form->control("divisions.$index.name", [
				'placeholder' => __('Division Name'),
			]) ?>
		</h4>
	</div>
	<fieldset id="DivisionDetails<?= $index ?>" class="panel-collapse collapse<?= $collapsed ? '' : ' in' ?>" role="tabpanel" aria-labelledby="DivisionHeading<?= $index ?>">
		<fieldset class="panel-body">
<?php
if (!$new) {
	echo $this->Form->control("divisions.$index.id");
}
echo $this->Form->control("divisions.$index.coord_list", [
	'templates' => $advanced,
	'label' => __('Coordinator Email List'),
	'size' => 70,
	'help' => __('An email alias for all coordinators of this division (can be a comma separated list of individual email addresses).'),
]);
echo $this->Form->control("divisions.$index.capt_list", [
	'templates' => $advanced,
	'label' => __('Coach/Captain Email List'),
	'size' => 70,
	'help' => __('An email alias for all coaches and captains of this division.'),
]);
echo $this->Form->control("divisions.$index.header", [
	'templates' => $advanced,
	'cols' => 70,
	'rows' => 5,
	'help' => __('A short blurb to be displayed at the top of schedule and standings pages, HTML is allowed.'),
	'class' => 'wysiwyg_advanced',
]);
echo $this->Form->control("divisions.$index.footer", [
	'templates' => $advanced,
	'cols' => 70,
	'rows' => 5,
	'help' => __('A short blurb to be displayed at the bottom of schedule and standings pages, HTML is allowed.'),
	'class' => 'wysiwyg_advanced',
]);
?>
			<fieldset>
				<legend><?= __('Dates') ?></legend>
<?php
echo $this->Form->control("divisions.$index.open", [
	'label' => __('First Game'),
	'empty' => '---',
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => !$league->isNew(),
	'help' => __('Date of the first game in the schedule. Will be used to determine open/closed status.'),
]);
echo $this->Form->control("divisions.$index.close", [
	'label' => __('Last Game'),
	'empty' => '---',
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => !$league->isNew(),
	'help' => __('Date of the last game in the schedule. Will be used to determine open/closed status.'),
]);
echo $this->Form->control("divisions.$index.roster_deadline", [
	'empty' => '---',
	'minYear' => Configure::read('options.year.event.min'),
	'maxYear' => Configure::read('options.year.event.max'),
	'looseYears' => !$league->isNew(),
	'help' => __('The date after which teams are no longer allowed to edit their rosters. Leave blank for no deadline (changes can be made until the division is closed).'),
]);
?>
			</fieldset>
			<fieldset>
				<legend><?= __('Specifics') ?></legend>
<?php
echo $this->Form->control("divisions.$index.days._ids", [
	'label' => __('Day(s) of play'),
	'multiple' => 'checkbox',
	'hiddenField' => false,
	'help' => __('Day, or days, on which this division will play.'),
]);

$ratios = [];
$sports = Configure::read('options.sport');
foreach (Configure::read('sports') as $sport => $details) {
	if (array_key_exists($sport, $sports)) {
		foreach (array_keys($details['roster_requirements']) as $name) {
			if (!array_key_exists($name, $ratios)) {
				$ratios[$name] = ['text' => $name, 'value' => $name, 'class' => "sport_$sport"];
			} else {
				$ratios[$name]['class'] .= " sport_$sport";
			}
		}
	}
}
ksort($ratios);
echo $this->Form->control("divisions.$index.ratio_rule", [
	'options' => array_values($ratios),
	'hide_single' => true,
	'empty' => '---',
	'help' => __('Gender format for the division.'),
]);

echo $this->Form->control("divisions.$index.roster_rule", [
	'templates' => $advanced,
	'cols' => 70,
	'help' => __('Rules that must be passed to allow a player to be added to the roster of a team in this division.') .
		' ' . $this->Html->help(['action' => 'rules', 'rules']),
]);
echo $this->Form->control("divisions.$index.roster_method", [
	'templates' => $advanced,
	'options' => Configure::read('options.roster_methods'),
	'default' => 'invite',
	'help' => __('Do players need to accept invitations, or can they just be added? The latter has privacy policy implications and should be used only when necessary.'),
]);
if (Configure::read('feature.registration')) {
	echo $this->Form->control("divisions.$index.flag_membership", [
		'templates' => $advanced,
		'options' => Configure::read('options.enable'),
		'default' => false,
	]);
}
echo $this->Form->control("divisions.$index.flag_roster_conflict", [
	'templates' => $advanced,
	'options' => Configure::read('options.enable'),
	'default' => true,
]);
echo $this->Form->control("divisions.$index.flag_schedule_conflict", [
	'templates' => $advanced,
	'options' => Configure::read('options.enable'),
	'default' => true,
]);
?>
			</fieldset>
			<fieldset>
				<legend><?= __('Scheduling') ?></legend>
<?php
echo $this->Jquery->ajaxInput("divisions.$index.schedule_type", [
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
$administrative = $this->Authorize->can('scheduling_fields', \App\Controller\DivisionsController::class);
if (!empty($league->divisions[$index]->schedule_type)) {
	$league_obj = ModuleRegistry::getInstance()->load("LeagueType:{$league->divisions[$index]->schedule_type}");
	$fields = $league_obj->schedulingFields($administrative);
} else {
	$fields = [];
}
$unlock_fields = [];
foreach (ModuleRegistry::getModuleList('LeagueType') as $type) {
	$other = ModuleRegistry::getInstance()->load("LeagueType:{$type}");
	$other_fields = $other->schedulingFields($administrative);
	foreach (array_keys($other_fields) as $field) {
		if (!array_key_exists($field, $fields)) {
			$unlock_fields[] = $field;
		}
	}
}
echo $this->element('Divisions/scheduling_fields', compact('fields', 'unlock_fields', 'index'));
?>
				</div>
<?php
echo $this->Form->control("divisions.$index.exclude_teams", [
	'templates' => $advanced,
	'options' => Configure::read('options.enable'),
	'default' => false,
	'help' => __('Allows coordinators to exclude teams from schedule generation.'),
]);
echo $this->Form->control("divisions.$index.double_booking", [
	'templates' => $advanced,
	'label' => __('Allow double-booking?'),
	'options' => Configure::read('options.enable'),
	'default' => false,
	'help' => __('Allows coordinators to schedule multiple games in a single game slot.'),
]);
?>
			</fieldset>
			<fieldset>
				<legend><?= __('Scoring') ?></legend>
<?php
echo $this->Form->control("divisions.$index.rating_calculator", [
	'options' => Configure::read('options.rating_calculator'),
	'hide_single' => true,
	'default' => 'none',
	'help' => __('What type of ratings calculation to use.'),
]);
echo $this->Form->control("divisions.$index.email_after", [
	'label' => __('Scoring reminder delay'),
	'size' => 5,
	'default' => 0,
	'help' => __('Email coaches and captains who haven\'t scored games after this many hours, no reminder if 0.'),
]);
echo $this->Form->control("divisions.$index.finalize_after", [
	'size' => 5,
	'default' => 0,
	'help' => __('Games which haven\'t been scored will be automatically finalized after this many hours, no finalization if 0.'),
]);
if (Configure::read('scoring.allstars')) {
	echo $this->Form->control("divisions.$index.allstars", [
		'templates' => $advanced,
		'options' => Configure::read('options.allstar'),
		'default' => 'never',
		'help' => __('When to ask coaches and captains for allstar nominations.'),
	]);
	echo $this->Form->control("divisions.$index.allstars_from", [
		'templates' => $advanced,
		'label' => __('All-star nominations from'),
		'options' => Configure::read('options.allstar_from'),
		'default' => 'opponent',
		'help' => __('Which team will allstar nominations come from? Ignored if the above field is set to "never".'),
	]);
}
if (Configure::read('scoring.most_spirited')) {
	echo $this->Form->control("divisions.$index.most_spirited", [
		'templates' => $advanced,
		'options' => Configure::read('options.most_spirited'),
		'default' => 'never',
		'help' => __('When to ask coaches and captains for "most spirited player" nominations.'),
	]);
}
?>
			</fieldset>
		</fieldset>
	</fieldset>
</div>
