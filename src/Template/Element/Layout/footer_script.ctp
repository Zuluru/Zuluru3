<?php
$footer_scripts = $this->fetch('footer_script');
if (!empty($footer_scripts)) {
	echo $this->Html->scriptBlock("jQuery(document).ready(function() {\n" . $footer_scripts . "\n});");
}
