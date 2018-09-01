<?php
$this->Html->addCrumb(__('Groups'));
$this->Html->addCrumb(__('List'));
?>

<div class="groups index">
	<h2><?= __('Permission Groups') ?></h2>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= __('Name') ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach ($groups as $group):
?>
			<tr>
				<td><?= h($group->name) ?></td>
				<td class="actions"><?php
				if ($group->name != 'Administrator') {
					if ($group->active) {
						echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', 'group' => $group->id]]);
					} else {
						echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', 'group' => $group->id]]);
					}
				}
				?></td>
			</tr>

<?php
endforeach;
?>
		</tbody>
	</table>
	</div>
	<p><?= __('Active groups are available for people to select during account setup or edit, or be assigned to by an admin. The "Player" group will always be available for admins; deactivating this only means that it can\'t be used at the time of account creation (e.g. if you are running a youth league where most accounts will be parents). The "Administrator" group cannot be deactivated.') ?></p>
</div>
