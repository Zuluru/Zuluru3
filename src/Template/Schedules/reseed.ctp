<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var string $type
 */

$this->Breadcrumbs->add(__('Division'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('Add Games'));
if ($type == 'crossover') {
	$this->Breadcrumbs->add(__('Crossover Details'));
} else {
	$this->Breadcrumbs->add(__('Re-seeding Details'));
}
?>

<div class="schedules add">

	<p><?php
	if ($type == 'crossover') {
		echo __('You are defining crossover games. Select which pool positions feed into these games below.');
	} else {
		echo __('You are re-seeding teams into power pools. Select which pool positions feed into these pools below.');
	}
	echo ' ';
	echo __('For example, selecting the "1st" option in the "Pool B" sub-group of options will place the team with the best record in Pool B in that slot.');
	echo ' ';
	echo __('Selecting the "2nd" option in the "1st place teams" sub-group of options will find the team with the second-best record among all of the teams that finished 1st in their pool.');
	?></p>

<?php
echo $this->Form->create($division, ['align' => 'horizontal']);
$division->_options->step = 'reseed';
echo $this->element('hidden', ['model' => '_options', 'fields' => $division->_options]);
?>

	<fieldset>
		<legend><?= __('{0} Details', $type == 'crossover' ? __('Crossover') : __('Re-seeding')) ?></legend>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th><?= __('Pool') ?></th>
						<th><?= __('Seed') ?></th>
						<th><?= __('Qualifier') ?></th>
					</tr>
				</thead>
				<tbody>
<?php
foreach ($division->_options->pools as $i => $pool):
	$display_name = $pool->name;
	for ($team = 1; $team <= $pool->count; ++ $team):
?>
					<tr>
						<td><?= $display_name ?></td>
						<td><?= $team ?></td>
						<td><?= $this->Form->control("_options.pools.$i.pools_teams.$team.qualifier", [
							'label' => false,
							'options' => $options,
							'empty' => 'Select:',
						]) ?></td>
					</tr>
<?php
		$display_name = '';
	endfor;
endforeach;
?>

				</tbody>
			</table>
		</div>
	</fieldset>

<?php
echo $this->Form->button(__('Next step'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?= $this->element('Divisions/actions', [
	'league' => $division->league,
	'division' => $division,
	'format' => 'list',
]) ?>
	</ul>
</div>
