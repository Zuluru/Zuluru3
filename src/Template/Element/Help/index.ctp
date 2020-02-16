<?php
use Cake\Core\Configure;

$identity = $this->Authorize->getIdentity();

// TODOBOOTSTRAP: Bootstrap grid stuff to float these sections next to each other?
?>

<p><?= __('{0} has online help throughout the system, accessed by clicking the {1} icons on any page. These bits of documentation are also collected here in two forms: as guides for various user types and grouped by functional area.',
	ZULURU,
	$this->Html->iconImg('help_16.png')
) ?></p>
<h2><?= __('User Guides') ?></h2>
<ul>
<?php
echo $this->Html->tag('li', $this->Html->link(__('New Users'), ['controller' => 'Help', 'action' => 'guide', 'new_user']));
echo $this->Html->tag('li', $this->Html->link(__('Advanced Users'), ['controller' => 'Help', 'action' => 'guide', 'advanced']));
echo $this->Html->tag('li', $this->Html->link(__('Coaches/Captains'), ['controller' => 'Help', 'action' => 'guide', 'captain']));
if ($identity && ($identity->isManager() || $identity->isCoordinator())) {
	echo $this->Html->tag('li', $this->Html->link(__('Coordinators'), ['controller' => 'Help', 'action' => 'guide', 'coordinator']));
}
if ($identity && $identity->isManager()):
	echo $this->Html->tag('li', __('Administrators'));
?>
	<ul>
<?php
	echo $this->Html->tag('li', $this->Html->link(__('Site Setup and Configuration'), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'setup']));
	echo $this->Html->tag('li', $this->Html->link(__('Player Management'), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'players']));
	echo $this->Html->tag('li', $this->Html->link(__('League Management'), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'leagues']));
	echo $this->Html->tag('li', $this->Html->link(__('{0} Management', Configure::read('UI.field_cap')), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'fields']));
	echo $this->Html->tag('li', $this->Html->link(__('Registration'), ['controller' => 'Help', 'action' => 'guide', 'administrator', 'registration']));
?>
	</ul>
<?php
endif;
?>
</ul>
<h2><?= __('Functional Areas') ?></h2>
<ul>
<?php
echo $this->Html->tag('li', $this->Html->link(__('People'), ['controller' => 'Help', 'action' => 'people']));
if (Configure::read('feature.registration')) {
	if ($identity && $identity->isManager()) {
		echo $this->Html->tag('li', $this->Html->link(__('Events'), ['controller' => 'Help', 'action' => 'events']));
	}
	echo $this->Html->tag('li', $this->Html->link(__('Registration'), ['controller' => 'Help', 'action' => 'registration']));
}
if ($identity && $identity->isManager()) {
	echo $this->Html->tag('li', $this->Html->link(__('Waivers'), ['controller' => 'Help', 'action' => 'waivers']));
}
echo $this->Html->tag('li', $this->Html->link(__('Teams'), ['controller' => 'Help', 'action' => 'teams']));
echo $this->Html->tag('li', $this->Html->link(__('Games'), ['controller' => 'Help', 'action' => 'games']));
if ($identity && ($identity->isManager() || $identity->isCoordinator())) {
	echo $this->Html->tag('li', $this->Html->link(__('Schedules'), ['controller' => 'Help', 'action' => 'schedules']));
	echo $this->Html->tag('li', $this->Html->link(__('Leagues'), ['controller' => 'Help', 'action' => 'leagues']) .
		' ' . __('and') . ' ' .
		$this->Html->link(__('Divisions'), ['controller' => 'Help', 'action' => 'divisions']));
}
if ($identity && $identity->isManager()) {
	echo $this->Html->tag('li', $this->Html->link(__('{0} and {1}', __('Facilities'), Configure::read('UI.fields_cap')), ['controller' => 'Help', 'action' => 'facilities']));
	echo $this->Html->tag('li', $this->Html->link(__('Rules Engine'), ['controller' => 'Help', 'action' => 'rules']));
	echo $this->Html->tag('li', $this->Html->link(__('Configuration'), ['controller' => 'Help', 'action' => 'settings']));
}
?>
</ul>
