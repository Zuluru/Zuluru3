<?php
if (isset($error)) {
	$this->Html->scriptBlock("alert('$error')", ['buffer' => true]);
} else {
	$taken = __('{0} taken', $taken);
	$this->Html->scriptBlock("jQuery('#score_team_{$this->request->data['team_id']}').find('span.timeout_count').html('$taken'); jQuery('#TwitterMessage').val('$twitter');", ['buffer' => true]);
}
