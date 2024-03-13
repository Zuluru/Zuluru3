<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<h2><?= __('Coach/Captain Guide') ?></h2>
<p><?= __('So, you want to be a coach or captain? {0} includes many tools and features to make this often thankless job much easier.', ZULURU) ?></p>

<?php
	echo $this->element('Help/topics', [
		'section' => 'teams',
		'topics' => [
			'edit' => [
				'title' => 'Team Creation and Editing',
				'image' => 'edit_32.png',
			],
		],
	]);

	echo $this->element('Help/topics', [
		'section' => 'teams',
		'topics' => [
			'roster_add' => [
				'title' => 'Adding Players',
				'image' => 'roster_add_32.png',
			],
			'roster_role' => 'Promoting/Demoting/Removing Players',
		],
	]);

	echo $this->element('Help/topics', [
		'section' => 'games',
		'topics' => [
			'recent_and_upcoming' => 'Recent and Upcoming Games',
		],
	]);
?>
<hr>
<h3><?= __('Responsibilities') ?></h3>
<p><?= __('As a coach or captain, you should familiarize yourself with the details of your league and division. {0} has many options for how{1} standings are calculated, playoffs are scheduled, etc. Knowing which options are in play for your team is important.',
	ZULURU, (Configure::read('feature.spirit') ? __(' spirit scores are collected,') : '')
) ?></p>
<p><?= __('You should also know who is coordinating your division. Coordinators are listed on the "{0}" page. These are the people responsible for setting your schedule,{1} etc. They do their best, but things do sometimes slip through the cracks, and you need to know who to contact in these cases.',
	__('View Division'),
	(Configure::read('feature.spirit') ? __(' handling spirit issues,') : '')
) ?></p>
