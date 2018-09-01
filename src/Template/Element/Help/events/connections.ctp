<p><?= __('The "manage connections" page is used to define logical connections between your events.') ?></p>
<?php
echo $this->element('Help/topics', [
		'section' => 'events/edit',
		'topics' => [
			'predecessor',
			'successorto' => 'Successor To',
			'successor',
			'predecessorto' => 'Predecessor To',
			'alternate',
		],
]);
