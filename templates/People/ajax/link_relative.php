<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */
?>
<?= $this->element('People/search_results', [
	'extra_url' => [
		__('Link as relative') => ['controller' => 'People', 'action' => 'link_relative', 'person' => $person->id, 'return' => false, 'url_parameter' => 'relative'],
	]
]);
