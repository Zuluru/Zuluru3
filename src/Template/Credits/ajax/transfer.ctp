<?php
/**
 * @type \App\Model\Entity\Credit $credit
 */
?>
<?= $this->element('People/search_results', [
	'extra_url' => [
		__('Transfer Credit') => ['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credit->id, 'return' => false],
	]
])
?>
