<?php
if (isset($message)) {
	$this->Html->scriptBlock("alert('$message')", ['buffer' => true]);
} else {
	$taken = __('{0} taken', $taken);
	$this->Html->scriptBlock("zjQuery('#score_team_{$this->getRequest()->getData('team_id')}').find('span.timeout_count').html('$taken'); zjQuery('#TwitterMessage').val('$twitter');", ['buffer' => true]);
}
