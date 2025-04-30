<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game[] $games
 * @var \Cake\I18n\FrozenDate $date
 * @var string $sport
 * @var string[] $officials
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('Assign Officials'));
$this->Breadcrumbs->add($this->Time->date($date));
$this->Breadcrumbs->add(Inflector::humanize(__($sport)));
?>

<div class="games officials form" id="GameList">
	<h2><?= __('Assign Officials') . ': ' . $this->Time->date($date) ?></h2>
<?php
echo $this->Form->create(null, ['align' => 'horizontal']);
?>
	<div class="actions columns">
		<?= $this->Bootstrap->navPills([
			$this->Jquery->selectAll('#GameList', null, $this->Bootstrap->navPillLinkClasses()),
		]) ?>
	</div>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<tr>
				<th></th>
				<th><?= __('Time') ?></th>
				<th><?= __(Configure::read("sports.{$sport}.field_cap")) ?></th>
				<th><?= __('Home') ?></th>
				<th><?= __('Away') ?></th>
				<th><?= __('Officials') ?></th>
			</tr>
<?php
$last_slot = null;
foreach ($games as $game):
	$game->readDependencies();
	$same_slot = ($game->game_slot->id === $last_slot);
?>
			<tr<?= $game->published ? '' : ' class="unpublished"' ?>>
				<td><?php
					if (!$same_slot) {
						echo $this->Form->control("games.{$game->id}.assign", ['label' => false, 'type' => 'checkbox']);
					}
				?></td>
				<td><?php
					if (!$same_slot) {
						echo $this->Html->link($this->Time->timeRange($game->game_slot), ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]);
					}
				?></td>
				<td><?= $same_slot ? '' : $this->element('Fields/block', ['field' => $game->game_slot->field]) ?></td>
				<td><?php
					if (empty($game->home_team)) {
						if ($game->has('home_dependency')) {
							echo $game->home_dependency;
						} else {
							echo __('Unassigned');
						}
					} else {
						echo $this->element('Teams/block', ['team' => $game->home_team, 'options' => ['max_length' => 25]]);
					}
				?></td>
				<td><?php
					if (empty($game->away_team)) {
						if ($game->division->schedule_type === 'competition') {
							echo __('N/A');
						} else if ($game->has('away_dependency')) {
							echo $game->away_dependency;
						} else {
							echo __('Unassigned');
						}
					} else {
						echo $this->element('Teams/block', ['team' => $game->away_team, 'options' => ['max_length' => 25]]);
					}
				?></td>
				<td><?= $this->element('Games/officials', ['game' => $game, 'officials' => $game->officials, 'league' => $game->division->league]) ?></td>
			</tr>

<?php
	$last_slot = $game->game_slot->id;
endforeach;
?>
		</table>
	</div>
<?php
echo $this->Form->control('officials._ids', [
	'label' => false,
	'options' => $officials,
	'multiple' => true,
	'hiddenField' => false,
	'title' => __('Select the official(s) for the selected game(s)'),
]);
if ($this->Form->hasFormProtector()) {
	$this->Form->unlockField('asmSelect0');
	$this->Form->unlockField('officials._ids');
}
$this->Html->css('jquery.asmselect.css', ['block' => true]);
$this->Html->script('jquery.asmselect.js', ['block' => true]);
$this->Html->scriptBlock('zjQuery("select[multiple]").asmSelect({sortable:true});', ['buffer' => true]);

echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
