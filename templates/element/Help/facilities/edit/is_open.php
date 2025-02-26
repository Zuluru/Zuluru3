<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('A facility should be marked as "open" as long as any {0} at the facility is potentially in use. Only close a facility when it is no longer available.',
	Configure::read('UI.field')
);
?></p>
