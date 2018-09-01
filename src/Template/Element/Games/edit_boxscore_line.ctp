<?php
use Cake\Core\Configure;

$stats = false;
if ($detail->play == 'Start') {
	$play = __('Game started');
} else if ($detail->play == 'Timeout') {
	$play = __('Timeout');
} else if (Configure::read("sports.{$game->division->league->sport}.other_options.{$detail->play}")) {
	$play = __(Configure::read("sports.{$game->division->league->sport}.other_options.{$detail->play}"));
} else {
	$play = __($detail->play);
	$stats = true;
}
?>

<tr>
	<td><?php
	echo $this->Form->hidden("score_details.{$detail->id}.id", [
		'value' => $detail->id,
	]);
	// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
	$this->Form->unlockField("score_details.{$detail->id}.id");
	// TODO: When the team is changed, change the player options. In the meantime, just make it fixed.
	if ($stats) {
		echo $team_names[$detail->team_id];
		echo $this->Form->hidden("score_details.{$detail->id}.team_id", [
			'value' => $detail->team_id,
		]);
		// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
		$this->Form->unlockField("score_details.{$detail->id}.team_id");
	} else {
		echo $this->Form->input("score_details.{$detail->id}.team_id", [
			'type' => 'select',
			'options' => $team_names,
			'value' => $detail->team_id,
			'label' => false,
			'secure' => false,
		]);
	}
	?></td>
	<td><?php
	echo $this->Form->hidden("score_details.{$detail->id}.created.year", [
		'value' => $year,
	]);
	echo $this->Form->hidden("score_details.{$detail->id}.created.month", [
		'value' => $month,
	]);
	echo $this->Form->hidden("score_details.{$detail->id}.created.day", [
		'value' => $day,
	]);
	// TODO: Add security to this, somehow. Low priority, as it's already restricted by permissions to people we purportedly trust.
	$this->Form->unlockField("score_details.{$detail->id}.created.year");
	$this->Form->unlockField("score_details.{$detail->id}.created.month");
	$this->Form->unlockField("score_details.{$detail->id}.created.day");

	echo $this->Form->input("score_details.{$detail->id}.created", [
		'type' => 'time',
		'value' => $detail->created,
		'label' => false,
		'secure' => false,
	]);
	?></td>
	<td><?= $play ?></td>
<?php
foreach($game->division->league->stat_types as $i => $stat):
?>
	<td><?php
	if ($stats) {
		if (!empty($detail->score_detail_stats)) {
			$person = collection($detail->score_detail_stats)->firstMatch(['stat_type_id' => $stat->id]);
		} else {
			$person = null;
		}
		echo $this->Form->input("score_details.{$detail->id}.score_detail_stats.$i.person_id", [
			'label' => false,
			'options' => $roster[$detail->team_id],
			'empty' => '---',
			'default' => $person ? $person->person_id : null,
		]);
		echo $this->Form->hidden("score_details.{$detail->id}.score_detail_stats.$i.stat_type_id", ['value' => $stat->id]);
	}
	?></td>
<?php
endforeach;
?>
	<td><?= isset($scores) ? implode(' - ', $scores) : '' ?></td>
	<td><?php
	echo $this->Jquery->ajaxLink($this->Html->iconImg('delete_24.png', ['alt' => __('Delete Score Detail'), 'title' => __('Delete Score Detail')]), [
		'url' => ['action' => 'delete_score', 'game' => $game->id, 'detail' => $detail->id],
		'confirm' => __('Are you sure you want to delete this score entry?'),
		'disposition' => 'remove_closest',
		'selector' => 'tr',
	], [
		'escape' => false,
	]);
	?></td>
</tr>
