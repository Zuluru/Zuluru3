<?php
/**
 * @type $this \App\View\AppView
 * @type $events \App\Model\Entity\Event[]
 */

use App\Model\Entity\Event;
?>
<div>
<?php
echo $this->Selector->selector($events, 'Sport',
	function (Event $item) { return $item->division ? $item->division->league->sport : null; },
	null,
	'id', true, true);
echo $this->Selector->selector($events, 'Season',
	function (Event $item) { return $item->division ? $item->division->league->season : null; },
	null,
	'id', false, true);
echo $this->Selector->selector($events, 'Type',
	function (Event $item) { return $item->event_type->name; },
	function (Event $item) { return $item->event_type->id; },
	'id', true, true);
echo $this->Selector->selector($events, 'Day',
	// TODO: Handle multi-day divisions
	function (Event $item) { return $item->division && !empty($item->division->days) ? $item->division->days[0]->name : null; },
	function (Event $item) { return $item->division && !empty($item->division->days) ? $item->division->days[0]->id : null; },
	'id', true, true);
echo $this->Selector->selector($events, 'Competition', 'level_of_play', null, 'id', true, true);
echo $this->Selector->selector($events, 'Location', 'location', null, 'id', true, true);
?>
</div>
<div style="clear:both;"></div>
