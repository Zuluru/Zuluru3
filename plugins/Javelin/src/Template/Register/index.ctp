<?php
/**
 * @var \App\View\AppView $this
 * @var \Javelin\Form\RegisterForm $register
 */

$this->Breadcrumbs->add('Javelin');
$this->Breadcrumbs->add(__('Register'));
?>

<div class="javelin register">
	<h2><?= __('Register with {0}', 'Javelin') ?></h2>
	<p><?= __('To register your site with {0}, you must indicate your organization\'s primary contact. {0} will contact this person to assist with setting up your administrative account on their system.', 'Javelin') ?></p>
	<p class="warning-message"><?= __('Be sure that you have your {0} and {1} completed before submitting this form, as data from there is included in this initialization step.',
		$this->Html->link(__('organization'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'organization']),
		$this->Html->link(__('email settings'), ['plugin' => false, 'controller' => 'Settings', 'action' => 'email'])
	) ?></p>
	<p><?= __('To register yourself as the primary contact, {0}. Otherwise, find the person with the form below and click the {1} icon.',
		$this->Html->link(__('click here'), ['plugin' => 'Javelin', 'controller' => 'Register', 'action' => 'index', 'person' => $this->Identity->get()->person->id]),
		'Javelin'
	) ?></p>
<?php // This, and the form processing, will need to change to accommodate affiliates ?>
<?= $this->element('People/search_form') ?>

	<div id="SearchResults" class="zuluru_pagination">

<?= $this->element('People/search_results', ['extra_url' => [__('Register as contact') => ['plugin' => 'Javelin', 'controller' => 'Register', 'action' => 'index']]]) ?>

	</div>
</div>
