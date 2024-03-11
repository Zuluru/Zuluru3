<?php
/**
 * @type $registrations \App\Model\Entity\Registration[]
 * @type $audit \App\Model\Entity\RegistrationAudit|null
 */
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$reg_id_format = Configure::read('payment.reg_id_format');
// TODOBOOTSTRAP: Fix the formatting of this whole ugly page
?>
<table border=0 width=700>
<?php
if (isset($audit)):
?>
	<tr><td colspan="4" class="center"><span class="warning-message"><?= __('Your Transaction has been Approved') ?></span></td></tr>
	<tr><td colspan="4" class="center"><span class="warning-message"><?= __('Print this receipt for your records') ?></span></td></tr>
	<tr><td colspan="4" bgcolor="#EEEEEE">&nbsp;</td></tr>
<?php
endif;
?>
	<tr><td align="center" colspan="4"><h2 class="center"><?= Configure::read('organization.name') ?></h2></td></tr>
	<tr><td align="center" colspan="4"><?= Configure::read('organization.address') ?></td></tr>
	<tr><td align="center" colspan="4"><?= Configure::read('organization.address2') ?></td></tr>
	<tr><td align="center" colspan="4"><?= Configure::read('organization.city') ?>, <?= Configure::read('organization.province') ?></td></tr>
	<tr><td align="center" colspan="4"><?= Configure::read('organization.postal') ?></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="4"><?= Configure::read('organization.phone') ?></td></tr>

	<tr><td align="center" colspan="4"><a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/"><?= $_SERVER['HTTP_HOST'] ?></a></td></tr>
<?php
if (!empty(Configure::read('organization.hst_registration'))):
?>
	<tr><td align="center" colspan="4"><?= __('HST Registration: {0}', Configure::read('organization.hst_registration')) ?></td></tr>
	<tr><td align="center" colspan="4"><?= __('Invoice # {0}', sprintf(Configure::read('registration.order_id_format'), $registrations[0]->id)) ?></td></tr>
<?php
endif;
?>
	<tr><td>&nbsp;</td></tr>

<?php
if (isset($audit)):
?>
	<tr bgcolor="#EEEEEE"><td colspan="4"><b><?= __('Transaction Type') ?>:
<?php
	switch ($audit->transaction_name) {
		case 'purchase':
		case 'cavv_purchase':
			echo __('Purchase');
			break;

		case 'idebit_purchase':
			echo __('Debit Purchase');
			break;

		case 'preauth':
		case 'cavv_preauth':
			echo __('Pre-authorization');
			break;

		default:
			echo __($audit->transaction_name);
	}
?>
	</b></td></tr>
	<tr><td><?= __('Order ID') ?>:</td><td><?= $audit->order_id ?></td></tr>
	<tr>
		<td><?= __('Date / Time') ?>:</td><td><?= "{$audit->date}  {$audit->time}" ?></td>
<?php
	if (array_key_exists('approval_code', $audit)):
?>
		<td><?= __('Approval Code') ?>:</td><td><?= $audit->approval_code ?></td>
<?php
	else:
?>
		<td></td><td></td>
<?php
	endif;
?>
	</tr>
	<tr>
		<td nowrap><?= __('Sequence Number') ?>:</td><td><?= $audit->transaction_id ?></td>
<?php
	if (array_key_exists('iso_code', $audit)):
?>
		<td><?= __('Response&nbsp;/&nbsp;ISO Code') ?>:</td><td nowrap><?= "{$audit->response_code}/{$audit->iso_code}" ?></td>
<?php
	else:
?>
		<td><?= __('Response Code') ?>:</td><td><?= $audit->response_code ?></td>
<?php
	endif;
?>
	</tr>
	<tr>
		<td><?= __('Amount') ?>:</td><td><?= $this->Number->Currency($audit->charge_total) ?></td>
<?php
	if (array_key_exists('f4l4', $audit)):
?>
		<td><?= __('Card #') ?>:</td><td><?= $audit->f4l4 ?></td>
<?php
	else:
?>
		<td></td><td></td>
<?php
	endif;
?>
	</tr>
<?php
	if (array_key_exists('message', $audit)):
?>
	<tr><td colspan="4" nowrap><?= __('Message') ?>: <?= $audit->message ?></td></tr>
<?php
	endif;
?>
	<tr><td>&nbsp;</td></tr>

<?php
	if ($audit->transaction_name === 'idebit_purchase'):
?>
	<tr bgcolor="#EEEEEE"><td colspan="4"><b><?= __('INTERAC&reg; Online Information') ?></b></td></tr>
	<tr>
		<td><?= __('Issuer Name') ?>:</td><td><?= $audit->issuer ?></td>
	</tr>
	<tr>
		<td><?= __('Issuer Confirmation') ?>:</td><td><?= $audit->issuer_invoice ?></td>
	</tr>
	<tr>
		<td><?= __('Issuer Invoice #') ?>:</td><td><?= $audit->issuer_confirmation ?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
<?php
	endif;
endif;
?>

</table>

<table border="0" cellspacing="1" cellpadding="3" width="700">
	<tr><td colspan=5 bgcolor="#EEEEEE"><strong><?= __('Item Information') ?></strong></td></tr>
	<tr>
		<td bgcolor="#DDDDDD" width=100><strong><?= __('ID') ?></strong></td>
		<td bgcolor="#DDDDDD" width=350><strong><?= __('Description') ?></strong></td>
		<td bgcolor="#DDDDDD" width=50 align="middle"><strong><?= __('Quantity') ?></strong></td>
		<td bgcolor="#DDDDDD" width=100 align="right"><strong><?= __('Unit Cost') ?></strong></td>
		<td bgcolor="#DDDDDD" width=100 align="right"><strong><?= __('Subtotal') ?></strong></td>
	</tr>
<?php
$date = null;
foreach ($registrations as $registration):
	$paid = collection($registration->payments)->filter(function (Payment $payment) { return in_array($payment->payment_type, Configure::read('payment_payment')); })->max('created');
	if ($paid && $paid->created > $date) {
		$date = $paid->created;
	}

	if (isset($audit)) {
		[$cost, $tax1, $tax2] = $registration->paymentAmounts();
	} else {
		[$cost, $tax1, $tax2] = [$registration->price->cost, $registration->price->tax1, $registration->price->tax2];
	}
?>
	<tr>
		<td valign="top"><?= sprintf($reg_id_format, $registration->event->id) ?></td>
		<td valign="top"><?= $registration->long_description ?></td>
		<td valign="top">1</td>
		<td valign="top" align="right"><?= $this->Number->currency($cost) ?></td>
		<td valign="top" align="right"><?= $this->Number->currency($cost) ?></td>
	</tr>

<?php
	if ($tax1 > 0):
?>
	<tr>
		<td></td><td></td><td></td>
		<td align="right"><?= Configure::read('payment.tax1_name') ?>:</td>
		<td align="right"><?= $this->Number->currency($tax1) ?></td>
	</tr>
<?php
	endif;

	if ($tax2 > 0):
?>
	<tr>
		<td></td><td></td><td></td>
		<td align="right"><?= Configure::read('payment.tax2_name') ?>:</td>
		<td align="right"><?= $this->Number->currency($tax2) ?></td>
	</tr>
<?php
	endif;
endforeach;

if (isset($audit)) {
	$total = $audit->charge_total;
} else {
	$total = $cost + $tax1 + $tax2;
}
?>

	<tr>
		<td></td><td></td><td></td><td align="right"><?= __('Total') ?>:</td>
		<td align="right"><?= $this->Number->currency($total) ?></td>
	</tr>
<?php
if ($date):
?>
	<tr>
		<td></td><td></td><td></td><td align="right"><?= __('Paid') ?>:</td>
		<td align="right"><?= $this->Time->date($date) ?></td>
	</tr>
<?php
endif;
?>
</table>

<table width="700" cellspacing=3 cellpadding=3>
	<tr><td bgcolor="#EEEEEE"><strong><?= __('Customer Information') ?></strong></td></tr>
	<tr>
		<td><?= $registration->person->full_name ?></td>
	</tr>
	<tr>
		<td><?= $registration->person->addr_street ?></td>
	</tr>
	<tr>
		<td><?= $registration->person->addr_city ?></td>
	</tr>
	<tr>
		<td><?= $registration->person->addr_prov ?></td>
	</tr>
	<tr>
		<td><?= $registration->person->addr_postalcode ?></td>
	</tr>
	<tr>
		<td><?= $registration->person->addr_country ?></td>
	</tr>
	<tr>
		<td><?= $registration->person->home_phone ?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>

	<tr><td><?= $this->element('Payments/refund') ?></td></tr>

</table>
