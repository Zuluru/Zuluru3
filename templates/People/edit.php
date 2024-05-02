<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Group[] $groups
 * @var string[] $provinces
 * @var string[] $countries
 * @var string[] $affiliates
 * @var string $user_model
 * @var string $id_field
 * @var string $user_field
 * @var string $email_field
 * @var bool $manage_users
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add($person->full_name);
$this->Breadcrumbs->add(__('Edit'));

$short = Configure::read('organization.short_name');
$admin = Configure::read('email.admin_email');
$identity = $this->Authorize->getIdentity();
$is_manager = $identity->isManagerOf($person);

$access = [PROFILE_USER_UPDATE, PROFILE_REGISTRATION];
// People with incomplete profiles can update any of the fields that
// normally only admins can edit, so that they can successfully fill
// out all of the profile.
if ($is_manager || !$person->complete) {
	$access[] = PROFILE_ADMIN_UPDATE;
}
?>

<div class="people form">
<h2><?php
if (!empty($person->uploads) && $person->uploads[0]->approved == true) {
	echo $this->element('People/player_photo', ['person' => $person, 'photo' => $person->uploads[0]]);
}
echo $identity->isMe($person) ? __('Edit Your Profile') : $person->full_name ?></h2>
<?php
if ($person->user_id):
?>

<p><?= __('Note that email and phone publish settings below apply to most registered users on the site. Coaches and captains will always have access to view the phone numbers and email addresses of their confirmed players. All team coaches and captains will also have their email address viewable by other players. People using the site without being logged in will never see any contact information.') ?></p>
<?php
	if (Configure::read('App.urls.privacyPolicy')):
?>

<p><?= __('If you have concerns about the data {0} collects, please see our {1}.',
		$short,
		$this->Html->tag('strong', $this->Html->link(__('Privacy Policy'), Configure::read('App.urls.privacyPolicy'), ['target' => '_new']))
) ?></p>
<?php
	endif;

	if (Configure::read('feature.photos') && empty($person->uploads) && $identity->isMe($person)):
?>

	<fieldset>
		<legend><?= __('Photo') ?></legend>
		<?= $this->Html->iconLink('blank_profile.jpg', ['controller' => 'People', 'action' => 'photo_upload'], ['class' => 'thumbnail', 'style' => 'float: left; margin-bottom: 7px;']) ?>
		<p style="float: left;"><?= __('{0} a profile photo', $this->Html->link(__('Click to upload'), ['controller' => 'People', 'action' => 'photo_upload'])) ?></p>
	</fieldset>
<?php
	endif;
endif;
?>

<?php
echo $this->Form->create($person, ['align' => 'horizontal']);
echo $this->Form->hidden('complete', ['value' => true]);

if (($person->user_id && count($groups) > 1) || $is_manager):
?>

	<fieldset>
		<legend><?= __('Account Type') ?></legend>
<?php
	if ($person->user_id) {
		echo $this->Jquery->toggleInput('groups._ids', [
			'label' => __('Select all roles that apply to you.'),
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
	}
	if ($is_manager) {
		$options = Configure::read('options.record_status');
		if (Configure::read('feature.auto_approve')) {
			unset($options['new']);
		}
		echo $this->Form->control('status', [
			'type' => 'select',
			'empty' => '---',
			'options' => $options,
		]);
	}
?>

	</fieldset>
<?php
elseif ($person->user_id):
	echo $this->Jquery->toggleInput('groups._ids', [
		'label' => __('Select all roles that apply to you.'),
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
endif;
$this->Form->unlockField('groups._ids');
?>

	<fieldset>
		<legend><?= __('Your Information') ?></legend>
<?php
if (in_array(Configure::read('profile.first_name'), $access)) {
	echo $this->Form->control('first_name', [
		'label' => Configure::read('profile.legal_name') ? __('Preferred Name') : __('First Name'),
		'help' => __('First (and, if desired, middle) name.'),
	]);
} else {
	echo $this->Form->control('first_name', [
		'disabled' => true,
		'class' => 'disabled',
		'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new name'), $this->Html->link($admin, "mailto:$admin")),
	]);
}
if (in_array(Configure::read('profile.legal_name'), $access)) {
	echo $this->Form->control('legal_name', [
		'help' => __('For insurance and legal purposes. This will be visible only to administrators. Required only if substantially different from your Preferred Name.'),
	]);
} else if (Configure::read('profile.legal_name')) {
	echo $this->Form->control('legal_name', [
		'disabled' => true,
		'class' => 'disabled',
		'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new name'), $this->Html->link($admin, "mailto:$admin")),
	]);
}
if (in_array(Configure::read('profile.last_name'), $access)) {
	echo $this->Form->control('last_name');
} else {
	echo $this->Form->control('last_name', [
		'disabled' => true,
		'class' => 'disabled',
		'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new name'), $this->Html->link($admin, "mailto:$admin")),
	]);
}

if ($person->user_id) {
	$phone_numbers_enabled = array_diff([
		Configure::read('profile.home_phone'),
		Configure::read('profile.work_phone'),
		Configure::read('profile.mobile_phone')
	], [0]);
	if (count($phone_numbers_enabled) > 1) {
		echo $this->Html->para(null, __('Enter at least one telephone number below.'));
	}

	if (in_array(Configure::read('profile.home_phone'), $access)) {
		echo $this->Form->control('home_phone', [
			'help' => __('Enter your home telephone number.'),
		]);
	} else if (Configure::read('profile.home_phone')) {
		echo $this->Form->control('home_phone', [
			'disabled' => true,
			'class' => 'disabled',
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new phone number'), $this->Html->link($admin, "mailto:$admin")),
		]);
	}
	if (Configure::read('profile.home_phone')) {
		echo $this->Form->control('publish_home_phone', [
			'label' => __('Allow registered users to view home number'),
		]);
	}
	if (in_array(Configure::read('profile.work_phone'), $access)) {
		echo $this->Form->control('work_phone', [
			'help' => __('Enter your work telephone number (optional).'),
		]);
		echo $this->Form->control('work_ext', [
			'label' => __('Work Extension'),
			'help' => __('Enter your work extension (optional).'),
		]);
	} else if (Configure::read('profile.work_phone')) {
		echo $this->Form->control('work_phone', [
			'disabled' => true,
			'class' => 'disabled',
		]);
		echo $this->Form->control('work_ext', [
			'disabled' => true,
			'class' => 'disabled',
			'label' => __('Work Extension'),
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new phone number'), $this->Html->link($admin, "mailto:$admin")),
		]);
	}
	if (Configure::read('profile.work_phone')) {
		echo $this->Form->control('publish_work_phone', [
			'label' => __('Allow registered users to view work number'),
		]);
	}
	if (in_array(Configure::read('profile.mobile_phone'), $access)) {
		echo $this->Form->control('mobile_phone', [
			'help' => __('Enter your cell or pager number (optional).'),
		]);
	} else if (Configure::read('profile.mobile_phone')) {
		echo $this->Form->control('mobile_phone', [
			'disabled' => true,
			'class' => 'disabled',
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new phone number'), $this->Html->link($admin, "mailto:$admin")),
		]);
	}
	if (Configure::read('profile.mobile_phone')) {
		echo $this->Form->control('publish_mobile_phone', [
			'label' => __('Allow registered users to view mobile number'),
		]);
	}
}
?>

	</fieldset>
<?php
if ($person->user_id):
?>

	<fieldset class="parent" style="display:none;">
		<legend><?= __('Alternate Contact (optional)') ?></legend>
		<p><?= __('This alternate contact information is for display purposes only. If the alternate contact should have their own login details, do not enter their information here; instead create a separate account and then link them together.') ?></p>
<?php
	echo $this->Form->control('alternate_first_name', [
		'label' => __('First Name'),
		'help' => __('First (and, if desired, middle) name.'),
		'secure' => false,
	]);
	echo $this->Form->control('alternate_last_name', [
		'label' => __('Last Name'),
		'secure' => false,
	]);
	if (Configure::read('profile.work_phone')) {
		echo $this->Form->control('alternate_work_phone', [
			'label' => __('Work Phone'),
			'help' => __('Enter your work telephone number (optional).'),
			'secure' => false,
		]);
		echo $this->Form->control('alternate_work_ext', [
			'label' => __('Work Extension'),
			'help' => __('Enter your work extension (optional).'),
			'secure' => false,
		]);
		echo $this->Form->control('publish_alternate_work_phone', [
			'label' => __('Allow registered users to view work number'),
			'secure' => false,
		]);
	}
	if (Configure::read('profile.mobile_phone')) {
		echo $this->Form->control('alternate_mobile_phone', [
			'label' => __('Mobile Phone'),
			'help' => __('Enter your cell or pager number (optional).'),
			'secure' => false,
		]);
		echo $this->Form->control('publish_alternate_mobile_phone', [
			'label' => __('Allow registered users to view mobile number'),
			'secure' => false,
		]);
	}
?>

	</fieldset>
<?php
endif;

if ($person->user_id && $manage_users):
?>

	<fieldset>
		<legend><?= __('Username') ?></legend>
<?php
	$user_model = Inflector::singularize(Inflector::underscore($user_model));
	echo $this->Form->hidden("$user_model.$id_field");
	echo $this->Form->control("$user_model.$user_field", [
		'label' => __('Username'),
	]);
?>

	</fieldset>
<?php
endif;

// We hide the affiliate selection if it's not enabled, for admins,
// and for managers when only one affiliate is allowed. The latter
// is to prevent managers from switching themselves to another
// affiliate where they're not a manager.
if (Configure::read('feature.affiliates') && !$identity->isAdmin() &&
	(Configure::read('feature.multiple_affiliates') || !$identity->isManager())):
?>

	<fieldset>
		<legend><?= __('Affiliate') ?></legend>
<?php
	if (Configure::read('feature.multiple_affiliates')) {
		$help = __('Select all affiliates you are interested in.');
		if ($identity->isManager()) {
			$help .= ' ' . __('Note that affiliates you are already a manager of ({0}) are not included here; this will remain unchanged.',
				implode(', ', collection($this->UserCache->read('ManagedAffiliates'))->extract(function ($entity) { return $entity->translateField('name'); })->toArray()));
		}
		echo $this->Form->control('affiliates._ids', [
			'help' => $help,
			'multiple' => 'checkbox',
			'hide_single' => !$identity->isManager(),
		]);
		$this->Form->unlockField('affiliates._ids');
	} else {
		echo $this->Form->control('affiliates.0.id', [
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

if ($person->user_id):
?>

	<fieldset>
		<legend><?= __('Online Contact') ?></legend>
<?php
	echo $this->Form->control("$user_model.$email_field", [
		'label' => __('Email'),
		'disabled' => !$manage_users,
		'help' => $manage_users ? null : __('This field is managed through your {0}.',
			$this->Html->link(__('primary account'), Configure::read('App.urls.manage'))
		),
	]);
	echo $this->Form->control('publish_email', [
		'label' => __('Allow registered users to view my email address'),
	]);
	echo $this->Form->control('alternate_email', [
		'help' => __('Optional second email address.'),
	]);
	echo $this->Form->control('publish_alternate_email', [
		'label' => __('Allow registered users to view my alternate email address'),
	]);
	if (Configure::read('feature.gravatar')) {
		if (Configure::read('feature.photos')) {
			$after = __('You can have an image shown on your account by uploading a photo directly, or by enabling this setting and then creating a {0} account using the email address you\'ve associated with your {1} account.',
				$this->Html->link('gravatar.com', 'https://www.gravatar.com/'), Configure::read('organization.short_name'));
		} else {
			$after = __('You can have an image shown on your account if you enable this setting and then create a {0} account using the email address you\'ve associated with your {1} account.',
				$this->Html->link('gravatar.com', 'https://www.gravatar.com/'), Configure::read('organization.short_name'));
		}
		echo $this->Form->control('show_gravatar', [
			'label' => __('Show Gravatar image for your account?'),
			'help' => $after,
		]);
	}
	if (in_array(Configure::read('profile.contact_for_feedback'), $access)) {
		echo $this->Form->control('contact_for_feedback', [
			'label' => __('From time to time, {0} would like to contact members with information on our programs and to solicit feedback. Can {0} contact you in this regard?', $short),
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
		if (in_array(Configure::read('profile.addr_street'), $access)) {
			echo $this->Form->control('addr_street', [
				'label' => __('Street and Number'),
				'help' => __('Number, street name, and apartment number if necessary.'),
			]);
		} else if (Configure::read('profile.addr_street')) {
			echo $this->Form->control('addr_street', [
				'disabled' => true,
				'class' => 'disabled',
				'label' => __('Street and Number'),
				'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new address'), $this->Html->link($admin, "mailto:$admin")),
			]);
		}
		if (in_array(Configure::read('profile.addr_city'), $access)) {
			echo $this->Form->control('addr_city', [
				'label' => __('City'),
				'help' => __('Name of city.'),
			]);
		} else if (Configure::read('profile.addr_city')) {
			echo $this->Form->control('addr_city', [
				'disabled' => true,
				'class' => 'disabled',
				'label' => __('City'),
				'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new address'), $this->Html->link($admin, "mailto:$admin")),
			]);
		}
		if (in_array(Configure::read('profile.addr_prov'), $access)) {
			echo $this->Form->control('addr_prov', [
				'label' => __('Province'),
				'type' => 'select',
				'empty' => '---',
				'options' => $provinces,
				'help' => __('Select a province/state from the list'),
			]);
		} else if (Configure::read('profile.addr_prov')) {
			echo $this->Form->control('addr_prov', [
				'disabled' => true,
				'class' => 'disabled',
				'label' => __('Province'),
				'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new address'), $this->Html->link($admin, "mailto:$admin")),
			]);
		}
		if (in_array(Configure::read('profile.addr_country'), $access)) {
			echo $this->Form->control('addr_country', [
				'label' => __('Country'),
				'type' => 'select',
				'empty' => '---',
				'options' => $countries,
				'hide_single' => true,
				'help' => __('Select a country from the list.'),
			]);
		} else if (Configure::read('profile.addr_country') && count($countries) > 1) {
			echo $this->Form->control('addr_country', [
				'disabled' => true,
				'class' => 'disabled',
				'label' => __('Country'),
				'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new address'), $this->Html->link($admin, "mailto:$admin")),
			]);
		}
		if (in_array(Configure::read('profile.addr_postalcode'), $access)) {
			$fields = Configure::read('UI.fields');
			echo $this->Form->control('addr_postalcode', [
				'label' => __('Postal Code'),
				'help' => __('Please enter a correct postal code matching the address above. {0} uses this information to help locate new {1} near its members.', $short, Configure::read('UI.fields')),
			]);
		} else if (Configure::read('profile.addr_postalcode')) {
			echo $this->Form->control('addr_postalcode', [
				'disabled' => true,
				'class' => 'disabled',
				'label' => __('Postal Code'),
				'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new address'), $this->Html->link($admin, "mailto:$admin")),
			]);
		}
?>

	</fieldset>
<?php
	endif;
endif;

if (Configure::read('profile.gender') || Configure::read('profile.pronouns') || Configure::read('profile.birthdate') ||
	Configure::read('profile.year_started') || Configure::read('profile.skill_level') ||
	Configure::read('profile.height') || Configure::read('profile.shirt_size') ||
	Configure::read('feature.dog_questions')):
?>

	<fieldset class="player">
		<legend><?= __('Your Player Profile') ?></legend>
<?php
	echo $this->element('People/gender_inputs', ['prefix' => '', 'secure' => false, 'edit' => $access, 'person' => $person]);

	if (in_array(Configure::read('profile.birthdate'), $access)) {
		if (Configure::read('feature.birth_year_only')) {
			echo $this->Form->control('birthdate', [
				'templates' => [
					'dateWidget' => '{{year}}',
					// Change the input container template, removing the "date" class, so it doesn't get a date picker added
					'inputContainer' => '<div class="mb-3 form-group row {{required}}">{{content}}</div>',
					'inputContainerError' => '<div class="mb-3 form-group row {{required}} has-error">{{content}}</div>',
				],
				'minYear' => Configure::read('options.year.born.min'),
				'maxYear' => Configure::read('options.year.born.max'),
				'empty' => '---',
				'default' => '',
				'help' => __('Please enter a correct birthdate; having accurate information is important for insurance purposes.'),
				'secure' => false,
			]);
			echo $this->Form->hidden('birthdate.month', [
				'value' => 1,
			]);
			echo $this->Form->hidden('birthdate.day', [
				'value' => 1,
			]);
			$this->Form->unlockField('birthdate.month');
			$this->Form->unlockField('birthdate.day');
		} else {
			echo $this->Form->control('birthdate', [
				'minYear' => Configure::read('options.year.born.min'),
				'maxYear' => Configure::read('options.year.born.max'),
				'empty' => '---',
				'default' => '',
				'help' => __('Please enter a correct birthdate; having accurate information is important for insurance purposes.'),
				'secure' => false,
			]);
		}
	} else if (Configure::read('profile.birthdate')) {
		echo $this->Form->control('birthdate', [
			'disabled' => true,
			'year' => [
				'class' => 'disabled',
			],
			'month' => [
				'class' => 'disabled',
			],
			'day' => [
				'class' => 'disabled',
			],
			'minYear' => Configure::read('options.year.born.min'),
			'maxYear' => Configure::read('options.year.born.max'),
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('correct birthdate'), $this->Html->link($admin, "mailto:$admin")),
			'secure' => false,
		]);
	}
	if (in_array(Configure::read('profile.height'), $access)) {
		if (Configure::read('feature.units') == 'Metric') {
			$units = __('centimeters');
		} else {
			$units = __('inches (5 feet is 60 inches; 6 feet is 72 inches)');
		}
		echo $this->Form->control('height', [
			'size' => 6,
			'help' => __('Please enter your height in {0}. This is used to help build even teams from individual signups.', $units),
			'secure' => false,
		]);
	} else if (Configure::read('profile.height')) {
		echo $this->Form->control('height', [
			'disabled' => true,
			'class' => 'disabled',
			'size' => 6,
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new height'), $this->Html->link($admin, "mailto:$admin")),
			'secure' => false,
		]);
	}
	if (in_array(Configure::read('profile.shirt_size'), $access)) {
		echo $this->Form->control('shirt_size', [
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.shirt_size'),
			'help' => __('This information may be used by the league or your team captain to order shirts/jerseys.'),
			'secure' => false,
		]);
	} else if (Configure::read('profile.shirt_size')) {
		echo $this->Form->control('shirt_size', [
			'disabled' => true,
			'class' => 'disabled',
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new shirt size'), $this->Html->link($admin, "mailto:$admin")),
			'secure' => false,
		]);
	}
	if (Configure::read('feature.dog_questions')) {
		echo $this->Form->control('has_dog', [
			'secure' => false,
		]);
	}
	echo $this->element('People/skill_edit', compact('access'));
?>

	</fieldset>
<?php
endif;

if (array_key_exists(GROUP_COACH, $groups) && $person->user_id && Configure::read('profile.shirt_size')):
?>

	<fieldset class="coach">
		<legend><?= __('Your Coaching Profile') ?></legend>
<?php
	if (in_array(Configure::read('profile.shirt_size'), $access)) {
		echo $this->Form->control('shirt_size', [
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.shirt_size'),
			'help' => __('This information may be used by the league or your team captain to order shirts/jerseys.'),
			'secure' => false,
		]);
	} else if (Configure::read('profile.shirt_size')) {
		echo $this->Form->control('shirt_size', [
			'disabled' => true,
			'class' => 'disabled',
			'help' => __('To prevent system abuses, this can only be changed by an administrator. To change this, please email your {0} to {1}.', __('new shirt size'), $this->Html->link($admin, "mailto:$admin")),
			'secure' => false,
		]);
	}
?>

	</fieldset>
<?php
endif;
?>
<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
<?= $this->Form->end() ?>
</div>

<?php
if (Configure::read('profile.skill_level')) {
	$sports = Configure::read('options.sport');
	foreach (array_keys($sports) as $sport) {
		if (Configure::read("sports.{$sport}.rating_questions")) {
			echo $this->element('People/rating', ['sport' => $sport]);
		}
	}
}
