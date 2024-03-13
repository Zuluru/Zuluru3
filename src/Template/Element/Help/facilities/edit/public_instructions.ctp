<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('Use this area for any general instructions that don\'t fall under other categories. Examples might include {0} setup notes, presence of playgrounds for those with children, etc.',
	Configure::read('UI.field')
);
?></p>
<p><?= __('If you leave this field blank, it will not be shown on the facility view page.') ?></p>
