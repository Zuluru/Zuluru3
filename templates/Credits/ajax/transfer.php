<?php
/**
 * @var \App\Model\Entity\Credit $credit
 */
?>
<?= $this->element('People/search_results', [
	'extra_url' => [
		$this->Html->iconImg('move_24.png', ['alt' => __('Transfer Credit'), 'title' => __('Transfer')]) => [
			'controller' => 'Credits', 'action' => 'transfer', '?' => ['credit' => $credit->id, 'return' => false],
			'_link_opts' => ['escape' => false, 'class' => 'icon', 'confirm' => __('Are you sure you want to transfer this credit to this person?')]
		],
	]
])
?>
