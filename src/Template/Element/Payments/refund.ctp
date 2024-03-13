<?php
/**
 * @var \App\View\AppView $this
 * @var string $refund
 */

use Cake\Core\Configure;

$refund = Configure::read('registration.refund_policy_text');
if (!empty($refund)) {
	echo $this->Html->tag('h2', __('Refund Policy'));
	echo $refund;
}
