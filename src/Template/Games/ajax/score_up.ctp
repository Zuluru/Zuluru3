<?php
if (isset($message)) {
	$this->Html->scriptBlock("alert('$message')", ['buffer' => true]);
} else {
	$this->Html->scriptBlock("jQuery('#score_team_{$this->getRequest()->getData('team_id')}').find('td.score').html('$team_score'); jQuery('#TwitterMessage').val('$twitter');", ['buffer' => true]);
}
