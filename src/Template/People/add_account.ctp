<?php
/**
 * @type $this \App\View\AppView
 * @type $person \App\Model\Entity\Person
 * @type string $user_field
 * @type string $email_field
 * @type string[] $provinces
 * @type string[] $countries
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;

$this->Html->addCrumb(__('Users'));
$this->Html->addCrumb(__('Create Login'));

$short = Configure::read('organization.short_name');
?>

<h3><?= __('Create Login') ?></h3>

<?php
// Create the form and maybe add some spam-prevention tools
echo $this->Form->create($person, ['align' => 'horizontal']);
?>

	<fieldset>
		<legend><?= __('Username and Password') ?></legend>
<?php
echo $this->Form->input("user.{$user_field}", [
	'label' => __('Username'),
]);
echo $this->Form->input('user.new_password', ['type' => 'password', 'label' => __('Password')]);
echo $this->Form->input('user.confirm_password', ['type' => 'password', 'label' => __('Confirm Password')]);
?>
	</fieldset>
	<fieldset>
		<legend><?= __('Contact Information') ?></legend>
		<p class="warning-message"><?= __('Your youth profile has not had your personal contact information attached to it. To become a full-fledged account, we need a few more details from you now.') ?></p>
<?php
$phone_numbers_enabled = array_diff([
	Configure::read('profile.home_phone'),
	Configure::read('profile.work_phone'),
	Configure::read('profile.mobile_phone')
], [0]);
if (count($phone_numbers_enabled) > 1) {
	echo $this->Html->para(null, __('Enter at least one telephone number below.'));
}

if (Configure::read('profile.home_phone')) {
	echo $this->Form->input('home_phone', [
		'help' => __('Enter your home telephone number.'),
	]);
	echo $this->Form->input('publish_home_phone', [
		'label' => __('Allow registered users to view home number'),
	]);
}
if (Configure::read('profile.work_phone')) {
	echo $this->Form->input('work_phone', [
		'help' => __('Enter your work telephone number (optional).'),
	]);
	echo $this->Form->input('work_ext', [
		'label' => __('Work Extension'),
		'help' => __('Enter your work extension (optional).'),
	]);
	echo $this->Form->input('publish_work_phone', [
		'label' => __('Allow registered users to view work number'),
	]);
}
if (Configure::read('profile.mobile_phone')) {
	echo $this->Form->input('mobile_phone', [
		'help' => __('Enter your cell or pager number (optional).'),
	]);
	echo $this->Form->input('publish_mobile_phone', [
		'label' => __('Allow registered users to view mobile number'),
	]);
}
echo $this->Form->input("user.{$email_field}", [
	'label' => __('Email'),
]);
echo $this->Form->input('publish_email', [
	'label' => __('Allow registered users to view my email address'),
]);
echo $this->Form->input('alternate_email', [
	'help' => __('Optional second email address.'),
]);
echo $this->Form->input('publish_alternate_email', [
	'label' => __('Allow registered users to view my alternate email address'),
]);
if (Configure::read('feature.gravatar')) {
	if (Configure::read('feature.photos')) {
		$after = __('You can have an image shown on your account by uploading a photo directly, or by enabling this setting and then create a {0} account using the email address you\'ve associated with your {1} account.',
			$this->Html->link('gravatar.com', 'https://www.gravatar.com/'), Configure::read('organization.short_name'));
	} else {
		$after = __('You can have an image shown on your account if you enable this setting and then create a {0} account using the email address you\'ve associated with your {1} account.',
			$this->Html->link('gravatar.com', 'https://www.gravatar.com/'), Configure::read('organization.short_name'));
	}
	echo $this->Form->input('show_gravatar', [
		'label' => __('Show Gravatar image for your account?'),
		'help' => $after,
	]);
}
if (Configure::read('profile.contact_for_feedback')) {
	echo $this->Form->input('contact_for_feedback', [
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
		echo $this->Form->input('addr_street', [
			'label' => __('Street and Number'),
			'help' => __('Number, street name, and apartment number if necessary.'),
		]);
	}
	if (Configure::read('profile.addr_city')) {
		echo $this->Form->input('addr_city', [
			'label' => __('City'),
			'help' => __('Name of city.'),
		]);
	}
	if (Configure::read('profile.addr_prov')) {
		echo $this->Form->input('addr_prov', [
			'label' => __('Province'),
			'type' => 'select',
			'empty' => '---',
			'options' => $provinces,
			'help' => __('Select a province/state from the list'),
		]);
	}
	if (Configure::read('profile.addr_country')) {
		echo $this->Form->input('addr_country', [
			'label' => __('Country'),
			'type' => 'select',
			'empty' => '---',
			'options' => $countries,
			'hide_single' => true,
			'help' => __('Select a country from the list.'),
		]);
	}
	if (Configure::read('profile.addr_postalcode')) {
		echo $this->Form->input('addr_postalcode', [
			'label' => __('Postal Code'),
			'help' => __('Please enter a correct postal code matching the address above. {0} uses this information to help locate new {1} near its members.', $short, Configure::read('UI.fields')),
		]);
	}
?>
	</fieldset>
<?php
endif;
?>
<?= $this->Form->button(__('Submit and save your information'), ['class' => 'btn-success']) ?>
<?= $this->Form->end();
