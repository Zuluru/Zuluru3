<?php
$relatives = $this->UserCache->allActAs();
if (!empty($relatives)) {
	$url = array_merge(['action' => $this->getRequest()->getParam('action')], $this->getRequest()->getQueryParams());
	$links = [];

	foreach ($relatives as $id => $relative) {
		$person = \App\Core\UserCache::getInstance()->read('Person', $id);
		$identity = new \App\Authentication\ActAsIdentity($this->getRequest()->getAttribute('authorization'),
			$this->getRequest()->getAttribute('authentication')->buildIdentity($person)
		);
		if ($identity->can('show_registration', \App\Controller\PeopleController::class)) {
			$url['act_as'] = $id;
			$links[$id] = $this->Html->link($relative, $url);
		}
	}

	if (!empty($links)) {
		echo $this->Html->para(null, __('Note that you are registering {0}. To register {1} instead, click on their name.',
			$this->UserCache->read('Person.full_name'),
			implode(' ' . __('or') . ' ', $links)));
	}
}
