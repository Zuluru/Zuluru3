<?php
/**
 * @type \App\Model\Entity\Team $team
 * @type \App\Model\Entity\Event $event
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('Team'));
$this->Html->addCrumb(__('Add Player'));
$this->Html->addCrumb($team->name);
?>

<div class="teams add_player">
	<h2><?= __('Add Player') . ': ' . $team->name ?></h2>

<?php
if (empty($event->registrations)) {
	echo $this->Html->para(null, __('All people registered for {0} are already on this roster.', $event->name));
} else {
	echo $this->Html->para(null, __('The following people have registered and paid for {0} but are not on the current roster:', $event->name));
	echo $this->Form->create(false, ['align' => 'horizontal']);
	echo $this->Form->hidden('event', ['value' => $event->id]);

	$cannot = [];

	foreach ($event->registrations as $registration) {
		// TODOBOOTSTRAP: Better formatting of this list
		if ($registration->can_add === true) {
			echo $this->Form->input("player.{$registration->person_id}.role", [
				'label' => [
					'text' => $this->element('People/block', ['person' => $registration->person, 'link' => false]),
					'escape' => false,
				],
				'type' => 'radio',
				'options' => $registration->roster_role_options,
				'default' => 'none',
				'hiddenField' => false,
			]);

			echo $this->Form->hidden("player.{$registration->person_id}.position", ['value' => 'unspecified']);

			// TODO: If the team has numbers, add a field for entering that here
		} else {
			$cannot[] = $this->Html->tag('span', $this->Html->link($registration->person->full_name, ['controller' => 'People', 'action' => 'view', 'person' => $registration->person->id]), ['title' => $this->Html->formatMessage($registration->can_add, null, true)]);
		}
	}

	echo $this->Form->button(__('Add'), ['class' => 'btn-success']);
	echo $this->Form->end();
	if (!empty($cannot)) {
		echo $this->Html->para(null, __('The following people cannot be added to the roster. Hover your mouse over a name to see the reason why.'));
		echo $this->Html->para(null, implode(', ', $cannot) . '.');
	}
}
?>

</div>
