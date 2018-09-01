<?php
use Cake\I18n\FrozenDate;
?>
<div id="gamestoday">
<p><?php
if (empty($games)) {
	echo __('No games today');
} else {
	echo $this->Html->link(__n('{0} game today', '{0} games today', $games, $games), ['action' => 'day', 'date' => FrozenDate::now()->toDateString()], ['target' => '_top']);
}
?></p>
</div>
