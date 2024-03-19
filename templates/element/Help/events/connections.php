<?php
/**
 * @var \App\View\AppView $this
 */
?>
<p><?= __('The "{0}" page is used to define logical connections between your events.',
	__('Manage Connections')
) ?></p>
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
