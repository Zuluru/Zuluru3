<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

$this->Breadcrumbs->add(__('Division'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('Add Games'));
$this->Breadcrumbs->add(__('Set Pool Details'));
?>

<div class="schedules add">

	<p><?= __('You are scheduling a tournament with multiple pools. Please provide the details for each pool.') ?></p>

<?php
echo $this->Form->create($division, ['align' => 'horizontal']);
$division->_options->step = 'details';
echo $this->element('hidden', ['model' => '_options', 'fields' => $division->_options]);
?>

	<fieldset>
		<legend><?= __('Pool Details') ?></legend>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th></th>
						<th><?= __('Name') ?></th>
<?php
if ($type != 'snake'):
?>
						<th><?= __('Number of Teams') ?></th>
<?php
endif;
?>
					</tr>
				</thead>
				<tbody>
<?php
for ($i = 1; $i <= $pools; ++ $i):
?>
					<tr>
						<td><?= $i ?>.</td>
						<td><?= $this->Form->i18nControls("_options.pools.$i.name", [
							'label' => false,
							'maxlength' => 2,
							'size' => 5,
							'default' => $name ++,
						]) ?></td>
<?php
	if ($type != 'snake'):
?>
						<td><?= $this->Form->control("_options.pools.$i.count", [
							'label' => false,
							'type' => 'number',
							'maxlength' => 2,
							'size' => 5,
							'default' => $sizes[$i],
						]) ?></td>
<?php
	endif;
?>
					</tr>
<?php
endfor;
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
<?= $this->element('Divisions/actions', [
	'league' => $division->league,
	'division' => $division,
	'format' => 'list',
]) ?>
</div>
