<?php
foreach ($messages as $message) {
	echo $this->Html->formatMessage($message, 'p');
}
