<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Newsletter $newsletter
 * @var \App\Model\Entity\Person $person
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<div id="newsletter">
	<?= $this->element('Email/html/newsletter_header') ?>
	<div id="newsletter_body">
<?php
$text = $newsletter->text;
if ($newsletter->personalize) {
	$text = strtr($text, [
		'first_name' => $person->first_name,
		'last_name' => $person->last_name,
		'full_name' => $person->full_name,
	]);
}
echo $text;
?>

		<div id="newsletter_epilogue">
<?php
if ($newsletter->personalize):
?>
			<p><?= __('This message was sent to {0}.', $person->email) ?></p>
<?php
endif;
?>

			<p><?= __('You have received this message because you are on the {0} {1} mailing list. To learn more about how we use your information, please read our privacy policy or contact {2}.',
				Configure::read('feature.affiliates') ? $newsletter->mailing_list->affiliate->name : Configure::read('organization.name'),
				$newsletter->mailing_list->name,
				$this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email'))
			) ?></p>

<?php
if ($newsletter->mailing_list->opt_out):
	if ($newsletter->personalize) {
		$unsubscribe_url = Router::url(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $newsletter->mailing_list->id, 'person' => $person->id, 'code' => $code], true);
	} else {
		$unsubscribe_url = Router::url(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $newsletter->mailing_list->id], true);
	}
?>
			<p><?php
				echo __('To unsubscribe from this mailing list, {0}.', $this->Html->link(__('click here'), $unsubscribe_url));
				if (!$newsletter->personalize) {
					echo ' ' . __('You must be logged in to the web site for this to work.');
				}
			?></p>
<?php
endif;
?>
		</div>
	</div>
	<?= $this->element('Email/html/newsletter_footer') ?>
</div>
