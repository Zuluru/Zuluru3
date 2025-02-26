<?php
/**
 * @var \App\View\AppView $this
 */

// TODO: Delete this; any chance Cake will make their version public instead of protected?
if (!function_exists('_formatAddress')) {
	function _formatAddress($val, $key) {
		return "\"$val\" <$key>";
	}
}

if (is_array($from)) {
	$from = implode(', ', array_map('_formatAddress', $from, array_keys($from)));
}
if (is_array($replyTo)) {
	$replyTo = implode(', ', array_map('_formatAddress', $replyTo, array_keys($replyTo)));
}
if (is_array($to)) {
	$to = implode(', ', array_map('_formatAddress', $to, array_keys($to)));
}
if (is_array($cc)) {
	$cc = implode(', ', array_map('_formatAddress', $cc, array_keys($cc)));
}
if (is_array($bcc)) {
	$bcc = implode(', ', array_map('_formatAddress', $bcc, array_keys($bcc)));
}
?>
<p><?= __('From: {0}', h($from)) ?>
<?php
if (!empty($replyTo)):
?>
<br /><?= __('Reply-To: {0}', h($replyTo)) ?>
<?php
endif;
?>
<br /><?= __('To: {0}', h($to)) ?>
<?php
if (!empty($cc)):
?>
<br /><?= __('CC: {0}', h($cc)) ?>
<?php
endif;

if (!empty($bcc)):
?>
<br /><?= __('BCC: {0}', h($bcc)) ?>
<?php
endif;
?>
<br /><?= __('Subject: {0}', h($subject)) ?>
</p>
<p><?= __('Headers') ?>:</p>
<pre><?= h($result['headers']) ?></pre>
<p><?= __('Message') ?>:</p>
<?php
// Remove the html, head and body tags from the HTML in the message, if there is any, to keep the output sane.
// TODO: By default, just show the HTML version, with an expanding link that shows the raw message (including
// use of htmlentities on the HTML portion).
$matched = preg_match('#(.*)<!DOCTYPE.*<body>(.*)</body></html>(.*)#mis', $result['message'], $matches);
if ($matched) {
	if (!empty($matches[1])) {
		echo $this->Html->tag('pre', $matches[1]) . "\n";
	}
	echo $matches[2];
	if (!empty($matches[3])) {
		echo $this->Html->tag('pre', $matches[3]) . "\n";
	}
} else {
	echo $this->Html->tag('pre', $result['message']);
}
