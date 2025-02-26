<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 */
?>
<?= $this->element('People/roster_role', ['roster' => $person->_joinData, 'division' => $team->division]);
