<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Field $field
 * @var \App\Model\Entity\GameSlot $game_slot
 * @var array $weeks
 */
?>
<li><?= $this->Jquery->toggleLink($field->long_name, "#Field{$field->id}Slots") ?>
<div id="Field<?= $field->id ?>Slots" class="no-labels" style="margin-top: 1em;<?= empty($expanded) ? ' display: none;' : '' ?>">
<?php
foreach ($weeks as $key => $week) {
	foreach ($times as $key2 => $start) {
		if ($game_slot->length > 0) {
			$game_slot->game_date = $week;
			$game_slot->game_end = $start->addMinutes($game_slot->length - $game_slot->buffer);
			$end = $this->Time->time($game_slot->end_time);
		} else if (empty($game_slot->game_end)) {
			$end = __('dark ({0})', $this->Time->time(\App\Lib\local_sunset_for_date($week)));
		} else {
			$end = $this->Time->time($game_slot->game_end);
		}
		echo $this->Form->control("game_slots.{$field->id}.$key.$key2", [
			'div' => false,
			'label' => __('{0} {1}-{2}', $this->Time->date($week), $this->Time->time($start), $end),
			'type' => 'checkbox',
			'hiddenField' => false,
			'checked' => true,
			// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
			'secure' => false,
		]);
	}
}
?>
</div>
</li>
