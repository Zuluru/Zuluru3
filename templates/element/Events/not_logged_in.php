<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p class="error-message"><?= __('You cannot register for any events until you are {0} to the site. The system can help you {1} or {2}.',
	$this->Html->link(__('logged on'), Configure::read('App.urls.login')),
	$this->Html->link(__('recover forgotten passwords'), Configure::read('App.urls.resetPassword')),
	$this->Html->link(__('create a new profile (and user ID with password) if you are new to the {0} site', Configure::read('organization.short_name')), Configure::read('App.urls.register'))) ?></p>
