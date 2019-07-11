<?php
use Cake\Core\Configure;

$identity = $this->Identity->get();
?>

<h2><?= __('New User Guide') ?></h2>
<p><?= __('For a new user, {0} can be a little overwhelming. This guide will help you through the most important things to get you started. After that, you may be interested in the {1}, and if you plan on running a team, the {2} is a useful resource.',
	ZULURU,
	$this->Html->link(__('advanced users guide'), ['controller' => 'Help', 'action' => 'guide', 'advanced']),
	$this->Html->link(__('captains guide'), ['controller' => 'Help', 'action' => 'guide', 'captain'])
) ?></p>

<h2><?= __('User Account and Profile') ?></h2>
<p><?= __('Some features of {0} (e.g. schedules and standings) are available for anyone to use. However, to participate in the {0}, you must have a user account on the system.',
	ZULURU, Configure::read('organization.name')
);
echo ' ';
if ($identity) {
	echo __('You are already logged in to the system, so it seems that you\'ve successfully taken care of this step. For the record, your username is "{0}" and your ID number is {1}.',
		$identity->user_name, $identity->person->id
	);
} else if (Configure::read('feature.control_account_creation')) {
	echo __('If you don\'t already have an account, {0} to get yourself set up.',
		$this->Html->link(__('follow these directions'), Configure::read('App.urls.register'))
	);
} else {
	echo __('This site manages user accounts through {0}. If you don\'t already have an account, {1} to get yourself set up.',
		Configure::read('feature.authenticate_through'), $this->Html->link(__('follow these directions'), Configure::read('App.urls.register'))
	);
}
?></p>

<p><?php
if (!Configure::read('feature.auto_approve')) {
	echo __('Next, each person must have their completed profile approved by an administrator.');
}
echo ' ';
if ($identity) {
	if (!$identity->person->complete) {
		echo __('To complete your profile, {0}',
			$this->Html->link(__('follow these directions'), ['controller' => 'People', 'action' => 'edit'])
		);
		$complete = __('and will not be until you have completed it');
	} else {
		$complete = __('but this should happen soon');
	}

	switch($identity->person->status) {
		case 'new':
			echo __('Your profile has not yet been approved, {0}. Until then, you can continue to use the site, but may be limited in some areas.', $complete);
			break;
		case 'active':
			echo __('Your profile has been approved, so you should be free to access all features of the site.');
			break;
		case 'inactive':
			echo __('Your profile is currently {0}, so you can continue to use the site, but may be limited in some areas. To reactivate, {1}.',
				__($identity->person->status),
				$this->Html->link(__('click here'), ['controller' => 'People', 'action' => 'reactivate'])
			);
			break;
	}
} else {
	echo __('After you have created your account and completed your profile, it is normally approved within one business day, often sooner, but you can use most features of the site while you are waiting for this.');
}
?></p>

<?php
if (Configure::read('feature.registration')):
?>
<h2><?= __('Registration') ?></h2>
<?php
	echo $this->element('Help/topics', [
		'section' => 'registration',
		'topics' => [
			'introduction',
			'wizard',
		],
		'compact' => true,
	]);
endif;
?>
<h2><?= __('Teams') ?></h2>
<?php
echo $this->element('Help/topics', [
	'section' => 'teams',
	'topics' => [
		'joining_teams',
		'my_teams',
	],
	'compact' => true,
]);
