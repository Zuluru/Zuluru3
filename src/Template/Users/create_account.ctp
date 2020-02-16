<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;

$this->Html->addCrumb(__('Users'));
$this->Html->addCrumb(__('Create'));

$short = Configure::read('organization.short_name');
?>

<h3><?= __('Create Account') ?></h3>

<p><?= __('To create a new account, fill in all the fields below and click \'Submit\' when done.');
if (!Configure::read('feature.auto_approve')) {
	echo ' ' . __('Your account will be placed on hold until approved by an administrator. Once approved, you will have full access to the system.');
}
?></p>
<div class="alert alert-info alert-small" role="alert">
	<span><?= __('{0} If you already have an account from a previous season, {1}! Instead, please {2} to regain access to your account.',
		$this->Html->tag('strong', __('NOTE') . ': '),
		$this->Html->tag('strong', __('DO NOT CREATE ANOTHER ONE')),
		$this->Html->link(__('follow these instructions'), Configure::read('App.urls.resetPassword'))
	);
	?></span>
</div>
<p><?= __('Note that email and phone publish settings below only apply to regular people. Coaches and captains will always have access to view the phone numbers and email addresses of their confirmed players. All team coaches and captains will also have their email address viewable by other players.') ?></p>
<?php
if (Configure::read('App.urls.privacyPolicy')):
?>
<p><?= __('If you have concerns about the data {0} collects, please see our {1}.',
	$short,
	$this->Html->tag('strong', $this->Html->link(__('Privacy Policy'), Configure::read('App.urls.privacyPolicy'), ['target' => '_new']))
);
?></p>
<?php
endif;
?>

<?php
// Create the form and maybe add some spam-prevention tools
echo $this->Form->create($user, ['align' => 'horizontal']);
if (Configure::read('feature.antispam')):
?>
	<div id="spam_trap" style="display:none;">
<?php
	echo $this->Form->input('subject');
	echo $this->Form->hidden('timestamp', ['default' => FrozenTime::now()->toUnixString()]);
?>
	</div>
<?php
endif;

if (count($groups) > 1):
?>
	<fieldset>
		<legend><?= __('Account Type') ?></legend>
<?php
endif;

echo $this->Jquery->toggleInput('person.groups._ids', [
	'label' => __('Select all roles that apply to you.') . ' ' . __('You will be able to change these later, if required.'),
	'type' => 'select',
	'multiple' => 'checkbox',
	'options' => $groups,
	'hide_single' => true,
], [
	'values' => [
		GROUP_PLAYER => '.player',
		GROUP_PARENT => '.parent',
		GROUP_COACH => '.coach',
	],
]);

if ($this->Authorize->can('list_new', \App\Controller\PeopleController::class)) {
	$options = Configure::read('options.record_status');
	if (Configure::read('feature.auto_approve')) {
		unset($options['new']);
	}
	echo $this->Form->input('person.status', [
		'type' => 'select',
		'empty' => '---',
		'options' => $options,
	]);
}

if (count($groups) > 1):
?>
	</fieldset>
<?php
endif;
?>

	<fieldset>
		<legend><?= __('Your Information') . ' ' . $this->Html->tag('span', __('(parent\'s name, not the child\'s)'), ['style' => 'display:none;', 'class' => 'parent']) ?></legend>
<?php
echo $this->Form->input('person.first_name', [
	'help' => __('First (and, if desired, middle) name.'),
]);
echo $this->Form->input('person.last_name');

$phone_numbers_enabled = array_diff([
	Configure::read('profile.home_phone'),
	Configure::read('profile.work_phone'),
	Configure::read('profile.mobile_phone')
], [0]);
if (count($phone_numbers_enabled) > 1) {
	echo $this->Html->para(null, __('Enter at least one telephone number below.'));
}

if (Configure::read('profile.home_phone')) {
	echo $this->Form->input('person.home_phone', [
		'help' => __('Enter your home telephone number.'),
	]);
	echo $this->Form->input('person.publish_home_phone', [
		'label' => __('Allow other people to view home number'),
	]);
}
if (Configure::read('profile.work_phone')) {
	echo $this->Form->input('person.work_phone', [
		'help' => __('Enter your work telephone number (optional).'),
	]);
	echo $this->Form->input('person.work_ext', [
		'label' => __('Work Extension'),
		'help' => __('Enter your work extension (optional).'),
	]);
	echo $this->Form->input('person.publish_work_phone', [
		'label' => __('Allow other people to view work number'),
	]);
}
if (Configure::read('profile.mobile_phone')) {
	echo $this->Form->input('person.mobile_phone', [
		'help' => __('Enter your cell or pager number (optional).'),
	]);
	echo $this->Form->input('person.publish_mobile_phone', [
		'label' => __('Allow other people to view mobile number'),
	]);
}

if (array_key_exists(GROUP_PARENT, $groups)):
?>
		<fieldset class="parent" style="display:none;">
			<legend><?= __('Alternate Contact (optional)') ?></legend>
			<p><?= __('This alternate parent/guardian contact information is for display purposes only. If the alternate contact should have their own login, do not enter their information here; instead create a separate account and then link them together.') ?></p>
			<p><?= __('This is not for your child\'s name; enter that in the "Child Profile" section below.') ?></p>
<?php
	echo $this->Form->input('person.alternate_first_name', [
		'label' => __('First Name'),
		'help' => __('First (and, if desired, middle) name.'),
		'secure' => false,
	]);
	echo $this->Form->input('person.alternate_last_name', [
		'label' => __('Last Name'),
		'secure' => false,
	]);
	if (Configure::read('profile.work_phone')) {
		echo $this->Form->input('person.alternate_work_phone', [
			'label' => __('Work Phone'),
			'help' => __('Enter your work telephone number (optional).'),
			'secure' => false,
		]);
		echo $this->Form->input('person.alternate_work_ext', [
			'label' => __('Work Extension'),
			'help' => __('Enter your work extension (optional).'),
			'secure' => false,
		]);
		echo $this->Form->input('person.publish_alternate_work_phone', [
			'label' => __('Allow other people to view work number'),
			'secure' => false,
		]);
	}
	if (Configure::read('profile.mobile_phone')) {
		echo $this->Form->input('person.alternate_mobile_phone', [
			'label' => __('Mobile Phone'),
			'help' => __('Enter your cell or pager number (optional).'),
			'secure' => false,
		]);
		echo $this->Form->input('person.publish_alternate_mobile_phone', [
			'label' => __('Allow other people to view mobile number'),
			'secure' => false,
		]);
	}
?>
		</fieldset>
<?php
endif;
?>
	</fieldset>
	<fieldset>
		<legend><?= __('Username and Password') ?></legend>
<?php
echo $this->Form->input($user_field, [
	'label' => __('Username'),
]);
echo $this->Form->input('new_password', ['type' => 'password', 'label' => __('Password')]);
echo $this->Form->input('confirm_password', ['type' => 'password', 'label' => __('Confirm Password')]);
?>
	</fieldset>
<?php
if (Configure::read('feature.affiliates')):
?>
	<fieldset>
		<legend><?= __('Affiliate') ?></legend>
<?php
	if (Configure::read('feature.multiple_affiliates')) {
		echo $this->Form->input('person.affiliates._ids', [
			'help' => __('Select all affiliates you are interested in.'),
			'multiple' => 'checkbox',
		]);
	} else {
		echo $this->Form->input('person.affiliates.0.id', [
			'label' => __('Affiliate'),
			'options' => $affiliates,
			'type' => 'select',
			'empty' => '---',
		]);
	}
?>
	</fieldset>
<?php
endif;
?>
	<fieldset>
		<legend><?= __('Online Contact') ?></legend>
<?php
echo $this->Form->input($email_field, [
	'label' => __('Email'),
]);
echo $this->Form->input('person.publish_email', [
	'label' => __('Allow other people to view my email address'),
]);
echo $this->Form->input('person.alternate_email', [
	'help' => __('Optional second email address.'),
]);
echo $this->Form->input('person.publish_alternate_email', [
	'label' => __('Allow other people to view my alternate email address'),
]);
if (Configure::read('feature.gravatar')) {
	if (Configure::read('feature.photos')) {
		$after = __('You can have an image shown on your account by uploading a photo directly, or by enabling this setting and then create a {0} account using the email address you\'ve associated with your {1} account.',
			$this->Html->link('gravatar.com', 'https://www.gravatar.com/'), Configure::read('organization.short_name'));
	} else {
		$after = __('You can have an image shown on your account if you enable this setting and then create a {0} account using the email address you\'ve associated with your {1} account.',
			$this->Html->link('gravatar.com', 'https://www.gravatar.com/'), Configure::read('organization.short_name'));
	}
	echo $this->Form->input('person.show_gravatar', [
		'label' => __('Show Gravatar image for your account?'),
		'help' => $after,
	]);
}
if (Configure::read('profile.contact_for_feedback')) {
	echo $this->Form->input('person.contact_for_feedback', [
		'label' => __('From time to time, {0} would like to contact members with information on our programs and to solicit feedback. Can {0} contact you in this regard?', $short),
		'checked' => true,
	]);
}
?>
	</fieldset>
<?php
if (Configure::read('profile.addr_street') || Configure::read('profile.addr_city') ||
	Configure::read('profile.addr_prov') || Configure::read('profile.addr_country') ||
	Configure::read('profile.addr_postalcode')):
?>
	<fieldset>
		<legend><?= __('Street Address') ?></legend>
<?php
	if (Configure::read('profile.addr_street')) {
		echo $this->Form->input('person.addr_street', [
			'label' => __('Street and Number'),
			'help' => __('Number, street name, and apartment number if necessary.'),
		]);
	}
	if (Configure::read('profile.addr_city')) {
		echo $this->Form->input('person.addr_city', [
			'label' => __('City'),
			'help' => __('Name of city.'),
		]);
	}
	if (Configure::read('profile.addr_prov')) {
		echo $this->Form->input('person.addr_prov', [
			'label' => __('Province'),
			'type' => 'select',
			'empty' => '---',
			'options' => $provinces,
			'help' => __('Select a province/state from the list'),
		]);
	}
	if (Configure::read('profile.addr_country')) {
		echo $this->Form->input('person.addr_country', [
			'label' => __('Country'),
			'type' => 'select',
			'empty' => '---',
			'options' => $countries,
			'hide_single' => true,
			'help' => __('Select a country from the list.'),
		]);
	}
	if (Configure::read('profile.addr_postalcode')) {
		echo $this->Form->input('person.addr_postalcode', [
			'label' => __('Postal Code'),
			'help' => __('Please enter a correct postal code matching the address above. {0} uses this information to help locate new {1} near its members.', $short, Configure::read('UI.fields')),
		]);
	}
?>
	</fieldset>
<?php
endif;
?>
	<fieldset class="player" style="display:none;">
		<legend><?= __('Your Player Profile') ?></legend>
<?php
echo $this->element('People/gender_inputs', ['prefix' => 'person.', 'secure' => false, 'edit' => false]);

if (Configure::read('profile.birthdate')) {
	if (Configure::read('feature.birth_year_only')) {
		echo $this->Form->input('person.birthdate', [
			'templates' => [
				'dateWidget' => '{{year}}',
				// Change the input container template, removing the "date" class, so it doesn't get a date picker added
				'inputContainer' => '<div class="form-group {{required}}">{{content}}</div>',
				'inputContainerError' => '<div class="form-group {{required}} has-error">{{content}}</div>',
			],
			'minYear' => Configure::read('options.year.born.min'),
			'maxYear' => Configure::read('options.year.born.max'),
			'empty' => '---',
			'help' => __('Please enter a correct birthdate; having accurate information is important for insurance purposes.'),
			'secure' => false,
		]);
		echo $this->Form->hidden('person.birthdate.month', [
			'value' => 1,
			'secure' => false,
		]);
		echo $this->Form->hidden('person.birthdate.day', [
			'value' => 1,
			'secure' => false,
		]);
	} else {
		echo $this->Form->input('person.birthdate', [
			'minYear' => Configure::read('options.year.born.min'),
			'maxYear' => Configure::read('options.year.born.max'),
			'empty' => '---',
			'help' => __('Please enter a correct birthdate; having accurate information is important for insurance purposes.'),
			'secure' => false,
		]);
	}
}
if (Configure::read('profile.height')) {
	if (Configure::read('feature.units') == 'Metric') {
		$units = __('centimeters');
	} else {
		$units = __('inches (5 feet is 60 inches; 6 feet is 72 inches)');
	}
	echo $this->Form->input('person.height', [
		'size' => 6,
		'help' => __('Please enter your height in {0}. This is used to help generate even teams for hat leagues.', $units),
		'secure' => false,
	]);
}

if (in_array(Configure::read('profile.shirt_size'), [PROFILE_USER_UPDATE, PROFILE_ADMIN_UPDATE])) {
	echo $this->Form->input('person.shirt_size', [
		'type' => 'select',
		'empty' => '---',
		'options' => Configure::read('options.shirt_size'),
		'secure' => false,
	]);
}
if (Configure::read('feature.dog_questions')) {
	echo $this->Form->input('person.has_dog', [
		'secure' => false,
	]);
}
echo $this->element('People/skill_edit', ['prefix' => 'person']);
?>
	</fieldset>
<?php
if (array_key_exists(GROUP_COACH, $groups) && Configure::read('profile.shirt_size')):
?>
	<fieldset class="coach" style="display:none;">
		<legend><?= __('Your Coaching Profile') ?></legend>
<?php
	if (Configure::read('profile.shirt_size')) {
		echo $this->Form->input('person.shirt_size', [
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.shirt_size'),
			'secure' => false,
		]);
	}
?>
	</fieldset>
<?php
endif;

if (array_key_exists(GROUP_PARENT, $groups)):
?>
	<fieldset class="parent" style="display:none;">
		<legend><?= __('Child Profile') ?></legend>
<?php
	echo $this->Form->input('person.relatives.0.first_name', [
		'help' => __('First (and, if desired, middle) name.'),
		'secure' => false,
	]);
	echo $this->Form->input('person.relatives.0.last_name', [
		'secure' => false,
	]);

	echo $this->element('People/gender_inputs', ['prefix' => 'person.relatives.0.', 'secure' => false, 'edit' => false]);

	if (Configure::read('profile.birthdate')) {
		if (Configure::read('feature.birth_year_only')) {
			echo $this->Form->input('person.relatives.0.birthdate', [
				'templates' => [
					'dateWidget' => '{{year}}',
					// Change the input container template, removing the "date" class, so it doesn't get a date picker added
					'inputContainer' => '<div class="form-group {{required}}">{{content}}</div>',
					'inputContainerError' => '<div class="form-group {{required}} has-error">{{content}}</div>',
				],
				'minYear' => Configure::read('options.year.born.min'),
				'maxYear' => Configure::read('options.year.born.max'),
				'empty' => '---',
				'help' => __('Please enter a correct birthdate; having accurate information is important for insurance purposes.'),
				'secure' => false,
			]);
			echo $this->Form->hidden('person.relatives.0.birthdate.month', [
				'value' => 1,
				'secure' => false,
			]);
			echo $this->Form->hidden('person.relatives.0.birthdate.day', [
				'value' => 1,
				'secure' => false,
			]);
		} else {
			echo $this->Form->input('person.relatives.0.birthdate', [
				'minYear' => Configure::read('options.year.born.min'),
				'maxYear' => Configure::read('options.year.born.max'),
				'empty' => '---',
				'help' => __('Please enter a correct birthdate; having accurate information is important for insurance purposes.'),
				'secure' => false,
			]);
		}
	}
	if (Configure::read('profile.height')) {
		if (Configure::read('feature.units') == 'Metric') {
			$units = __('centimeters');
		} else {
			$units = __('inches (5 feet is 60 inches; 6 feet is 72 inches)');
		}
		echo $this->Form->input('person.relatives.0.height', [
			'size' => 6,
			'help' => __('Please enter your height in {0}. This is used to help generate even teams for hat leagues.', $units),
			'secure' => false,
		]);
	}
	if (in_array(Configure::read('profile.shirt_size'), [PROFILE_USER_UPDATE, PROFILE_ADMIN_UPDATE])) {
		echo $this->Form->input('person.relatives.0.shirt_size', [
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.shirt_size'),
			'secure' => false,
		]);
	}
	echo $this->element('People/skill_edit', ['prefix' => 'person.relatives.0']);
?>
	</fieldset>
<?php
endif;
?>
<?= $this->Form->button(__('Submit and save your information'), ['class' => 'btn-success', 'name' => 'action', 'value' => 'create']) ?>
<?php
// Don't show this link under Drupal, Joomla, etc., but instead give directions on how to add more after manual login?
if (!$this->Authorize->getIdentity() && Configure::read('feature.authenticate_through') == 'Zuluru') {
	echo $this->Form->button(__('Save your information and add another child'), ['class' => 'parent', 'style' => 'display:none;', 'name' => 'action', 'value' => 'continue']);
}
?>
<?= $this->Form->end() ?>

<?php
if (Configure::read('profile.skill_level')) {
	$sports = Configure::read('options.sport');
	foreach (array_keys($sports) as $sport) {
		if (Configure::read("sports.{$sport}.rating_questions")) {
			echo $this->element('People/rating', ['sport' => $sport]);
		}
	}
}
