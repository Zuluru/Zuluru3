<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Contact[] $contacts
 */

$this->Breadcrumbs->add(__('Contacts'));
$this->Breadcrumbs->add(__('List'));
?>

<div class="contacts index">
	<h2><?= __('Contacts') ?></h2>
<?php
if (empty($contacts)):
?>
	<p class="warning-message"><?= __('There are no contacts in the system.') ?></p>
<?php
else:
?>
	<p><?= $this->Paginator->counter(
		__('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total, starting on record {{start}}, ending on {{end}}')
	) ?></p>
	<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('name') ?></th>
				<th><?= $this->Paginator->sort('email') ?></th>
				<th class="actions"><?= __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$affiliate_id = null;
	$managed_affiliates = $this->UserCache->read('ManagedAffiliateIDs');
	foreach ($contacts as $contact):
		if (count($affiliates) > 1 && $contact->affiliate_id != $affiliate_id):
			$affiliate_id = $contact->affiliate_id;
?>
			<tr>
				<th colspan="3">
					<h3 class="affiliate"><?= h($contact->_matchingData['Affiliates']->name) ?></h3>
				</th>
			</tr>
<?php
		endif;
?>
			<tr>
				<td><?= h($contact->name) ?></td>
				<td><?= h($contact->email) ?></td>
				<td class="actions"><?php
				echo $this->Html->iconLink('edit_24.png',
					['action' => 'edit', '?' => ['contact' => $contact->id]],
					['alt' => __('Edit'), 'title' => __('Edit')]);
				echo $this->Form->iconPostLink('delete_24.png',
					['action' => 'delete', '?' => ['contact' => $contact->id]],
					['alt' => __('Delete'), 'title' => __('Delete')],
					['confirm' => __('Are you sure you want to delete this contact?')]);
				echo $this->Html->iconLink('email_24.png',
					['action' => 'message', '?' => ['contact' => $contact->id]],
					['alt' => __('Message Contact'), 'title' => __('Message Contact')]);
				?></td>
			</tr>

<?php
	endforeach;
?>
		</tbody>
	</table>
	</div>
	<nav class="paginator"><ul class="pagination">
		<?= $this->Paginator->numbers(['prev' => true, 'next' => true]) ?>
	</ul></nav>
<?php
endif;
?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Contact')]));
?>
	</ul>
</div>
