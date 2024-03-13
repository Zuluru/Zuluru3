<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Notice $notice
 */

use Cake\Core\Configure;

if (!empty($notice)):
?>
<div id="SystemNotice">
<?php
// Use system configuration and current user record to do replacements in notice text
$person = $this->UserCache->read('Person');
while (preg_match('#(.*)<%person (.*?) %>(.*)#', $notice->notice, $matches)) {
	$notice->notice = $matches[1] . $person->{$matches[2]} . $matches[3];
}

while (preg_match('#(.*)<%icon (.*?) %>(.*)#', $notice->notice, $matches)) {
	$notice->notice = $matches[1] . $this->Html->iconImg($matches[2]) . $matches[3];
}

while (preg_match('#(.*)<%link (.*?) (.*?) (.*?) %>(.*)#', $notice->notice, $matches)) {
	$notice->notice = $matches[1] . $this->Html->link($matches[4], ['plugin' => false, 'controller' => $matches[2], 'action' => $matches[3]]) . $matches[5];
}

while (preg_match('#(.*)<%setting (.*?) %>(.*)#', $notice->notice, $matches)) {
	$notice->notice = $matches[1] . Configure::read($matches[2]) . $matches[3];
}

echo $notice->notice;
?>
	<div class="actions">
<?php
	echo $this->Jquery->ajaxLink(__('I\'m busy, remind me later'), [
		'url' => ['plugin' => false, 'controller' => 'Notices', 'action' => 'viewed', $notice->id, true],
		'disposition' => 'hide',
		'selector' => '#SystemNotice',
	]);
	echo $this->Jquery->ajaxLink(__('Okay, got it'), [
		'url' => ['plugin' => false, 'controller' => 'Notices', 'action' => 'viewed', $notice->id],
		'disposition' => 'hide',
		'selector' => '#SystemNotice',
	]);
?>
	</div>
</div>
<?php
endif;
