<?php
use App\Model\Entity\Division;
use Cake\Core\Configure;

if (!isset($tournaments)) {
	if ($league->has('divisions')) {
		$tournaments = collection($league->divisions)->every(function (Division $division) {
			return $division->schedule_type == 'tournament';
		});
	} else {
		$tournaments = false;
	}
}
$this->Html->addCrumb($tournaments ? __('Tournaments') : __('Leagues'));
if ($league->isNew()) {
	$this->Html->addCrumb(__('Create'));
} else {
	$this->Html->addCrumb(h($league->name));
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

<div class="leagues form">
	<?= $this->Form->create($league, ['align' => 'horizontal']) ?>
	<p><?= $this->Jquery->toggleLinkPair(
		$this->Html->iconImg('gears_32.png', ['style' => 'vertical-align:middle; padding-right: 5px;']) . ' ' . __('Show advanced configuration'),
		'basic',
		$this->Html->iconImg('gear_32.png', ['style' => 'vertical-align:middle; padding-right: 5px;']) . ' ' . __('Show basic configuration'),
		'advanced',
		[ 'escape' => false ]
	) ?></p>
	<fieldset>
		<legend><?= $league->isNew() ? ($tournaments ? __('Create Tournament') : __('Create League')) : ($tournaments ? __('Edit Tournament') : __('Edit League')) ?></legend>
		<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="false">
			<div class="panel panel-default">
				<div class="panel-heading" role="tab" id="LeagueHeading">
					<h4 class="panel-title"><a role="button" class="accordion-toggle" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#LeagueDetails" aria-expanded="true" aria-controls="LeagueDetails"><?= $tournaments ? __('Tournament Details') : __('League Details') ?></a></h4>
				</div>
				<div id="LeagueDetails" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="LeagueHeading">
					<div class="panel-body">
<?php
echo $this->Form->input('name', [
	'size' => 70,
	'help' => $tournaments ? __('The full name of the tournament.') : __('The full name of the league. Year and season will be automatically added.'),
]);

if ($league->isNew()) {
	echo $this->Form->input('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
		'empty' => '---',
	]);
}

$sports = Configure::read('options.sport');
$values = [];
foreach (array_keys(Configure::read('options.sport')) as $sport) {
	$values[$sport] = ".sport_{$sport}";
}
echo $this->Jquery->toggleInput('sport', [
	'options' => $sports,
	'hide_single' => true,
	'empty' => '---',
	'help' => $tournaments ? __('Sport played in this tournament.') : __('Sport played in this league.'),
], [
	'values' => $values,
	'parent_selector' => 'div.checkbox',
	'parent_selector_optional' => true,
]);

echo $this->Form->input('season', [
	'options' => Configure::read('options.season'),
	'hide_single' => true,
	'empty' => '---',
	'help' => $tournaments ? __('Season during which this tournament\'s games take place.') : __('Season during which this league\'s games take place.'),
]);

echo $this->Form->input('schedule_attempts', [
	'div' => 'input advanced',
	'size' => 5,
	'default' => 100,
	'help' => __('Number of attempts to generate a schedule, before taking the best option.'),
]);
?>
						<fieldset<?= (Configure::read('feature.spirit') && !Configure::read("sports.{$league->sport}.competition")) || Configure::read('scoring.stat_tracking') ? '' : ' class="advanced"' ?>>
							<legend><?= __('Scoring') ?></legend>
<?php
$tie_breakers = Configure::read('options.tie_breaker');
if (Configure::read('feature.spirit') && !Configure::read("sports.{$league->sport}.competition")) {
	echo $this->Html->para('warning-message', $tournaments ?
		__('NOTE: If you set the questionnaire to "{0}" and disable numeric entry, spirit will not be tracked for this tournament.', Configure::read('options.spirit_questions.none')) :
		__('NOTE: If you set the questionnaire to "{0}" and disable numeric entry, spirit will not be tracked for this league.', Configure::read('options.spirit_questions.none'))
	);
	echo $this->Form->input('sotg_questions', [
		'options' => Configure::read('options.spirit_questions'),
		'label' => __('Spirit Questionnaire'),
		'default' => Configure::read('scoring.spirit_questions'),
		'help' => __('Select which questionnaire to use for spirit scoring, or "{0}" to use numeric scoring only.', Configure::read('options.spirit_questions.none')),
	]);
	echo $this->Form->input('numeric_sotg', [
		'options' => Configure::read('options.enable'),
		'label' => __('Spirit Numeric Entry'),
		'default' => Configure::read('scoring.spirit_numeric'),
		'help' => __('Enable or disable the entry of a numeric spirit score, independent of the questionnaire selected above.'),
	]);
	echo $this->Form->input('display_sotg', [
		'div' => 'input advanced',
		'options' => Configure::read('options.sotg_display'),
		'label' => __('Spirit Display'),
		'default' => 'all',
		'help' => __('Control spirit display. "All" shows numeric scores and survey answers (if applicable) to anyone. "Numeric" shows game scores but not survey answers. "Symbols Only" shows only star, check, and X, with no numeric values attached. "Coordinator Only" restricts viewing of any per-game information to coordinators only.'),
	]);
} else {
	echo $this->Form->hidden('sotg_questions', ['value' => 'none']);
	echo $this->Form->hidden('numeric_sotg', ['value' => 0]);
	unset($tie_breakers['spirit']);
}
if (Configure::read('scoring.carbon_flip')) {
	echo $this->Form->input('carbon_flip', [
		'div' => 'input advanced',
		'options' => Configure::read('options.enable'),
		'empty' => $league->isNew() ? '---' : false,
		'label' => __('Carbon Flip'),
		'default' => Configure::read('scoring.spirit_numeric'),
		'help' => __('Enable or disable entry of carbon flip results in score submission.'),
	]);
} else {
	unset($tie_breakers['cf']);
}

$tie_breaker_options = [];
$flip = array_flip($league->tie_breakers);
foreach ($tie_breakers as $value => $text) {
	$option = compact('value', 'text');
	if (array_key_exists($value, $flip)) {
		$option['id'] = sprintf("option_%04d", $flip[$value]);
	}
	$tie_breaker_options[] = $option;
}

echo $this->Form->input('tie_breakers', [
	'div' => 'input advanced',
	'options' => $tie_breaker_options,
	'type' => 'select',
	'multiple' => true,
	'hiddenField' => false,
	'title' => __('Order of tie-breakers to use in standings'),
]);
$this->Form->unlockField('asmSelect0');
$this->Html->css('jquery.asmselect.css', ['block' => true]);
$this->Html->script('jquery.asmselect.js', ['block' => true]);
$this->Html->scriptBlock('jQuery("select[multiple]").asmSelect({sortable:true});', ['buffer' => true]);

echo $this->Form->input('expected_max_score', [
	'div' => 'input advanced',
	'size' => 5,
	'default' => 17,
	'help' => __('Used as the size of the ratings table.'),
]);

if (Configure::read('scoring.stat_tracking')):
	echo $this->Jquery->toggleInput('stat_tracking', [
		'options' => Configure::read('options.stat_tracking'),
		'empty' => '---',
		'help' => __('When to ask coaches/captains for game stats.'),
	], [
		'values' => [
			'always' => '#StatDetails',
			'optional' => '#StatDetails',
		],
	]);
?>
							<div id="StatDetails">
<?= $this->Jquery->selectAll('#StatDetails', __('stats')) ?>
								<div id="StatFields">
<?php
	$options = [];

	$stat_types = collection($stat_types)->groupBy('type')->toArray();
	foreach ([
		 'entered' => __('Stats to enter'),
		 'game_calc' => __('Per-game calculated stats to display'),
		 'season_total' => __('Stats to display season totals of'),
		 'season_avg' => __('Stats to display season averages of'),
		 'season_calc' => __('Stats to display season calculated values for'),
	] as $type => $type_desc) {
		$options = [];
		foreach ($stat_types[$type] as $stat_type) {
			$options[] = [
				'text' => $stat_type->name,
				'value' => $stat_type->id,
				'class' => "sport_{$stat_type->sport}",
			];
		}
		echo $this->Html->tag('fieldset', $this->Html->tag('legend', $type_desc) .
			$this->Form->input('stat_types._ids', [
				'label' => false,
				'options' => $options,
				'multiple' => 'checkbox',
				'hiddenField' => false,
			])
		);
	}
?>
								</div>
							</div>
<?php
endif;
?>
						</fieldset>
					</div>
				</div>
			</div>
<?php
if (empty($league->divisions)) {
	echo $this->element('Leagues/division', ['index' => 0]);
} else {
	foreach ($league->divisions as $index => $division) {
		echo $this->element('Leagues/division', compact('index'));
	}
}
?>
		</div>
		<div class="actions columns">
			<ul class="nav nav-pills">
<?php
	echo $this->Html->tag('li', $this->Jquery->ajaxLink($this->Html->iconImg('add_32.png', ['alt' => __('Add Division'), 'title' => __('New Division')]), [
		'url' => ['action' => 'add_division'],
		'disposition' => 'append',
		'selector' => '#accordion',
	], [
		'class' => 'icon',
		'escape' => false,
	]));
?>
			</ul>
		</div>
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
$this->Html->scriptBlock('jQuery(".advanced").hide();', ['buffer' => true]);
?>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->link($tournaments ? __('List Leagues') : __('List Leagues'), ['action' => 'index']));
if (!$league->isNew()) {
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', 'league' => $league->id],
		['alt' => __('Delete'), 'title' => $tournaments ? __('Delete League') : __('Delete League')],
		['confirm' => __('Are you sure you want to delete this league?')]));
	echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('New'), 'title' => $tournaments ? __('New League') : __('New League')]));
}
?>
	</ul>
</div>
