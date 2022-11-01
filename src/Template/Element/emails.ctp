<?php
/**
 * @type $people \App\Model\Entity\Person[]
 * @type $cc \App\Model\Entity\Person[]
 */

use App\Controller\AppController;

$emails = AppController::_extractEmails($people, false, true, true);
$link = 'mailto:' . implode(',', $emails);
if (!empty($cc)) {
	$cc_emails = AppController::_extractEmails($cc, false, true, true);
	$link .= '?cc=' . implode(',', $cc_emails);
	$emails = array_merge($emails, $cc_emails);
}
?>
<p><?= __('You can copy and paste the emails below into your addressbook, or {0}.',
	$this->Html->link(__('send an email right away'), $link));
?></p>
<?php
echo implode(',<br>', array_map('htmlentities', $emails));
?>
<p><?= __('Note that if you are using Microsoft Outlook, you may need to click in the To line of the message that pops up in order for the addresses to be recognized.') ?></p>
