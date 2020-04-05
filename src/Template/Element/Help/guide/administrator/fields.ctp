<?php
use Cake\Core\Configure;
?>

<h2><?= __('{0}: {1}', __('Administrator Guide'), __('{0} Management', Configure::read('UI.field_cap'))) ?></h2>
<p><?= __('{0} management is fairly straight-forward. {1} are handled in a hierarchical structure.',
	Configure::read('UI.field_cap'), Configure::read('UI.fields_cap')
) ?></p>

<h3><?= __('Regions') ?></h3>
<p><?= __('Your organization will have one or more regions, which you manage through the {0} -> Regions area. Examples might be North, South, East, Central, or named after neighbourhoods or political areas.',
	Configure::read('UI.fields_cap')
) ?></p>
<p><?= __('Distinct regions are primarily useful if you want to support teams with regional preferences.') ?></p>

<h3><?= __('Facilities') ?></h3>
<p><?= __('Each region will have one or more facilities. A facility is generally any park, school, stadium, etc. where you have procured one or more {0}.',
	Configure::read('UI.fields')
) ?></p>
<?= $this->element('Help/facilities/edit/name') ?>
<p><?= __('Each facility is also given a name three-letter code. For example, "Maple Grove Park" might be abbreviated as MGP or MAP.') ?></p>

<h3><?= Configure::read('UI.fields_cap') ?></h3>
<p><?= __('Each facility will have one or more {0}. A {1} is the space where a single game can take place.',
	Configure::read('UI.fields'), Configure::read('UI.field')
) ?></p>
<?= $this->element('Help/fields/edit/num') ?>
<p><?= __('If you use a single large "{0}" to host more than one game at a time, then that must be configured as multiple {1}.',
	Configure::read('UI.field'), Configure::read('UI.fields')
) ?></p>

<h3><?= __('Layouts') ?></h3>
<p><?= __('{0} includes a {1} layout viewer and editor integrated with Google Maps. When you are viewing or editing a {1}, other {2} at that facility will also be shown. Clicking the marker for a {1} will "activate" that {1}; in the viewer it will show details about that {1}, and in the editor it will change which {1} you are editing. In the editor, you can drag {2} around by their marker, and adjust their size and angle with buttons to the right.',
	ZULURU, Configure::read('UI.field'), Configure::read('UI.fields')
) ?></p>

<h4><?= __('Parking and Entrances') ?></h4>
<p><?= __('The editor also includes "{0}" and "{1}" buttons. Click these, then click on the map to add a parking or entrance marker. These markers can be dragged around like {2}, and can be deleted by simply clicking on them and confirming the deletion. Parking and entrance markers are facility-wide; you do not need to set up parking and entrances separately for each {3}.',
	__('Add Parking'), __('Add Entrance'),
	Configure::read('UI.fields'), Configure::read('UI.field')
) ?></p>

<h3><?= __('Open and Closed {0} and Facilities',
	Configure::read('UI.fields_cap')
) ?></h3>
<p><?= __('For historical purposes, once a game has been scheduled at a {0}, that {0} cannot be deleted from the system. Similarly, once a {0} has been added to a facility, that facility cannot be deleted. However, there are times when you no longer wish a {0} or facility to show up in the {1}. When this happens, you can close {2} or facilities.',
	Configure::read('UI.field'), $this->Html->link(__('facility list'), ['controller' => 'Facilities']), Configure::read('UI.fields')
) ?></p>
<p><?= __('By closing a {0}, you are temporarily removing it from circulation. It will no longer show up on the {1} list or the layout map for {1} at that facility, and you will not be able to add game slots for it. Facilities with no open {1} will not show up on the {0} list or the {2}.',
	Configure::read('UI.field'), Configure::read('UI.fields'),
	$this->Html->link(__('map of all {0}', Configure::read('UI.fields')), ['controller' => 'Maps'], ['target' => 'map'])
) ?></p>
<p><?= __('By closing a facility, you are essentially permanently removing it, and all of its {0}, from circulation. It will no longer show up on the map of all {0} or the {1} list. This should be done when a facility is permanently closed or permits are lost or dropped. Closing a facility also closes all associated {0}. Closed facilities can be re-opened, but its {0} will need to be re-opened individually.',
	Configure::read('UI.fields'), Configure::read('UI.field')
) ?></p>
