<?php
$this->Html->addCrumb(__('Teams'));
$this->Html->addCrumb(__('Join'));
?>

<div class="teams index" id="kick_start">
	<h2><?= __('Join a Team') ?></h2>

	<p><?= $this->Paginator->counter([
			'format' => __('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
		]) ?></p>

	<div class="actions columns">
		<ul>
<?php
$affiliate_id = null;
foreach ($teams as $team) {
	if (count($affiliates) > 1 && $team->division->league->affiliate_id != $affiliate_id):
		$affiliate_id = $team->division->league->affiliate_id;
?>
			<h3 class="affiliate"><?= h($team->division->league->affiliate->name) ?></h3>
<?php
	endif;

	if (!in_array($team->id, $this->UserCache->read('TeamIDs'))) {
		echo $this->Html->tag('li', $this->Html->link($team->name . ' (' . $team->division->league_name . ')',
			['controller' => 'Teams', 'action' => 'roster_request', 'team' => $team->id]));
	}
}
?>
		</ul>
	</div>

	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>

	<p class="warning-message"><?= __('If you don\'t see the team you\'re looking for, the coach or captain may have made the roster "closed", so that they have to invite you to join the team. Contact them directly to let them know you\'ve signed up and are ready to play!') ?></p>
</div>
