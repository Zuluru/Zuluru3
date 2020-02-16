<?php
use Cake\Core\Configure;

$field = Configure::read('UI.field');
$fields = Configure::read('UI.fields');
?>
<p><?= __('All teams must respect the following rules for all {0}. Note that some facilities have additional restrictions listed that must also be followed.', $fields) ?></p>
<ul>
	<li><?= __('Garbage containers may not exist at all facilities. Do not leave any garbage behind when you leave -- even if it isn\'t yours. Take extra care to remove any hazardous items (i.e. bottlecaps, glass) to avoid injury to others.') ?></li>
<?php
if (Configure::read('feature.dog_questions')):
?>
	<li><?= __('If dogs are not allowed at a particular {0}, you <strong>must</strong> respect this. If dogs are permitted at a {0}, you must clean up after your pet and take the waste away with you.', $field) ?></li>
<?php
endif;
?>
	<li><?= __('By law, alcohol is not permitted at any league {0}, and can cause us to lose the ability to play there.', $field) ?></li>
</ul>
<p><?= __('If {0} are lost due to the actions of a particular player or team, they will be <strong>removed from the league</strong>.', $fields) ?></p>
