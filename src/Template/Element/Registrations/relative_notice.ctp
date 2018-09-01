<?php
use App\Controller\AppController;

$relatives = $this->UserCache->allActAs();
if (!empty($relatives)) {
	$url = array_merge(['action' => $this->request->action], $this->request->query);
	$links = [];

	foreach ($relatives as $id => $relative) {
		if (AppController::_showRegistration($id)) {
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
