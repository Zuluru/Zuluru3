<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('Selecting a facility does not guarantee that you will play there, it simply increases your chances. You cannot select a specific {0} at a facility, only the facility itself.',
	Configure::read('UI.field')
) ?></p>
<p><?= __('Drag individual facilities up and down so that your top choice is first in the list. If there is somewhere that you really don\'t want to play, select all facilities and put that one last.') ?></p>
