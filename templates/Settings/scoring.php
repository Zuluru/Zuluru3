<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Affiliate $affiliate
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Settings'));
$this->Breadcrumbs->add(__('Scoring'));
?>

<div class="settings form">
<?php
if ($affiliate) {
	$empty = __('Use default');
} else {
	$empty = false;
}
echo $this->Form->create(null, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
	<fieldset>
		<legend><?= __('Defaulted Games') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'scoring',
	'name' => 'default_winning_score',
	'options' => [
		'label' => __('Winning score to record for defaulted games'),
		'size' => 6,
	],
]);
echo $this->element('Settings/input', [
	'category' => 'scoring',
	'name' => 'default_losing_score',
	'options' => [
		'label' => __('Losing score to record for defaulted games'),
		'size' => 6,
	],
]);
echo $this->element('Settings/input', [
	'category' => 'scoring',
	'name' => 'default_transfer_ratings',
	'options' => [
		'label' => __('Transfer ratings points for defaulted games'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
	],
]);
?>
	</fieldset>

<?php
if (Configure::read('feature.spirit')):
?>
	<fieldset>
		<legend><?= __('Spirit Scores') ?></legend>
<?php
	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'spirit_questions',
		'options' => [
			'label' => __('Spirit Questions'),
			'type' => 'select',
			'options' => Configure::read ('options.spirit_questions'),
			'empty' => $empty,
			'help' => __('Default type of spirit questions to use when creating a new league.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'spirit_numeric',
		'options' => [
			'label' => __('Spirit Numeric'),
			'type' => 'radio',
			'options' => Configure::read ('options.enable'),
			'help' => __('Default enable or disable entry of numeric spirit scores when creating a new league.'),
		],
	]);

	echo $this->Html->para(null, __('By using various combinations of questions and numeric entry above, you can have just the questionnaire, just the numeric entry, both or neither.'));
	echo $this->Html->para(null, __('The values set above will be the default value for leagues, but can be overridden on a per-league basis.'));

	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'spirit_max',
		'options' => [
			'label' => __('Maximum spirit score, when no questionnaire is used'),
			'size' => 6,
		],
	]);

	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'missing_score_spirit_penalty',
		'options' => [
			'label' => __('Spirit penalty for not entering score'),
			'size' => 6,
		],
	]);

	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'spirit_default',
		'options' => [
			'label' => __('Spirit Default'),
			'type' => 'radio',
			'options' => Configure::read ('options.enable'),
			'help' => __('Include a default spirit score when not entered.'),
		],
	]);
?>
	</fieldset>
<?php
endif;
?>

	<fieldset>
		<legend><?= __('Score Entry Features') ?></legend>
<?php
	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'allstars',
		'options' => [
			'label' => __('Allstars'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('If enabled, all-star submissions will be a per-league option; otherwise, they will be disabled entirely.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'incident_reports',
		'options' => [
			'label' => __('Incident Reports'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('If enabled, coaches and captains will be allowed to file incident reports when submitting scores.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'most_spirited',
		'options' => [
			'label' => __('Most Spirited'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('If enabled, "most spirited player" submissions will be a per-league option; otherwise, they will be disabled entirely.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'stat_tracking',
		'options' => [
			'label' => __('Handle stat submission and tracking as part of game scoring'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('Enable or disable stat tracking options. If enabled here, stats can still be disabled on a per-league basis.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'carbon_flip',
		'options' => [
			'label' => __('Handle carbon flip as part of game scoring'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('Enable or disable carbon flip, an environmentally-friendly option to replace traditional pre-game coin flips.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'women_present',
		'options' => [
			'label' => __('Ask about the number of women designated players as part of game scoring'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('This will only apply to divisions where the ratio rule permits variation.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'scoring',
		'name' => 'subs',
		'options' => [
			'label' => __('Ask for a list of subs as part of game scoring'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('If enabled, captains will be asked to include a list of players who subbed for their team in the game.'),
		],
	]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
