<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $taken
 */

if (isset($message)) {
	$this->Html->scriptBlock("alert('$message')", ['buffer' => true]);
} else {
	$taken = __('{0} taken', $taken);
	$this->Html->scriptBlock("zjQuery('#score_team_{$this->getRequest()->getData('team_id')}').find('span.timeout_count').html('$taken');", ['buffer' => true]);
}
