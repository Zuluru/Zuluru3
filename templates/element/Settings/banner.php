<?php
/**
 * @var \App\Model\Entity\Affiliate $affiliate
 */

use Cake\Core\Configure;

if (Configure::read('feature.affiliates')) {
	if ($affiliate) {
		echo $this->Html->para('warning-message', __('You are editing {0} settings. You should update only those that differ from the default. To use the default for text fields, simply leave the field blank.', $affiliate->name));
	} else {
		echo $this->Html->para('warning-message', __('You are editing the global system settings. These will be used for any affiliate that does not override them.'));
	}
}
