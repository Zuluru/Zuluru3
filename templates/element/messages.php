<?php
/**
 * @var \App\View\AppView $this
 * @var array $messages
 */

foreach ($messages as $message) {
	echo $this->Html->formatMessage($message, 'p');
}
