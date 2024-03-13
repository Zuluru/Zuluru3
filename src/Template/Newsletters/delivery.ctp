<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Newsletter $newsletter
 */

use App\Controller\AppController;

$this->Html->addCrumb(__('Newsletter'));
$this->Html->addCrumb($newsletter->name);
$this->Html->addCrumb(__('Delivery Report'));
?>

<div class="newsletters view">
	<h2><?= __('Delivery Report') . ': ' . $newsletter->name ?></h2>
	<p><?= __('This newsletter has been delivered to {0} people. Click letters below to see recipients whose last name start with that letter.', count($newsletter->deliveries)) ?></p>
<?php
$people = collection($people)->indexBy('id')->toArray();
$newsletter->deliveries = collection($newsletter->deliveries)->indexBy('person_id')->toArray();

$letters = [];
foreach ($people as $person) {
	$letters[strtoupper($person->last_name[0])] = true;
}
?>

	<p>
<?php
foreach (array_keys($letters) as $letter):
?>
		<a href="#" class="letter_link" id="letter_<?= $letter ?>"><?= $letter ?></a>
<?php
endforeach;
?>

	</p>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Recipient') ?></th>
					<th><?= __('Date Sent') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
foreach ($people as $person):
?>
				<tr class="letter letter_<?= strtoupper($person->last_name[0]) ?>">
					<td><?= $this->Html->link($person->full_name, ['controller' => 'People', 'action' => 'view', 'person' => $person->id]) ?></td>
					<td><?= $this->Time->date($newsletter->deliveries[$person->id]->created) ?></td>
				</tr>
<?php
endforeach;
?>
			</tbody>
		</table>
	</div>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('newsletter_send_32.png',
	['action' => 'send', 'newsletter' => $newsletter->id],
	['alt' => __('Send'), 'title' => __('Send')]));
echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
	['action' => 'edit', 'newsletter' => $newsletter->id, 'return' => AppController::_return()],
	['alt' => __('Edit'), 'title' => __('Edit')]));
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'newsletter' => $newsletter->id],
	['alt' => __('Delete'), 'title' => __('Delete Newsletter')],
	['confirm' => __('Are you sure you want to delete this newsletter?')]));
echo $this->Html->tag('li', $this->Html->link(__('List Newsletters'), ['action' => 'index']));
echo $this->Html->tag('li', $this->Html->iconLink('newsletter_add_32.png',
	['action' => 'add'],
	['alt' => __('Add'), 'title' => __('Add Newsletter')]));
?>
	</ul>
</div>

<?php
$this->Html->scriptBlock('
function display_letter(id) {
	zjQuery(".letter").css("display", "none");
	zjQuery("." + id).css("display", "");
}
', ['buffer' => true]);

$this->Html->scriptBlock('
display_letter("letter_A");
zjQuery(".letter_link").bind("click", function () { display_letter(zjQuery(this).attr("id")); return false;});
', ['buffer' => true]);
