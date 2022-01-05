<?php
/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Upload $photo
 */

$visible_properties = $person->getVisible();
?>

<h2><?php
	echo $person->full_name;
	echo $this->element('People/player_photo', ['person' => $person, 'photo' => $photo]);
?></h2>
<?php
echo $this->element('People/contacts', compact(['person']));

if (!empty($person->badges)) {
	echo $this->Html->tag('br');
	foreach ($person->badges as $badge) {
		echo $this->Html->iconLink("{$badge->icon}_48.png", ['controller' => 'Badges', 'action' => 'view', 'badge' => $badge->id],
			['alt' => $badge->name, 'title' => $badge->description]);
	}
}

if ($this->Authorize->can('view_contacts', $person)) {
	$related_to = $this->UserCache->read('RelatedTo', $person->id);
	if (!empty($related_to)) {
?>
	<h3><?= __('Contacts') ?></h3>
<?php
		foreach ($related_to as $relative){
			if ($relative->_joinData->approved){
				// TODOBOOTSTRAP: Work on spacing, especially where there are multiple relatives. Pass the formatted name to the element? Replace <br> with <p> and style them differently in tooltips?
				echo $this->Html->tag('strong', $this->Html->link($relative->full_name, ['controller' => 'People', 'action' => 'view', 'person' => $relative->id])) . '<br />';
				echo $this->element('People/contacts', ['person' => $relative]) . '<br />';
			}
		}
	}
}

if (!empty($person->notes)) {
	foreach ($person->notes as $note) {
		echo $note->note;
	}
}
