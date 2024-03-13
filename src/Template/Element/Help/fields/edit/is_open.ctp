<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('Typically, a {0} should be marked as "open" if it is in use by a current or upcoming league. By "closing" {1} not currently in use, the {0} list will only display those facilities that players might need to travel to, making it easier for them to find relevant information.',
	Configure::read('UI.field'), Configure::read('UI.fields')
);
?></p>
