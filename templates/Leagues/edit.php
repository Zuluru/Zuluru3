<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\League $league
 * @var \App\Model\Entity\Affiliate[] $affiliates
 * @var \App\Model\Entity\Day[] $days
 * @var \App\Model\Entity\Category[] $categories
 * @var \App\Model\Entity\StatType[] $stat_types
 * @var bool $tournaments
 */

use App\Model\Entity\Division;
use Cake\Core\Configure;use Cake\Utility\Inflector;

if (!isset($tournaments)) {
	if ($league->has('divisions')) {
		$tournaments = collection($league->divisions)->every(function (Division $division) {
			return $division->schedule_type == 'tournament';
		});
	} else {
		$tournaments = false;
	}
}
$this->Breadcrumbs->add($tournaments ? __('Tournaments') : __('Leagues'));
if ($league->isNew()) {
	$this->Breadcrumbs->add(__('Create'));
} else {
	$this->Breadcrumbs->add(h($league->name));
	$this->Breadcrumbs->add(__('Edit'));
}

// Set up the templates to use for advanced options. Note that these are based on the horizontal
// template set from the Bootstrap FormHelper, not the default templates from Bootstrap FormHelper
// or default templates from CakePHP FormHelper.
$advanced = [
	'inputContainer' => '<div class="mb-3 form-group row advanced {{type}}{{required}}">{{content}}</div>',
	'inputContainerError' => '<div class="mb-3 form-group row advanced {{type}}{{required}} has-error">{{content}}</div>',
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
<?php
$this->start('league_details');

echo $this->Form->i18nControls('name', [
	'size' => 70,
	'help' => $tournaments ? __('The full name of the tournament.') : __('The full name of the league. Year and season will be automatically added.'),
]);

if ($league->isNew()) {
	echo $this->Form->control('affiliate_id', [
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

echo $this->Form->control('season', [
	'options' => Configure::read('options.season'),
	'hide_single' => true,
	'empty' => '---',
	'help' => $tournaments ? __('Season during which this tournament\'s games take place.') : __('Season during which this league\'s games take place.'),
]);

if (!empty($categories)) {
	echo $this->Form->control('categories._ids', [
		'options' => $categories,
		'multiple' => true,
		'hiddenField' => false,
		'title' => __('Select all that apply'),
	]);
}

echo $this->Form->control('schedule_attempts', [
	'div' => 'input advanced',
	'size' => 5,
	'default' => 100,
	'help' => __('Number of attempts to generate a schedule, before taking the best option.'),
]);
?>
						<fieldset<?= (Configure::read('feature.spirit') && !Configure::read("sports.{$league->sport}.competition")) || Configure::read('scoring.stat_tracking') ? '' : ' class="advanced"' ?>>
							<legend><?= __('Scoring') ?></legend>
<?php
echo $this->Form->control('expected_max_score', [
	'div' => 'input advanced',
	'size' => 5,
	'default' => 17,
	'help' => __('Used as the size of the ratings table.'),
]);

$tie_breakers = Configure::read('options.tie_breaker');
if (Configure::read('feature.spirit') && !Configure::read("sports.{$league->sport}.competition")) {
	echo $this->Html->para('warning-message', $tournaments ?
		__('NOTE: If you set the questionnaire to "{0}" and disable numeric entry, spirit will not be tracked for this tournament.', Configure::read('options.spirit_questions.none')) :
		__('NOTE: If you set the questionnaire to "{0}" and disable numeric entry, spirit will not be tracked for this league.', Configure::read('options.spirit_questions.none'))
	);
	echo $this->Form->control('sotg_questions', [
		'options' => Configure::read('options.spirit_questions'),
		'label' => __('Spirit Questionnaire'),
		'default' => Configure::read('scoring.spirit_questions'),
		'help' => __('Select which questionnaire to use for spirit scoring, or "{0}" to use numeric scoring only.', Configure::read('options.spirit_questions.none')),
	]);
	echo $this->Form->control('numeric_sotg', [
		'options' => Configure::read('options.enable'),
		'label' => __('Spirit Numeric Entry'),
		'default' => Configure::read('scoring.spirit_numeric'),
		'help' => __('Enable or disable the entry of a numeric spirit score, independent of the questionnaire selected above.'),
	]);
	echo $this->Form->control('display_sotg', [
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
	echo $this->Form->control('carbon_flip', [
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

echo $this->Form->control('tie_breakers', [
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
$this->Html->scriptBlock('zjQuery("select[multiple]").asmSelect({sortable:true});', ['buffer' => true]);

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
<?= $this->Jquery->selectAll('#StatDetails', __('stats'), $this->Bootstrap->navPillLinkClasses()) ?>
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
		if (array_key_exists($type, $stat_types)) {
			foreach ($stat_types[$type] as $stat_type) {
				$options[] = [
					'text' => $stat_type->name,
					'value' => $stat_type->id,
					'class' => "sport_{$stat_type->sport}",
				];
			}
		}
		if (!empty($options)) {
			echo $this->Html->tag('fieldset', $this->Html->tag('legend', $type_desc) .
				$this->Form->control('stat_types._ids', [
					'label' => false,
					'options' => $options,
					'multiple' => 'checkbox',
					'hiddenField' => false,
				])
			);
		}
	}
?>
								</div>
							</div>
<?php
endif;
?>
						</fieldset>
<?php
$this->end();

$this->start('panels');

echo $this->Bootstrap->panel(
	$this->Bootstrap->panelHeading('League', $tournaments ? __('Tournament Details') : __('League Details'), ['collapsed' => false]),
	$this->Bootstrap->panelContent('League', $this->fetch('league_details'), ['collapsed' => false])
);

if (empty($league->divisions)) {
	echo $this->element('Leagues/division', ['index' => 0]);
} else {
	foreach ($league->divisions as $index => $division) {
		echo $this->element('Leagues/division', compact('index'));
	}
}

$this->end();
echo $this->Bootstrap->accordion($this->fetch('panels'));
?>
		<div class="actions columns">
<?php
echo $this->Bootstrap->navPills([
	$this->Jquery->ajaxLink(
		$this->Html->iconImg('add_32.png', ['alt' => __('Add Division'), 'title' => __('Add Division')]),
		[
			'url' => ['action' => 'add_division'],
			'disposition' => 'append',
			'selector' => '#accordion',
		],
		[
			'class' => 'icon',
			'escape' => false,
		]
	),
]);
?>
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
$this->Html->scriptBlock('zjQuery(".advanced").hide();', ['buffer' => true]);
?>
<div class="actions columns">
<?php
$links = [$this->Html->link($tournaments ? __('List Leagues') : __('List Leagues'), ['action' => 'index'], ['class' => $this->Bootstrap->navPillLinkClasses()])];
if (!$league->isNew()) {
	$links[] = $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['league' => $league->id]],
		['alt' => __('Delete'), 'title' => $tournaments ? __('Delete League') : __('Delete League')],
		['confirm' => __('Are you sure you want to delete this league?')]
	);
	$links[] = $this->Html->iconLink('add_32.png',
		['action' => 'add'],
		['alt' => __('Add'), 'title' => $tournaments ? __('Add League') : __('Add League')]
	);
}
echo $this->Bootstrap->navPills($links);
?>
</div>
