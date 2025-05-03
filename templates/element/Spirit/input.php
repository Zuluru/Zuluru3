<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $for_team
 * @var \App\Model\Entity\Team $from_team
 * @var \App\Module\Spirit $spirit_obj
 * @var \App\Service\Games\SpiritService $spirit_service
 * @var \App\Service\Games\ScoreService $score_service
 * @var bool $is_official
 */

use Cake\Core\Configure;

$index = $spirit_service->getEntryIndexFor($for_team->id, $is_official);
if (is_null($index)) {
	$index = Configure::read('next_spirit_index');
	if (!$index) {
		$index = 100;
	}
	Configure::write('next_spirit_index', $index + 1);
}

$prefix = "spirit_entries.{$index}";
if (array_key_exists($index, $game->spirit_entries)) {
	echo $this->Form->hidden("$prefix.id", [
		'value' => $game->spirit_entries[$index]->id,
	]);
}
echo $this->Form->hidden("$prefix.team_id", [
	'value' => $for_team->id,
]);
if (!$is_official) {
	echo $this->Form->hidden("$prefix.created_team_id", [
		'value' => $from_team->id,
	]);
	$creator = ' ' . __('by {0}', $from_team->name);
} else {
	$creator = ' ' . __('by official');
}

$spirit = $this->element("Spirit/input/{$spirit_obj->render_element}",
	compact('prefix', 'for_team', 'from_team', 'game', 'spirit_obj'));

if ($game->division->league->numeric_sotg) {
	if (!isset($opts)) {
		$opts = [];
	} else if (!is_array($opts)) {
		$opts = [$opts];
	}

	if ($spirit_obj->render_element !== 'none') {
		$suggest = '&nbsp;' .
			$this->Html->tag('span',
				$this->Html->link(__('Suggest'), '#', [
					'onclick' => "suggestSpirit('{$index}'); return false;",
				]), ['class' => 'actions']);
	} else {
		$suggest = null;
	}

	$opts = array_merge([
		'size' => 3,
		'label' => __('Spirit'),
		'type' => 'number',
		'div' => false,
		'help' => '&nbsp;' . __('(between 0 and {0})', $spirit_obj->max()) . $suggest,
		'secure' => false,
	], $opts);

	$spirit .= $this->Form->control("spirit_entries.{$index}.entered_sotg", $opts);
}

// Don't show this when submitting scores, just when editing. We don't need
// to check admin/coordinator permissions, as that's already been done.
if ($this->getRequest()->getParam('action') === 'edit') {
	$checked = false;
	if (array_key_exists($index, $game->spirit_entries) &&
		$game->spirit_entries[$index]->has('score_entry_penalty') &&
		$game->spirit_entries[$index]->score_entry_penalty !== 0)
	{
		$checked = true;
	} else if (!$game->isFinalized() && !$score_service->hasScoreEntryFrom($for_team->id)) {
		$checked = true;
	}
	$spirit .= $this->Form->control("spirit_entries.{$index}.score_entry_penalty", [
		'type' => 'checkbox',
		'label' => __('Assign penalty for missing score entry?'),
		'value' => -Configure::read('scoring.missing_score_spirit_penalty'),
		'checked' => $checked,
		'secure' => false,
	]);
}

if ($spirit) {
	echo $this->Html->tag('fieldset',
		$this->Html->tag('legend', __('Spirit assigned to {0}{1}', $for_team->name, $creator)) . $spirit,
		['class' => 'spirit normal']);
}
