<p><?= __('The "edit facility" page is used to update details of your facilities.') ?></p>
<p><?= __('The "create facility" page is essentially identical to this page.') ?></p>
<?php
echo $this->element('Help/topics', [
		'section' => 'facilities/edit',
		'topics' => [
			'name',
			'code',
			'is_open',
			'location_street' => 'Address',
			'driving_directions',
			'parking_details',
			'transit_directions',
			'biking_directions',
			'washrooms',
			'public_instructions',
			'site_instructions',
			'sponsor',
		],
]);
