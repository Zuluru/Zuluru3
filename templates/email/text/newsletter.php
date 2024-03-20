<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Newsletter $newsletter
 * @var \App\Model\Entity\Person $person
 */

use Cake\Core\Configure;
use Cake\Routing\Router;
use Html2Text\Html2Text;
?>

<?= $this->element('Email/text/newsletter_header'); ?>

<?php
$text = $newsletter->text;
if ($newsletter->personalize) {
	$text = strtr($text, [
		'first_name' => $person->first_name,
		'last_name' => $person->last_name,
		'full_name' => $person->full_name,
	]);
}
echo Html2Text::convert($text);
?>

<?php
if ($newsletter->personalize):
?>

<?= __('This message was sent to {0}.', $person->email) ?>

<?php
endif;
?>


<?= __('You have received this message because you are on the {0} {1} mailing list. To learn more about how we use your information, please read our privacy policy or contact {2}.',
	Configure::read('feature.affiliates') ? $newsletter->mailing_list->affiliate->name : Configure::read('organization.name'),
	$newsletter->mailing_list->name,
	Configure::read('email.admin_name')
) ?>


<?php
if ($newsletter->mailing_list->opt_out):
	if ($newsletter->personalize) {
		$unsubscribe_url = Router::url(['controller' => 'MailingLists', 'action' => 'unsubscribe', '?' => ['list' => $newsletter->mailing_list->id, 'person' => $person->id, 'code' => $code]], true);
	} else {
		$unsubscribe_url = Router::url(['controller' => 'MailingLists', 'action' => 'unsubscribe', '?' => ['list' => $newsletter->mailing_list->id]], true);
	}

	echo __('To unsubscribe from this mailing list, {0}.', __('click here: {0}', $unsubscribe_url));
	if (!$newsletter->personalize) {
		echo ' ' . __('You must be logged in to the web site for this to work.');
	}
?>


<?php
endif;
?>

<?= $this->element('Email/text/newsletter_footer') ?>
