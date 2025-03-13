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

function resetSelectorForm(trigger) {
	trigger.closest('table').find('input').not('.disabled').each(function() {
		input = zjQuery(this);
		form = input.closest('form');

		if (!form.attr('data-auto-selected-by-select')) {
			input.prop('checked', false).removeAttr('disabled');
			radioChanged(input);
		}
	});
}

function radioChangedCallback(trigger, row, radio_selector) {
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

	if (zjQuery(trigger).prop('checked')) {
		// There may now be other radio forms in the same row that have only one valid option. Grey them out.
		var form_id = zjQuery(trigger).closest('form').attr('id');
		row.find('form').not('#' + form_id).each(function() {
			form = zjQuery(this);
			if (form.attr('data-auto-selected-by-select')) {
				return;
			}

			options = form.find(radio_selector);
			if (options.length === 1) {
				if (!zjQuery(options[0]).prop('checked')) {
					form.find('input').attr('disabled', 'disabled');
					options.removeAttr('disabled');
					options.prop('checked', true);
					form.attr('data-auto-selected-by-radio', 1);
				}
			} else if (options.length > 1) {
				form.find('input').attr('disabled', 'disabled');
				options.removeAttr('disabled');
			}
		});
	}
}

function initializePrices() {
	zjQuery('.final').each(function () {
		row = zjQuery(this).closest('tr');
		radioChangedCallback(zjQuery(this), row, '');
	});
}
");

$this->Html->scriptBlock('initializePrices();', ['buffer' => true]);
