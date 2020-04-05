<?php
use Cake\Core\Configure;
?>

<p><?= __('The name should be descriptive of the facility in general (e.g. the name of the school or complex where the {0} are found).',
	Configure::read('UI.fields')
);
?></p>
