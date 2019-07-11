<p><?= __('The "{0}" page is used to update details of your league. Only coordinators have permission to edit league details.',
	__('Edit League')
) ?></p>
<p><?= __('The "{0}" page is essentially identical to this page.',
	__('Create League')
) ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'leagues/edit',
	'topics' => [
		'name',
	],
]);
