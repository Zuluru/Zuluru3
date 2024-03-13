<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Affiliate $affiliate
 */
?>
<?= $this->element('People/search_results', ['extra_url' => [__('Add as manager') => ['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id]]]);
