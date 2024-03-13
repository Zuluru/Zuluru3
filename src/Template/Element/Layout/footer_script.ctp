<?php
/**
 * @var \App\View\AppView $this
 */

$footer_scripts = $this->fetch('footer_script');
if (!empty($footer_scripts)) {
	echo $this->Html->scriptBlock("zjQuery(document).ready(function() {\n" . $footer_scripts . "\n});");
}
