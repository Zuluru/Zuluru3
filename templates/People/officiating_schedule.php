<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $official
 * @var \Cake\Collection\CollectionInterface|\App\Model\Entity\Game[] $officiated_games
 * @var bool $all
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Officials'));
$this->Breadcrumbs->add($official->full_name);
$this->Breadcrumbs->add(__('Officiating Schedule'));
?>
<div class="officials schedule">
<h2><?= __('Officiating Schedule') . ': ' . $official->full_name ?></h2>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<tr>
			<th><?= __('Date') ?></th>
			<th><?= __('Time') ?></th>
			<th><?= __(Configure::read("sports.{$officiated_games->first()->division->league->sport}.field_cap")) ?></th>
			<th><?= __('Division') ?></th>
			<th><?= __('Score') ?></th>
		</tr>
<?php
foreach ($officiated_games as $game):
	$class = null;
	if (!$game->published) {
		$class = ' class="unpublished"';
	}
	$game->readDependencies();
?>
		<tr<?= $class ?>>
			<td><?= $this->Time->fulldate($game->game_slot->start_time) ?></td>
			<td><?= $this->Html->link($this->Time->timeRange($game->game_slot), ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]) ?></td>
			<td><?= $this->element('Fields/block', ['field' => $game->game_slot->field]) ?></td>
			<td><?= $this->element('Divisions/block', ['division' => $game->division, 'field' => 'long_league_name']) ?></td>
			<td class="actions"><?php
				echo $this->Game->score($game, $game->division);
				echo $this->Game->actions($game, $game->division, $game->division->league);
			?></td>
		</tr>
<?php
endforeach;
?>
	</table>
	</div>
	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
</div>
<div class="actions columns">
	<?php
	$links = [$this->Html->iconLink('view_24.png', ['controller' => 'People', 'action' => 'view', '?' => ['person' => $official->id]])];
	if ($all) {
		$links[] = $this->Html->link(__('Upcoming Games'), ['action' => 'officiating_schedule'], ['class' => $this->Bootstrap->navPillLinkClasses()]);
	} else {
		$links[] = $this->Html->link(__('All Games'), ['action' => 'officiating_schedule', '?' => ['all' => true]], ['class' => $this->Bootstrap->navPillLinkClasses()]);
	}

	echo $this->Bootstrap->navPills($links);
	?>
</div>
