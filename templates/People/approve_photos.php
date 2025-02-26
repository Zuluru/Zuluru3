<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Upload[] $photos
 */

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add(__('Approve Photos'));
?>

<div class="people photos">
	<h2><?= __('Approve Photos') ?></h2>

	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
<?php
foreach ($photos as $photo):
?>
			<tr>
				<td><?= $this->element('People/block', ['person' => $photo->person]) ?></td>
				<td><?= $this->element('People/player_photo', ['person' => $photo->person, 'photo' => $photo]) ?></td>
				<td class="actions"><?php
				echo $this->Jquery->ajaxLink(__('Approve'), [
					'url' => ['controller' => 'People', 'action' => 'approve_photo', '?' => ['person' => $photo->person->id]],
					'disposition' => 'remove_closest',
					'selector' => 'tr',
				]);
				echo $this->Jquery->ajaxLink(__('Delete'), [
					'url' => ['controller' => 'People', 'action' => 'delete_photo', '?' => ['person' => $photo->person->id]],
					'disposition' => 'remove_closest',
					'selector' => 'tr',
				]);
				?></td>
			</tr>
<?php
endforeach;
?>
		</table>
	</div>
</div>
