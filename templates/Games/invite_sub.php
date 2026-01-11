<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Game $game
 * @var \Cake\I18n\FrozenDate $date
 * @var \App\Model\Entity\Team $opponent
 * @var \App\Model\Entity\SubRequest[] $pastRequests
 */

$this->Breadcrumbs->add(__('Games'));
$this->Breadcrumbs->add(__('Invite Sub'));
$this->Breadcrumbs->add(h($team->name));
?>

<div class="games form">
<h2><?= __('Invite Sub') ?></h2>
	<dl class="row">
		<dt class="col-sm-3 text-end"><?= __('Team') ?></dt>
		<dd class="col-sm-9 mb-0"><?= $this->element('Teams/block', ['team' => $team]) ?></dd>
		<dt class="col-sm-3 text-end"><?= __('Game Date') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
		if (!$game->isNew()) {
			echo $this->Time->date($game->game_slot->game_date);
		} else {
			echo $this->Time->date($date);
		}
		?></dd>
		<dt class="col-sm-3 text-end"><?= __('Game Time') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
		if (!$game->isNew()) {
			echo $this->Time->time($game->game_slot->game_start);
		} else {
			echo __('TBD');
		}
		?></dd>
		<dt class="col-sm-3 text-end"><?= __('Opponent') ?></dt>
		<dd class="col-sm-9 mb-0"><?php
		if (isset($opponent)) {
			echo $this->element('Teams/block', ['team' => $opponent]);
		} else {
			echo __('TBD');
		}
		?></dd>
	</dl>

	<p><strong><?= __('Search for a player, and/or select from past invitations. People who are known to be unavailable to you will not be included here.') ?></strong></p>
	<p><?= __('Note that, to maximize accurate historical records, you are allowed to add subs to games up to three days after the game has been played.') ?></p>
<?php
if (empty($team->division_id)) {
	$affiliate_id = $team->affiliate_id;
} else {
	$affiliate_id = $team->division->league->affiliate_id;
}
echo $this->element('People/search_form', ['affiliate_id' => $affiliate_id]);
echo $this->Form->create($game, ['action' => 'invite_subs']);
echo $this->Form->hidden('team_id', ['value' => $team->id]);
if ($game->id) {
	echo $this->Form->hidden('game_id', ['value' => $game->id]);
} else {
	echo $this->Form->hidden('date', ['value' => $date]);
}
?>

	<div id="SearchResults" class="zuluru_pagination" style="margin-top: 1em;">
		<?= $this->element('People/sub_results') ?>
	</div>
<?php
foreach ($pastRequests as $option) {
	echo $this->Form->control("player.$option->person_id", [
		'label' => [
			'text' => $this->element('People/block', ['person' => $option->person, 'link' => false]),
			'escape' => false,
		],
		'type' => 'checkbox',
	]);
}
?>
<?php
echo $this->Form->control('note', [
	'label' => __('You may optionally add a personal note which will be included in the invitation email to the player.'),
	'size' => 80,
]);

echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
