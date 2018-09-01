<?php
if (isset($error)) {
	$this->Html->scriptBlock("alert('$error')", ['buffer' => true]);
} else {
	$this->Html->scriptBlock("jQuery('#score_team_{$this->request->data['team_id']}').find('td.score').html('$team_score'); jQuery('#TwitterMessage').val('$twitter');", ['buffer' => true]);
}
