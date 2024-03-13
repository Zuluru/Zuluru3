<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

$no_matches = __('No matching offering, please try a different option');
$multiple_matches = __('Multiple matches, please refine your selection');
$locale = \Cake\I18n\I18n::getLocale();
$currency = Configure::read('payment.currency');
$taxes = __('(taxes included)');

echo $this->Html->scriptBlock("
const formatter = new Intl.NumberFormat('$locale', {
	style: 'currency',
	currency: '$currency',
});

function resetRadio(trigger) {
	table = trigger.closest('table').find('input:checked').not('.disabled').each(function() {
		i = zjQuery(this);
		if (i.attr('disabled') === undefined) {
			i.prop('checked', false);
			radioChanged(i);
		}
	});
}

function radioChangedCallback(trigger, row) {
	options = row.find('span.prices:visible');
	el = row.find('span.final');

	if (options.length === 0) {
		html = '$no_matches';
	} else {
		min = options.get().reduce(function (result, item) {
			return Math.min(result, zjQuery(item).data('min-cost'));
		}, 999999);
		max = options.get().reduce(function (result, item) {
			return Math.max(result, zjQuery(item).data('max-cost'));
		}, 0);

		html = formatter.format(min);
		if (max !== min) {
			html += ' - ' + formatter.format(max);
		}
		html += ' ' + '$taxes';

		if (options.length === 1) {
			action = zjQuery(options[0]).data('link');
			html += '<br/>' + zjQuery(options[0]).data('event');
		} else {
			action = '$multiple_matches';
		}

		html += '<br/>' + action;
	}

	el.html(html);
}

function initializePrices() {
	zjQuery('.final').each(function () {
		row = zjQuery(this).closest('tr');
		radioChangedCallback(zjQuery(this), row);
	});
}
");

$this->Html->scriptBlock('initializePrices();', ['buffer' => true]);
