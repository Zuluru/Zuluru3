<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $team_score
 * @var string $twitter
 */

if (isset($message)) {
	$this->Html->scriptBlock("alert('$message')", ['buffer' => true]);
} else {
	$this->Html->scriptBlock("zjQuery('#score_team_{$this->getRequest()->getData('team_id')}').find('td.score').html('$team_score'); zjQuery('#TwitterMessage').val('$twitter');", ['buffer' => true]);
}
