<?php
use Cake\Core\Configure;

$prefix = "spirit_entries.{$index}";
if (array_key_exists($index, $game->spirit_entries)) {
	echo $this->Form->hidden("$prefix.id", [
		'value' => $game->spirit_entries[$index]->id,
	]);
}
echo $this->Form->hidden("$prefix.team_id", [
	'value' => $for_team->id,
]);
echo $this->Form->hidden("$prefix.created_team_id", [
	'value' => $from_team->id,
]);

$spirit = $this->element("Spirit/input/{$spirit_obj->render_element}",
	compact('prefix', 'for_team', 'from_team', 'game', 'spirit_obj'));

if ($game->division->league->numeric_sotg) {
	if (!isset($opts)) {
		$opts = [];
	} else if (!is_array($opts)) {
		$opts = [$opts];
	}

	if ($spirit_obj->render_element != 'none') {
		$suggest = '&nbsp;' .
			$this->Html->tag('span',
				$this->Html->link('Suggest', '#', [
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

	$spirit .= $this->Form->input("spirit_entries.{$index}.entered_sotg", $opts);
}

// Don't show this when submitting scores, just when editing. We don't need
// to check admin/coordinator permissions, as that's already been done.
if ($this->getRequest()->getParam('action') == 'edit') {
	$checked = false;
	if (array_key_exists($index, $game->spirit_entries) &&
		$game->spirit_entries[$index]->has('score_entry_penalty') &&
		$game->spirit_entries[$index]->score_entry_penalty != 0)
	{
		$checked = true;
	} else if (!$game->isFinalized() &&
		!array_key_exists($index, $game->score_entries))
	{
		$checked = true;
	}
	$spirit .= $this->Form->input("spirit_entries.{$index}.score_entry_penalty", [
		'type' => 'checkbox',
		'label' => __('Assign penalty for missing score entry?'),
		'value' => -Configure::read('scoring.missing_score_spirit_penalty'),
		'checked' => $checked,
		'secure' => false,
	]);
}

if ($spirit) {
	echo $this->Html->tag('fieldset',
		$this->Html->tag('legend', __('Spirit assigned to {0}', $for_team->name)) . $spirit,
		['class' => 'spirit normal']);
}
