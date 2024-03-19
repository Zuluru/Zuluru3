<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */
?>
<?= $this->element('People/search_results', ['extra_url' => [__('Add as coordinator') => ['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id, 'return' => false]]]);
