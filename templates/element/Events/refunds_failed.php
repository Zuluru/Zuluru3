<?php
/**
 * @var \App\View\AppView $this
 * @var string[] $failed
 */

use Cake\Core\Configure;
?>
<?php
echo $this->Html->para(null, __('The following registrations failed to be refunded. They may be special cases that need to be dealt with manually.'));
echo $this->Html->para(null, __(' Any selected registrations not listed here were handled successfully.'));
$list = [];
foreach ($failed as $id => $name) {
	$invnum = sprintf(Configure::read('registration.order_id_format'), $id);
	$list[] = $this->Html->link("$name ($invnum)", ['controller' => 'Registrations', 'action' => 'view', '?' => ['registration' => $id]], ['target' => '_new']);
}
echo $this->Html->nestedList($list);
