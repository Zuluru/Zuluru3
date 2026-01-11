<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person[] $people
 */
if (isset($search_error)):
	echo $this->Html->para(null, $search_error);
elseif (isset($people)):
	if ($this->Paginator->hasPage(2)) {
		echo $this->Html->para('warning-message', __('Note that there are many results for your search, and not all have been shown. Use a more specific search term to narrow it down.'));
	}
	foreach ($people as $person):
		$affiliates = collection($person->affiliates ?? [])->extract('id')->toArray();
		$mine = array_intersect($affiliates, $this->UserCache->read('ManagedAffiliateIDs'));
		$is_person_manager = !empty($mine);
		echo $this->Form->control("player.$person->id", [
			'label' => [
				'text' => $this->element('People/block', ['person' => $person, 'link' => false]),
				'escape' => false,
			],
			'type' => 'checkbox',
		]);
	endforeach;
endif;
