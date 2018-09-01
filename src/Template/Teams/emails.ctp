<?php
/**
 * @type \App\Model\Entity\Team $team
 */

use App\Model\Entity\Person;
use Cake\Core\Configure;

$this->Html->addCrumb(__('Team'));
$this->Html->addCrumb(__('Player Emails'));
$this->Html->addCrumb($team->name);

$grouped = collection($team->people)->groupBy(function (Person $person) {
	return in_array($person->_joinData->role, Configure::read('privileged_roster_roles')) ? 1 :
		(in_array($person->_joinData->role, Configure::read('playing_roster_roles')) ? 2 : 3);
})->toArray();

$people = $cc = [];
if (array_key_exists(1, $grouped)) {
	$people = array_merge($people, $grouped[1]);
}
if (array_key_exists(2, $grouped)) {
	$people = array_merge($people, $grouped[2]);
}
if (array_key_exists(3, $grouped)) {
	$cc = array_merge($cc, $grouped[3]);
}
?>

<div class="teams emails">
<h2><?= __('Player Emails') . ': ' . $team->name ?></h2>

<?= $this->element('emails', compact('people', 'cc')) ?>

</div>
