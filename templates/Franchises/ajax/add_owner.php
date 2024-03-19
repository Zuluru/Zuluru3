<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Franchise $franchise
 */

?>
<?= $this->element('People/search_results', ['extra_url' => [__('Make owner') => ['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id]]]);
