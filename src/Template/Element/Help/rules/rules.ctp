<?php
/**
 * @var \App\View\AppView $this
 */
?>
<p><?= __('The following rules are available for use in the rules engine. Note that rule names are accepted in upper, lower or mixed case, but arguments to rules are generally case-specific.') ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'rules/rules',
	'topics' => [
		'constant' => 'CONSTANT',
		'attribute' => 'ATTRIBUTE',
		'member_type' => 'MEMBER_TYPE',
		'format_date' => 'FORMAT_DATE',
		'team_count' => 'TEAM_COUNT',
		'league_team_count' => 'LEAGUE_TEAM_COUNT',
		'registered' => 'REGISTERED',
		'signed_waiver' => 'SIGNED_WAIVER',
		'has_document' => 'HAS_DOCUMENT',
		'compare' => 'COMPARE',
		'and' => 'AND',
		'or' => 'OR',
		'not' => 'NOT',
	],
]);
