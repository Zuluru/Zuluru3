<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Newsletter $newsletter
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;

$this->Breadcrumbs->add(__('Newsletter'));
$this->Breadcrumbs->add($newsletter->name);
$this->Breadcrumbs->add(__('Sending'));
?>

<div class="newsletters sent">
<h2><?= __('Sending') . ': ' . $newsletter->name ?></h2>
<?php
if ($execute) {
	echo $this->Html->para(null, __('Batch sent at {0}', $this->Time->time(FrozenTime::now())));
	echo $this->Html->para('warning-message', __('For the next batch to be sent, you must leave this screen open on this page!'));

	echo $this->Html->para(null, __('Sent email to') . ' ' . implode(', ', $emails));

	// Wait for a bit, then redirect to the next group
	$next = Router::url(['action' => 'send', '?' => ['newsletter' => $newsletter->id, 'execute' => true]], true);
	$this->Html->meta(['http-equiv' => 'refresh'], null, ['content' => "$delay;url=$next", 'block' => true]);
} else {
	if ($test) {
		echo $this->Html->para(null, __('Test email sent to') . ' ' . implode(', ', $emails));
	}
	echo $this->Html->para(null, __('To send yourself a test copy of this newsletter, {0}.',
		$this->Html->link(__('click here'), ['action' => 'send', '?' => ['newsletter' => $newsletter->id, 'test' => true]])));
	echo $this->Html->para(null, __('To initiate delivery of this newsletter, {0}.',
		$this->Html->link(__('click here'), ['action' => 'send', '?' => ['newsletter' => $newsletter->id, 'execute' => true]])));
}
?>
</div>
