<?php
use Cake\Core\Configure;
use App\Controller\AppController;
?>

<h2><?php
	echo $person->full_name;
	echo $this->element('People/player_photo', ['person' => $person, 'photo' => $photo]);
?></h2>
<?php
$view_contact = $is_me || Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator || $is_captain || $is_my_captain || $is_my_coordinator || $is_division_captain;

echo $this->element('People/contacts', compact(['person', 'view_contact']));

if (Configure::read('Perm.is_logged_in') && Configure::read('feature.badges') && !empty($person->badges)) {
	echo $this->Html->tag('br');
	foreach ($person->badges as $badge) {
		echo $this->Html->iconLink("{$badge->icon}_48.png", ['controller' => 'Badges', 'action' => 'view', 'badge' => $badge->id],
			['alt' => $badge->name, 'title' => $badge->description]);
	}
}

if ($view_contact) {
	if (AppController::_isChild($person)) {
		$related_to = $this->UserCache->read('RelatedTo', $person->id);
		if (!empty($related_to)) {
?>
	<h3><?= __('Contacts') ?></h3>
<?php
			foreach ($related_to as $relative){
				if ($relative->_joinData->approved){
					// TODOBOOTSTRAP: Work on spacing, especially where there are multiple relatives. Pass the formatted name to the element? Replace <br> with <p> and style them differently in tooltips?
					echo $this->Html->tag('strong', $this->Html->link($relative->full_name, ['controller' => 'People', 'action' => 'view', 'person' => $relative->id])) . '<br />';
					echo $this->element('People/contacts', ['person' => $relative, 'view_contact' => $view_contact]) . '<br />';
				}
			}
		}
	}
}
