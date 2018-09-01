<?php
if (!is_array($messages)) {
	trigger_error('TODOTESTING', E_USER_WARNING);
	exit;
}

foreach ($messages as $message) {
	echo $this->Html->formatMessage($message, 'p');
}
