/**
 * Collection of JavaScript methods for dealing with common Zuluru tasks.
 */

/**
 * Array for caching already-loaded tooltip data.
 * @type {Array}
 */
var tooltip_text = new Array();

/**
 * Function for dealing with tooltips.
 * @param base Base URL
 * @param trigger Element that triggered the tooltip load
 * @param callback jQuery UI-provided callback function to send the data to
 */
function loadTooltip(base, trigger, callback) {
	var id = trigger.attr('id');
	if (tooltip_text[id] == undefined) {
		// Put a timer in here to only start loading if the mouse stays for 500 ms.
		// Otherwise, Ajax calls start firing immediately whenever the mouse is
		// passed over such a link, causing server load for no reason.
		setTimeout(function() {
			if (!trigger.data('wait_for_tooltip')) {
				return;
			}
			var params = id.split('_');
			Pace.ignore(function(){
				zjQuery.ajax({
					type: 'GET',
					url: base + params[0] + '/tooltip?' + params[1] + '=' + params[2],
					success: function(data){
						// TODOLATER: Handle _message and _redirect like in handleAjaxRequest below
						tooltip_text[id] = data.content;
						if (trigger.data('wait_for_tooltip')) {
							callback(data.content);
							trigger.data('tooltip_displayed', true);
							trigger.data('wait_for_tooltip', false);
						}
					},
					error: function(message){
						// If the status is 0, it's probably because the user
						// clicked a link before the tip text loaded
						if (message.status != 0) {
							alert(message.statusText);
						}
					}
				});
			});
		}, 500);
	} else {
		callback(tooltip_text[id]);
		trigger.data('tooltip_displayed', true);
		trigger.data('wait_for_tooltip', false);
	}
}

/**
 * Callback for the sortable update function, to update the "sort" inputs
 */
function tableReorder(table) {
	var position = 0;
	zjQuery('tr', table).each(function () {
		var sort = zjQuery('input[name$="[sort]"]', zjQuery(this));
		if (sort.length > 0) {
			// Update the sort field with the new counter.
			sort.val(++position);
		}
	});
}

/**
 * Deal with various selector drop-downs
 */
function selectorChanged(trigger) {
	var show_selector = '';
	zjQuery('span.selector').find('select').each(function() {
		var id = zjQuery(this).attr('id');
		var setting = zjQuery(this).val();
		if (setting != '') {
			show_selector += '.' + id + '_' + setting;
		}
	});
	var all = zjQuery('[class*=\"selector_\"]');

	if (show_selector == '') {
		all.css('display', '');
		all.filter(':input').not('.disabled').removeAttr('disabled');
	} else {
		var show = zjQuery(show_selector);
		all.css('display', 'none');
		show.css('display', '');
		all.filter(':input').attr('disabled', 'disabled');
		show.filter(':input').not('.disabled').removeAttr('disabled');
	}

	if (trigger) {
		var id = trigger.attr('id');
		var setting = trigger.val();
		var all_radio = zjQuery('span.selector div.radio').find(':input[name="' + id + '"]');

		if (setting == '') {
			// Reset related radio selectors
			all_radio.removeAttr('disabled');
			all_radio.prop('checked', false);
		} else {
			// Set related radio selectors
			all_radio.not('.disabled').attr('disabled', 'disabled');
			all_radio.not('.disabled').prop('checked', false);
			var show_radio = all_radio.filter('[value="' + trigger.val() + '"]');
			show_radio.prop('checked', true);
		}

		all_radio.each(function() {
			radioChanged(this);
		});
	}
}

/**
 * Deal with various selector radio buttons
 */
function radioChanged(trigger) {
	// This is only supported right now for radio inputs in tr elements
	var row = zjQuery(trigger).closest('tr');
	if (row.length == 0) {
		console.log('Unsupported radio selector scenario');
		return;
	}

	var show_selector = '';
	row.find('input:checked').each(function() {
		var name = zjQuery(this).attr('name');
		var setting = zjQuery(this).val();
		if (setting != '') {
			show_selector += '.' + name + '_' + setting;
		}
	});
	var all = row.find('[class*=\"selector_\"]');
	if (show_selector == '') {
		all.css('display', '');
		all.filter(':input').not('.disabled').removeAttr('disabled');
	} else {
		var show = row.find(show_selector);
		all.css('display', 'none');
		show.css('display', '');
		all.filter(':input').attr('disabled', 'disabled');
		show.filter(':input').not('.disabled').removeAttr('disabled');
	}

	// Call any local callback function
	if (typeof radioChangedCallback === 'function') {
		radioChangedCallback(trigger, row);
	}
}

function closeInPlaceWidgets(container) {
	var open = false;

	// Close any already-open widgets, and cancel any associated bindings
	zjQuery('.zuluru-in-place-widget-options').each(function () {
		if (zjQuery(this).css('display') != 'none') {
			if (container) {
				// Find the expected position, relative to the clicked link
				var e_offset = container.offset();
				var a_offset = zjQuery(this).offset();
				if (a_offset.top == e_offset.top + 16 && a_offset.left == e_offset.left + 10) {
					open = true;
				}
			}
			zjQuery(this).css('display', 'none');
			zjQuery('body').unbind('click.zuluruWidget');
			zjQuery('body').unbind('keyup.zuluruWidget');
		}
	});

	// Mark all widgets as not open
	zjQuery('.zuluru-in-place-widget-open').removeClass('zuluru-in-place-widget-open');

	// If the thing that was clicked on was an already-open widget (now closed),
	// return true from this, and the caller will skip re-opening it.
	return open;
}

/**
 * Generic event handler function for our various Ajax triggers
 *
 * This is all driven by parameters saved in the DOM via HTML5 custom data
 * attributes. Note that some combinations of parameters won't make any sense,
 * but it will try to do its best...
 *
 * Available data values:
 *
 * - url: The URL to send the request to. Elements inside of forms are allowed to
 *   skip this, in which case the form target will be used.
 *
 * - disposition: One of "remove", "remove_closest", "remove_selector", "replace",
 *   "replace_content", "replace_closest", "append", "prepend", "before" or "hide",
 *   indicating which method of dealing with the result will be used. The
 *   "remove_closest" and "remove_selector" options require the "selector" option
 *   to be included as well.
 *
 * - selector: The jQuery selector to be used to find the element to operate on.
 *   For the "remove_closest" or "replace_closest" dispositions, this will be
 *   something like "div"; for the rest it will generally be an ID selector like
 *   "#row123". Where the selector is optional, the target will default to the
 *   trigger element.
 *
 * - remove-separator: In cases where there are a number of links separated by
 *   something, this indicates the something. For example, ", " or "</br>". It
 *   will be removed from between items when one is removed.
 *
 * - remove-separator-selector: When "remove-separator" is given, this option can
 *   provide a jQuery selector to be used to find the parent element that should
 *   be removed when the last link in a list is gone. "tr" would be a common
 *   example, removing the entire row when the cell with the links is empty.
 *
 * - confirm: If present, a confirmation prompt displayed to the user before taking
 *   the requested action.
 *
 * - dialog: Optional ID (not including #) of a dialog to open before posting.
 *   The dialog is expected to include a single form element. Save and Cancel
 *   buttons will be added to it. If a value is found (per the item below), the
 *   first input in the div will be populated with that value, otherwise it will
 *   be blanked on opening.
 *
 * - value: The value to send as a parameter. If the trigger is a widget option,
 *   the value is instead used as a name (with "-value" appended) to look up the
 *   true value in the main widget.
 *
 * - param-name: Used to change the field name used when submitting the parameter
 *   as data.
 *
 * - additional-inputs: Comma-separated list of selectors for inputs that should be
 *   added to the data being submitted.
 *
 * @param trigger Element that triggered the action
 * @param default_disposition The default data disposition for this action
 */
function handleAjaxTrigger(trigger, container, widget, default_disposition, require_data, input_selector) {
	var confirm_msg = container.attr('data-confirm');
	if (confirm_msg && !confirm(confirm_msg)) {
		return;
	}

	// The trigger might have unknowingly been inside a widget
	if (!widget) {
		var in_widget = trigger.closest('.zuluru-in-place-widget');
		if (in_widget) {
			closeInPlaceWidgets();
		}
	}

	// Some options need us to open a dialog, and the actual Ajax submission comes from there
	var dialog_id = trigger.attr('data-dialog');
	if (dialog_id) {
		dialog = zjQuery('#' + dialog_id);
		if (!dialog.length) {
			alert('Dialog ' + dialog_id + ' requested, but not found.');
			return;
		}

		var submit = dialog.find('[type=submit]');
		var buttons = {};
		if (submit.length == 0) {
			buttons[zuluru_save] = function() {
				dialog.off('keypress');

				// Collect the data to be sent. Links (e.g. jersey number) don't have param and value, their data
				// comes only from the dialog form. For in-place widgets (e.g. attendance comments), data-param
				// comes from the url_param parameter, the value is set on each option, and the dialog form
				// provides additional data.
				var form = dialog.find('form');
				if (!form.length) {
					alert('Dialog ' + dialog_id + ' does not contain a form.');
					return;
				}
				var data = new FormData(form.get(0));
				if (container.attr('data-param')) {
					data.append(container.attr('data-param'), trigger.attr('data-value'));
				}
				dialog.dialog('close');
				handleAjaxRequest(trigger, container, widget, default_disposition, require_data, input_selector, data);
			};
			dialog.on('keypress', function(event) {
				if (event.keyCode === zjQuery.ui.keyCode.ENTER) {
					dialog.off('keypress');
					var save = dialog.closest('.ui-dialog').find('button:contains("' + zuluru_save + '")');
					save.click();
					return false;
				}
			});
		} else {
			dialog.on('click', '[type=submit]', function() {
				dialog.off('click', '[type=submit]');
				var form = $(this).closest('form');
				if (!form.length) {
					alert('Dialog does not contain a form.');
					return;
				}
				var data = new FormData(form.get(0));
				dialog.dialog('close');
				handleAjaxRequest(trigger, container, widget, default_disposition, require_data, input_selector, data);
				// Prevent the form from being posted
				return false;
			});
		}
		buttons[zuluru_cancel] = function() {
			dialog.off('click', '[type=submit]');
			dialog.dialog('close');
		}

		dialog.dialog({
			buttons: buttons,
			modal: true,
			resizable: false,
			width: 480
		});

		// Perhaps initialize the dialog input with the provided data
		var input = dialog.find(':input');
		if (input.length != 1) {
			alert('Dialog ' + dialog_id + ' must have exactly one input in it.');
			return;
		}
		input = input[0];

		// TODO: Currently, the only things that support dialogs are in-place widgets and links.
		// If we find use cases for this with the other Ajax elements, this may need to be updated.
		var value = trigger.attr('data-value');
		if (widget) {
			if (!value) {
				alert('No value found on selected option.');
				return;
			}

			// If this is a widget, then the data-value on the trigger is what should be sent as the data-param,
			// and the value that goes in the form comes from the same-named data in the widget itself.
			value = widget.attr('data-' + value + '-value');
		}

		if (value) {
			zjQuery(input).val(value);
			input.selectionStart = input.selectionEnd = input.value.length;
		} else {
			zjQuery(input).val('');
		}

		zjQuery(input).focus();
	} else {
		var data = new FormData();
		if (container.attr('data-param')) {
			data.append(container.attr('data-param'), trigger.attr('data-value'));
		}
		handleAjaxRequest(trigger, container, widget, default_disposition, require_data, input_selector, data);
	}
}

/**
 * Handler function for the various Ajax requests that may be generated from our event handlers
 * @param trigger Element that triggered the request
 * @param data Optional data, e.g. from a form submission
 */
function handleAjaxRequest(trigger, container, widget, default_disposition, require_data, input_selector, data) {
	// Find the inputs to be included, if any
	if (!input_selector) {
		input_selector = trigger.attr('data-input-selector');
	}
	var inputs = zjQuery();
	var form = trigger.closest('form');
	if (input_selector) {
		inputs = inputs.add(zjQuery(input_selector));
	} else {
		if (form) {
			inputs = form.find(':input');
		} else if (require_data) {
			alert('No input selector given, and form was not found!');
			return;
		}
	}
	inputs = inputs.filter(':enabled');

	// Find the URL to post to
	if (widget) {
		var url = widget.attr('data-url');
	} else {
		var url = trigger.attr('data-url');
	}
	if (!url) {
		if (!form) {
			alert('No url given, and form was not found!');
			return;
		}
		url = form.attr('action');
	}

	var disposition = trigger.attr('data-disposition') || default_disposition;
	var disposition_selector = trigger.attr('data-selector');
	var target = null;

	// All but the *_closest dispositions may take a specific (e.g. ID) selector as their target
	if (disposition_selector && disposition != 'remove_closest' && disposition != 'replace_closest') {
		target = zjQuery(disposition_selector);
		if (!target) {
			alert('Selector ' + disposition_selector + ' requested, but not found.');
			return;
		}
	} else if (widget) {
		// For in-place widgets, the target is the widget, not the triggering option
		target = widget;
	} else {
		// Leave it as the trigger element for now, for the purposes of the spinner display or element removal
		target = trigger;
	}

	// Check if there are any other fields to be included
	var input_selectors = trigger.attr('data-additional-inputs');
	if (input_selectors) {
		input_selectors = input_selectors.split(',');
		for (i = 0; i < input_selectors.length; ++i) {
			var input = zjQuery(input_selectors[i]);
			if (!input) {
				alert('Invalid input selector "' + input_selectors[i] + '"!');
				return;
			}
			inputs = inputs.add(input);
		}
	}

	// Serialize all of the required data
	inputs.each(function() {
		if (!data) {
			data = new FormData();
		}

		input = zjQuery(this);
		if (input.attr('data-type') == 'date') {
			// Date inputs need to send all three fields, which we assume are inside the closest parent div
			input.closest('div').find('select').each(function() {
				data.append(zjQuery(this).attr('name'), zjQuery(this).val());
			});
		} else {
			var param_name = input.attr('data-param-name');
			if (param_name) {
				// Sometimes, the field name needs to be overridden
				data.append(param_name, input.val());
			} else if (!input.is(':checkbox') || input.prop('checked')) {
				data.append(input.attr('name'), input.val());
			}
		}
	});

	// If we are removing something, remove it now, don't want for the response from the server. Can't be done any
	// earlier, though, because some data above might come from the element we're going to remove.
	var remove = null;
	var hide = false;
	switch (disposition) {
		case 'hide':
			hide = true;
			// Intentionally fall through
		case 'remove':
			remove = target;
			break;
		case 'remove_closest':
			if (!disposition_selector) {
				alert('Error: "remove_closest" disposition requested, but no selector provided.')
			} else {
				remove = trigger.closest(disposition_selector);
				if (!remove) {
					alert('Selector ' + disposition_selector + ' requested, but not found.');
				}
			}
			break;
		case 'remove_selector':
			if (!disposition_selector) {
				alert('Error: "remove_selector" disposition requested, but no selector provided.')
			} else {
				remove = zjQuery(disposition_selector);
			}
			break;
	}

	if (remove) {
		// Some pages will have a series of items separated by *something*, and we
		// need to remove that separator too. We'll deal with that detail in the
		// animatedRemove function, because it needs to be done after the remove
		// finishes, which doesn't happen until after the animation is done, but
		// the animate function returns immediately.
		var separator = trigger.attr('data-remove-separator');
		var selector = trigger.attr('data-remove-separator-selector');
		animatedRemove(remove, hide, separator, selector);
	}

	// Set up the options we want on the Ajax request.
	var opts = {
		type: 'post',
		url: url,
		context: widget,
		beforeSend: function (xhr) {
			// TODOBOOTSTRAP: This doesn't work right if it's a link: it's still got the ".actions" styling, and it can't be undone if the call fails.
			// Similar, though less severe, issues with the other uses. Maybe we hide everything in the target and append the spinner to it,
			// then later we can remove the spinner and unhide things? Won't solve the a.actions problem.
			if (disposition == 'replace_content' || disposition == 'replace') {
				target.html(zuluru_spinner);
			}
			// This doesn't work with FormData. xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		}
	};

	if (data) {
		opts.data = data;
		opts.processData = false; // tell jQuery not to process the data
		opts.contentType = false; // tell jQuery not to set contentType
	}

	zjQuery.ajax(opts)
		.done(function (response) {
			// Any time there is a message, we want to display it. It means *something* failed,
			// though the request may still have been considered a success. For example, we
			// updated the database, but failed to send an email about it.
			if (response._message) {
				// TODOTESTING: Remove this check once Ajax component bug is fixed
				//if (response._message[0].message != null) {
					alert(response._message[0].message);
					console.log(response._message[0].message);
				//}
			}

			// An error means that it failed, and we want to display that error message.
			// A redirect also means that it failed, but the message associated with that
			// will already be in the _message displayed above. In that case, we've done
			// everything we need to do. If neither of these are true, it was a success,
			// and we need to do something to the DOM as a result.
			if (response.error) {
				alert(response.error);
				console.log(response.error);
			} else if (!response._redirect) {
				switch (disposition) {
					case 'replace':
						target.replaceWith(response.content);
						break;
					case 'replace_content':
						target.html(response.content);
						break;
					case 'replace_closest':
						if (!disposition_selector) {
							alert('Error: "replace_closest" disposition requested, but no selector provided.')
						} else {
							replace = trigger.closest(disposition_selector);
							if (!replace) {
								alert('Selector ' + disposition_selector + ' requested, but not found.');
							}
							replace.replaceWith(response.content);
						}
						break;
					case 'append':
						target.append(response.content);
						break;
					case 'prepend':
						target.prepend(response.content);
						break;
					case 'before':
						target.before(response.content);
						break;
					case 'remove':
					case 'remove_closest':
					case 'remove_selector':
					case 'hide':
						// These cases are taken care of earlier
						break;
					default:
						alert('Unknown Ajax disposition: ' + disposition + "\n" + 'Content was:' + "\n" + response.content);
						break;
				}

				// Call the initialize function to ensure any new fields are set up correctly.
				initializeStatus();

				// If the target is, or is in a sortable table, call Reorder
				sortable = target.closest('table.sortable');
				if (sortable) {
					tableReorder(sortable);
				}
			} else if (response._redirect.status == 100) {
				// We use status 100 to indicate that we really truly actually want the browser to redirect
				window.location.href = response._redirect.url;
			}
		})
		.fail(function (e) {
			alert('An error occurred: ' + e.responseText);
			console.log(e);
		});
}

/**
 * Remove elements from the DOM in an animated way.
 * @param remove Element to remove
 * @param hide If true, use "hide" visuals instead of "remove", which right now just doesn't change the colour.
 * @param separator Optional separator text that needs to be detected and removed from either side of the element
 * @param selector In the case of separators, the selector to be used to locate the ancestor of the element to
 * 			remove, if the element's parent becomes empty.
 */
function animatedRemove(remove, hide, separator, selector) {
	// Table rows can't be reliably animated, so we animate the cells instead
	var animate;
	if (remove.is('tr')) {
		animate = remove.children();
	} else {
		animate = remove;
	}

	// Fade to red, then scroll it out, then remove it entirely.
	if (hide) {
		var change = {};
	} else {
		var change = {backgroundColor: "#fbc7c7"};
	}
	animate.animate(change, function () {
		zjQuery(this).slideUp(function () {
			// If we're also dealing with separators, we do it below, but we do need
			// to save this item's parent to examine; it'll be too late to find it
			// once we remove the item in question!
			var parent = null;
			if (separator) {
				parent = remove.parent();
			}

			// Now we can safely remove it.
			remove.remove();

			if (parent) {
				// Now examine and optionally tweak the parent's contents.
				var contents = parent.html();
				if (contents) {
					var len = separator.length;
					if (contents.substr(0, len) == separator) {
						contents = contents.substr(len);
					} else if (contents.substr(contents.length - len, len) == separator) {
						contents = contents.substr(0, contents.length - len);
					} else {
						contents = contents.replace(separator + separator, separator);
					}
				}

				// If there's nothing left, remove the entire parent
				if (contents) {
					parent.html(contents);
				} else {
					if (selector) {
						parent = parent.closest(selector);
					}
					animatedRemove(parent, hide);
				}
			}
		})
	});
}

/**
 * Function to show/hide things based on an input's current value
 */
function toggleInput(trigger) {
	// Behaviour is different for checkboxes vs selects
	if (trigger.is(':checkbox')) {
		var value = trigger.prop('checked');
	} else if (trigger.is('select')) {
		var value = trigger.val();
		value = value.replace(/\s/g, '');
		if (value == '') {
			value = 'empty-string';
		}
	} else if (trigger.is('input')) {
		var value = trigger.val();
		if (value == '' || value == '.00' || value == '0.00') {
			value = 0;
		}
	} else {
		alert('Toggle trigger type is not recognized.');
		console.log(trigger);
	}

	// Get the key pieces of data from the DOM
	var values = trigger.attr('data-values');
	if (values) {
		var selector_hide = [];
		values = values.split(' ');
		var i;
		for (i = 0; i < values.length; ++ i) {
			// Get the selector associated with this value, and either show or hide it
			var value_selector = trigger.attr('data-selector-' + values[i]);
			if (values[i] == value) {
				var selector_show = value_selector;
			} else if (zjQuery.inArray(value_selector, selector_hide) == -1) {
				selector_hide.push(value_selector);
			}
		}
		selector_hide = selector_hide.join(', ');
	} else {
		var selector_toggle = trigger.attr('data-selector');
		if (selector_toggle) {
			if (value != '0') {
				selector_show = selector_toggle;
			} else {
				selector_hide = selector_toggle;
			}
		} else {
			if (value != '0') {
				var selector_hide = trigger.attr('data-selector-hide');
				var selector_show = trigger.attr('data-selector-show');
			} else {
				var selector_hide = trigger.attr('data-selector-show');
				var selector_show = trigger.attr('data-selector-hide');
			}
		}
	}

	var parent_selector = trigger.attr('data-parent-selector');
	var parent_selector_optional = trigger.attr('data-parent-selector-optional');
	var hide;
	var show;

	if (selector_hide) {
		hide = matchSelector(selector_hide, parent_selector, parent_selector_optional);
		if (hide) {
			hide.hide();
			hide.find(':input').attr('disabled', 'disabled');
		}
	}

	if (selector_show) {
		show = matchSelector(selector_show, parent_selector, parent_selector_optional, true);
		if (show) {
			show.show();
			show.find(':input').not('.disabled').removeAttr('disabled');
		}
	}

	// There might be inputs that we want to disable inside of containers that we want to show.
	// But we have to make sure that anything marked with both the show *and* hide selectors
	// still gets shown...
	if (show && hide) {
		var rehide = show.find(selector_hide).not(selector_show).find(':input');
		if (rehide) {
			rehide.attr('disabled', 'disabled');
		}
	}
}

/**
 * Function to find all elements that match the given selector(s). It's not a
 * simple selector expression, because it needs to deal with the possibility that
 * we're trying to match not elements themselves, but some parent, as well as the
 * possibility that if the parent is not found, we might be allowed to fall back
 * to the element itself.
 *
 * Sometimes, for example the list of sports on the profile edit page, the thing
 * we're showing (sport-specific fields) might be inside of a bigger thing that's
 * entirely hidden (the player section of the profile). For these cases, we have
 * the exclude_in_hidden parameter, which will first match what it can, and then
 * remove anything that's inside of a hidden element. As with the rest of this
 * function, this needs to be done differently depending on whether we're looking
 * at elements or their parents.
 */
function matchSelector(selector, parent_selector, parent_selector_optional, exclude_in_hidden) {
	var matches = zjQuery();

	if (parent_selector) {
		if (parent_selector_optional) {
			zjQuery(selector).each(function () {
				var parent = this.closest(parent_selector);
				if (parent) {
					if (exclude_in_hidden) {
						matches = matches.add(parent).not(':hidden ' + parent_selector);
					} else {
						matches = matches.add(parent);
					}
				} else {
					if (exclude_in_hidden) {
						matches = matches.add(this).not(':hidden ' + parent_selector);
					} else {
						matches = matches.add(this);
					}
				}
			});
		} else {
			matches = zjQuery(selector).closest(parent_selector);
			if (exclude_in_hidden) {
				matches = matches.not(':hidden ' + parent_selector);
			}
		}
	} else {
		matches = zjQuery(selector);
		if (exclude_in_hidden) {
			// Find all the hidden parent elements that aren't in the list of what we're about to show
			var hidden_parents = matches.closest(':hidden', zjQuery('.zuluru').get(0)).not(matches);
			// and remove everything that's inside one of those parent elements
			matches = matches.not(hidden_parents.find(selector));
		}
	}
	return matches;
}

/**
 * Sometimes, things come from Ajax queries that need these things done on them.
 * This function will be called at first page load, and after any successful Ajax
 * call, so that everything is always correctly initialized.
 */
function initializeStatus() {
	/**
	 * Initialize toggled DOM elements based on initial settings of inputs
	 */
	zjQuery('.zuluru_toggle_input').each(function () {
		toggleInput(zjQuery(this));
	});

	/**
	 * Set initial state of any selectors
	 */
	selectorChanged(null);

	/**
	 * Initialize CKEditor on any applicable input fields
	 */
	if (typeof CKEDITOR !== 'undefined') {
		CKEDITOR.replaceAll(function (textarea, config) {
			textarea = zjQuery(textarea);
			if (CKEDITOR.instances[textarea.attr('id')] != undefined) {
				return false;
			} else if (textarea.hasClass('wysiwyg_advanced')) {
				config.toolbar = [
					{
						name: 'clipboard',
						items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
					},
					{name: 'editing', items: ['SpellChecker', 'Scayt']},
					{name: 'links', items: ['Link', 'Unlink', 'Anchor']},
					{name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak']},
					'/',
					{
						name: 'basicstyles',
						items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
					},
					{
						name: 'paragraph',
						items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
							'-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
					},
					'/',
					{name: 'styles', items: ['Format', 'Font', 'FontSize']},
					{name: 'colors', items: ['TextColor', 'BGColor']},
					{name: 'tools', items: ['Maximize', 'ShowBlocks', '-', 'About']},
					{name: 'document', items: ['Source']}
				];
			} else if (textarea.hasClass('wysiwyg_simple')) {
				config.toolbar = [
					{name: 'clipboard', items: ['Cut', 'Copy', 'PasteText', '-', 'Undo', 'Redo']},
					{
						name: 'basicstyles',
						items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
					},
					{name: 'paragraph', items: ['NumberedList', 'BulletedList']},
					{name: 'styles', items: ['Format']}
				];
			} else if (textarea.hasClass('wysiwyg_newsletter')) {
				config.toolbar = [
					{
						name: 'clipboard',
						items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
					},
					{name: 'editing', items: ['SpellChecker', 'Scayt']},
					{name: 'links', items: ['Link', 'Unlink', 'Anchor']},
					{name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak']},
					'/',
					{
						name: 'basicstyles',
						items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
					},
					{
						name: 'paragraph',
						items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
							'-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
					},
					'/',
					{name: 'styles', items: ['Format', 'Font', 'FontSize']},
					{name: 'colors', items: ['TextColor', 'BGColor']},
					{name: 'tools', items: ['Maximize', 'ShowBlocks', '-', 'About']},
					{name: 'document', items: ['Source']}
				];
			} else {
				return false;
			}
			config.resize_dir = 'both';
		});
	}

	/**
	 * Add date picker inputs to any date fields found. First, remove any that already
	 * exist, so that when this is called a second time because of an Ajax response,
	 * we don't end up with duplicated picker icons.
	 */
	zjQuery('input.datepicker').remove();
	zjQuery('.ui-datepicker-trigger').remove();
	zjQuery('div.date').each(function () {
		zjQuery(this).find('select').last().not('.disabled').after('<input class="datepicker" type="hidden"/>');
	});
	zjQuery('div.datetime').each(function () {
		zjQuery(this).find('select').last().not('.disabled').after('<input class="datepicker" type="hidden"/>');
	});

	/**
	 * Enable the date picker inputs and handle their events
	 */
	zjQuery('.datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		buttonImage: zuluru_img_path + 'calendar.png',
		buttonImageOnly: true,
		duration: '',
		showOn: 'button',
		onSelect: function (sel_date) {
			var newDate = sel_date.split('-');

			// If the date inputs have events on them to trigger Ajax requests, we only want to have one fire.
			// To accomplish this, we only fire the change event on each one if they *don't* have that ajax_input
			// class, and then at the end we will fire the first one. Assumption here is that if any of them have
			// that class, all of them will.

			var inputs = zjQuery(this).siblings('select');
			inputs.each(function () {
				var input = zjQuery(this);
				var name = input.attr('name');
				if (name.substring(name.length - 5, name.length) == '[day]' && input.val() != newDate[2]) {
					input.val(newDate[2]);
					if (!input.hasClass('zuluru_ajax_input')) {
						input.change();
					}
				}
				else if (name.substring(name.length - 7, name.length) == '[month]' && input.val() != newDate[1]) {
					input.val(newDate[1]);
					if (!input.hasClass('zuluru_ajax_input')) {
						input.change();
					}
				}
				else if (name.substring(name.length - 6, name.length) == '[year]' && input.val() != newDate[0]) {
					input.val(newDate[0]);
					if (!input.hasClass('zuluru_ajax_input')) {
						input.change();
					}
				}
			});

			if (inputs.length > 0 && zjQuery(inputs[0]).hasClass('zuluru_ajax_input')) {
				zjQuery(inputs[0]).change();
			}
		},
		beforeShow: function () {
			var year = '';
			var month = '';
			var day = '';
			var name = '';
			zjQuery(this).siblings('select').each(function () {
				name = zjQuery(this).attr('name');
				if (name.substring(name.length - 5, name.length) == '[day]') day = zjQuery(this).val();
				else if (name.substring(name.length - 7, name.length) == '[month]') month = zjQuery(this).val();
				else if (name.substring(name.length - 6, name.length) == '[year]') year = zjQuery(this).val();
			});
			zjQuery(this).val(year + '-' + month + '-' + day);
			return {};
		}
	});

	/**
	 * Initialize tooltip behaviour
	 */
	if (zuluru_mobile) {
		// Mobile devices don't have "hover" semantics, so instead
		// we'll add a bunch of separate icons to toggle tooltips.
		zjQuery('.trigger').before(zuluru_popup + ' ');
		zjQuery('.tooltip_toggle').uitooltip({
				items: '.tooltip_toggle',
				position: { my: 'center bottom', at: 'center top-5' },
				content: function (callback) {
					zjQuery(this).next().data('wait_for_tooltip', true);
					loadTooltip(zuluru_base, zjQuery(this).next(), callback);
				}
			})
			// Handle clicks to open/close tooltips
			.on('click', function () {
				var visible = zjQuery(this).next().data('tooltip_displayed');
				// Close all other visible tooltips
				zjQuery('.tooltip_toggle').each(function(){
					zjQuery(this).next().data('wait_for_tooltip', false);
					if (zjQuery(this).next().data('tooltip_displayed')) {
						zjQuery(this).next().data('tooltip_displayed', false);
						zjQuery(this).uitooltip('close');
					}
				});
				if (!visible) {
					zjQuery(this).uitooltip('open');
				}
				return false;
			});
		// Turn off the default hover mechanic
		zjQuery('.tooltip_toggle').uitooltip('disable');
	} else {
		// Add the standard tooltip handler
		zjQuery('.zuluru').on({
			mouseleave: function () {
				zjQuery(this).data('wait_for_tooltip', false);
			},
			focusout: function () {
				zjQuery(this).data('wait_for_tooltip', false);
			}
		},'.trigger');
		zjQuery('.zuluru').uitooltip({
			items: '.trigger',
			show: { delay: 500 },
			hide: { delay: 500 },
			// TODO: This may push off the side of the page, for example first name in search results
			position: { my: 'center bottom', at: 'center top-5' },
			content: function (callback) {
				zjQuery(this).data('wait_for_tooltip', true);
				loadTooltip(zuluru_base, zjQuery(this), callback);
			},
			// Adapted from http://stackoverflow.com/a/15014759
			close: function (event, ui) {
				ui.tooltip.hover(
					function() {
						zjQuery(this).stop(true).fadeTo(500, 1);
					},
					function() {
						zjQuery(this).fadeOut('500', function (){ zjQuery(this).remove(); })
					}
				);
			}
		});
	}
}

/**
 * Code to execute on the browser's Ready state. Takes the jQuery object as a parameter, so we can safely reference $ in here.
 */
zjQuery(function($) {
	// Add the X-CSRF-Token header to all Ajax POST requests, so that they don't get black-holed
	$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
		jqXHR.setRequestHeader('X-CSRF-Token', zuluru_csrf_token);
	});

	// This is from http://jsbin.com/icuguz/12/edit, referenced by http://bugs.jqueryui.com/ticket/4731, to keep the
	// focus from jumping to the first tabbable element in the dialog.
	// TODO: Somehow make this apply only to help dialogs, not ones that allow comments to be sent.
	zjQuery.ui.dialog.prototype._focusTabbable = function () {
		this.uiDialogTitlebarClose.focus();
	};

	/**
	 * Replicate HTML5 placeholder functionality on downlevel browsers. This can probably go away in 2025 or so...
	 * Adapted from http://www.cssnewbie.com/cross-browser-support-for-html5-placeholder-text-in-forms/
	 */
	$.support.placeholder = false;
	test = document.createElement('input');
	if('placeholder' in test) {
		$.support.placeholder = true;
	} else {
		var active = document.activeElement;

		$(':text').focus(function () {
			if ($(this).attr('placeholder') != '' && $(this).attr('placeholder') != undefined && $(this).val() == $(this).attr('placeholder')) {
				$(this).val('').removeClass('hasPlaceholder');
			}
		}).blur(function () {
			if ($(this).attr('placeholder') != '' && $(this).attr('placeholder') != undefined && ($(this).val() == '' || $(this).val() == $(this).attr('placeholder'))) {
				$(this).val($(this).attr('placeholder'));
				$(this).addClass('hasPlaceholder');
			}
		});

		$(':password').focus(function () {
			if ($(this).attr('placeholder') != '' && $(this).attr('placeholder') != undefined && $(this).val() == $(this).attr('placeholder')) {
				$(this).val('').removeClass('hasPlaceholder');
			}
		}).blur(function () {
			if ($(this).attr('placeholder') != '' && $(this).attr('placeholder') != undefined && ($(this).val() == '' || $(this).val() == $(this).attr('placeholder'))) {
				$(this).val($(this).attr('placeholder'));
				$(this).addClass('hasPlaceholder');
			}
		});

		$(':text').blur();
		$(':password').blur();
		$(active).focus();
		$('form').submit(function () {
			$(this).find('.hasPlaceholder').each(function() { $(this).val(''); });
		});
	}

	/**
	 * Add event handler for popup dialogs
	 */
	$('body').on('click', '.zuluru_popup_link', function() {
		// Get the key pieces of data from the DOM
		var trigger = $(this);
		var id = trigger.data('id');

		var buttons = {};
		buttons[zuluru_close] = function () {
			zjQuery('#' + id).dialog('close');
		};

		container = zjQuery('.zuluru.container').first();
		zjQuery('#' + id).dialog({
			buttons: buttons,
			modal: true,
			resizable: false,
			height: zjQuery(window).height() * 0.9,
			width: container.width() * 0.9
		});

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handler for help dialogs
	 */
	$('body').on('click', '.zuluru_help_link', function() {
		// Get the key pieces of data from the DOM
		var trigger = $(this);
		var id = trigger.data('id');
		var link = trigger.attr('href');

		var buttons = {};
		buttons[zuluru_open_help] = function () {
			zjQuery('#' + id).dialog('close');
			window.open(link, '_blank');
		};
		buttons[zuluru_close] = function () {
			zjQuery('#' + id).dialog('close');
		};

		container = zjQuery('.zuluru.container').first();
		zjQuery('#' + id).dialog({
			buttons: buttons,
			modal: true,
			resizable: false,
			height: zjQuery(window).height() * 0.9,
			width: container.width() * 0.9
		});

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handler for Ajax links
	 */
	$('body').on('click', '.zuluru_ajax_link', function() {
		// Get the key pieces of data from the DOM
		var trigger = $(this);

		// Links contain all critical data themselves; there's no container to worry about
		handleAjaxTrigger(trigger, trigger, null, 'replace', false, null);

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handler for Ajax form buttons
	 */
	$('body').on('click', '.zuluru_ajax_button', function() {
		// Get the key pieces of data from the DOM
		var trigger = $(this);

		handleAjaxTrigger(trigger, trigger, null, 'replace_content', true, null);

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handler for Ajax inputs
	 */
	$('body').on('change', '.zuluru_ajax_input', function() {
		// Get the key pieces of data from the DOM
		var trigger = $(this);

		handleAjaxTrigger(trigger, trigger, null, 'replace_content', true, trigger);

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handler for toggle links
	 */
	$('body').on('click', '.zuluru_toggle_link', function() {
		// Get the key pieces of data from the DOM
		var trigger = $(this);

		var selector_toggle = trigger.attr('data-selector');
		if (selector_toggle) {
			$(selector_toggle).toggle();
		}

		var selector_hide = trigger.attr('data-selector-hide');
		if (selector_hide) {
			$(selector_hide).hide();
		}

		var selector_show = trigger.attr('data-selector-show');
		if (selector_show) {
			$(selector_show).show();
		}

		// May need to change the text on the link
		var show_text = trigger.attr('data-show-text');
		if (show_text) {
			if (show_text == trigger.text()) {
				trigger.text(trigger.attr('data-hide-text'));
			} else {
				trigger.text(show_text);
			}
		}

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handler for Ajax pagination links. These links are only found
	 * within <div class="zuluru_pagination">, and then in a container (typically
	 * tr or nav) with class "paginator". This way, we don't break pages that
	 * don't support Ajax pagination, and we don't Ajaxify links within the
	 * search results.
	 */
	$('body').on('click', 'div.zuluru_pagination .paginator a', function () {
		var url = $(this).attr('href');
		if (!url) {
			return false;
		}
		var container = $(this).closest('.zuluru_pagination');
		zjQuery.ajax({
			type: 'get',
			url: url,
		})
			.done(function (response) {
				// Any time there is a message, we want to display it. It means *something* failed,
				// though the request may still have been considered a success. For example, we
				// updated the database, but failed to send an email about it.
				if (response._message) {
					// TODOTESTING: Remove this check once Ajax component bug is fixed
					//if (response._message[0].message != null) {
					alert(response._message[0].message);
					console.log(response._message[0].message);
					//}
				}

				// An error means that it failed, and we want to display that error message.
				// A redirect also means that it failed, but the message associated with that
				// will already be in the _message displayed above. In that case, we've done
				// everything we need to do. If neither of these are true, it was a success,
				// and we need to do something to the DOM as a result.
				if (response.error) {
					alert(response.error);
					console.log(response.error);
				} else if (!response._redirect) {
					container.html(response.content);
				}
			})
			.fail(function (e) {
				alert('An error occurred: ' + e.responseText);
				console.log(e);
			});

		return false;
	});

	/**
	 * Add event handler for toggle inputs
	 */
	$('body').on('change', '.zuluru_toggle_input', function() {
		// Just call the helper function
		toggleInput($(this));

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handler for select all
	 */
	$('body').on('click', '.zuluru_select_all', function() {
		// Get the key pieces of data from the DOM
		var trigger = $(this);
		var selector = trigger.attr('data-selector');

		// If the trigger is a checkbox, we get the status from it
		if (trigger.is(':checkbox')) {
			var check = trigger.prop('checked');
		} else {
			var select_text = trigger.attr('data-select-text');
			var unselect_text = trigger.attr('data-unselect-text');

			var label = trigger.text();
			var check = true;
			if (label == select_text) {
				trigger.text(unselect_text);
			} else {
				trigger.text(select_text);
				check = false;
			}
		}

		zjQuery(selector + ' :checkbox').prop('checked', check);

		// Don't bubble the event up any further, unless the trigger itself was a checkbox
		if (!trigger.is(':checkbox')) {
			return false;
		}
	});

	/**
	 * Add event handler for selector changes
	 */
	$('body').on('change', 'span.selector select', function() {
		// Just call the helper function
		selectorChanged(jQuery(this));

		// Don't bubble the event up any further
		return false;
	});
	$('body').on('change', 'span.selector div.radio input', function() {
		// Just call the helper function
		radioChanged(zjQuery(this));

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handler for in-place widget clicks. This handler simply shows (or hides) the available options.
	 * The options have their own click handler that takes care of the actual Ajax processing.
	 */
	$('body').on('click', '.zuluru-in-place-widget', function() {
		// Get the key pieces of data from the DOM
		var trigger = $(this);
		var widget = trigger.closest('.zuluru-in-place-widget');
		var type = widget.attr('data-type');

		if (closeInPlaceWidgets(trigger)) {
			// Clicked on the thing that's already open; just close it and return.
			return false;
		}

		// Mark the widget as being open, so we can find it again later
		widget.addClass('zuluru-in-place-widget-open');

		// Find the new position, relative to the clicked link
		var offset = widget.offset();
		offset.top += 16;
		offset.left += 10;

		var div = $('#zuluru_in_place_widget_' + type +'_options');

		 // Hide any invalid options
		var valid_options = widget.attr('data-valid-options');
		if (valid_options) {
			div.children('div').each(function () {
				var value = $(this).attr('data-value');
				if (valid_options.indexOf('#' + value + '#') == -1) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});
		}

		// Show the options div and move it. Seems it has to be in that order. :-(
		div.css('display', '');
		div.offset(offset);
		// IE won't show it correctly on the first click unless we do this twice!
		div.offset(offset);

		var now = new Date();
		$('body').on('click.zuluruWidget', null, now.getTime(), function (event) {
			var now = new Date();
			if (now.getTime() > event.data + 25) {
				closeInPlaceWidgets();
			}
		});
		$('body').on('keyup.zuluruWidget', null, function(event) {
			if (event.keyCode == 27) {
				closeInPlaceWidgets();
			}
		});

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handler for in-place widget option clicks. This just does widget-specific DOM handling,
	 * and passes the results on to the generic event handler.
	 */
	$('body').on('click', '.zuluru-in-place-widget-option', function() {
		// If a widget option got clicked, there should be exactly one open widget.
		// We need to get the reference to it before we close it!
		var widget = $($('.zuluru-in-place-widget-open')[0]);

		closeInPlaceWidgets();

		// Get the key pieces of data from the DOM
		var trigger = $(this);
		var container = trigger.closest('.zuluru-in-place-widget-options');

		handleAjaxTrigger(trigger, container, widget, 'replace', false, null);

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Add event handlers for dynamic-load accordion panels.
	 */
	$('.dynamic-load').on('shown.bs.collapse', function (e) {
		var trigger = $(e.target);
		trigger.closest('.panel').find('.refresh').first().show();
		var container = trigger.children('.panel-body').first();
		if (container.html() != '') {
			return;
		}
		handleAjaxTrigger(trigger, trigger, null, 'replace_content', false, null);
	});
	$('.dynamic-load').on('hidden.bs.collapse', function (e) {
		var trigger = $(e.target);
		trigger.closest('.panel').find('.refresh').first().hide();
	});

	/**
	 * Add refresh event handlers for accordion panels.
	 */
	$('body').on('click', '.panel-heading .refresh', function() {
		var trigger = $(this);
		var container = trigger.children('.panel-body').first();
		handleAjaxTrigger(trigger, trigger, null, 'replace_content', false, null);

		// Don't bubble the event up any further
		return false;
	});

	/**
	 * Scroll selected accordion headings to the top of the page, if they are off the top.
	 * We have some long panels, and this increases usability.
	 * Adapted from http://stackoverflow.com/questions/21958933/bootstrap-accordion-scroll-to-top-of-active-panel-heading
	 */
	$('#accordion').on('shown.bs.collapse', function (e) {
		var offset = $(e.target).prev('.panel-heading');
		if (offset && ($(offset).offset().top < $(window).scrollTop())) {
			$('html,body').animate({
				scrollTop: $(offset).offset().top
			}, 500);
		}
	});

	/**
	 * Create dialogs where required. When there are autocomplete inputs inside a dialog,
	 * this must be done first, or else the <ul> for autocomplete results is outside the
	 * dialog and ends up underneath it. Here, we just make the thing into a dialog with
	 * no options, and trust that the options will be added later.
	 */
	$('.zuluru_dialog').dialog({
		autoOpen: false
	});

	/**
	 * Add autocomplete handling where required.
	 */
	$('.zuluru_autocomplete').autocomplete({
		source: function(request, callback) {
			var trigger = $(this.element);
			var url = trigger.attr('data-url');
			if (!url) {
				alert('URL required, but not found.');
				return false;
			}

			$.ajax({
				dataType: 'json',
				type: 'GET',
				url: url,
				data: {
					term: request.term,
				}
			})
				.done(function (response) {
					// Any time there is a message, we want to display it. It means *something* failed,
					// though the request may still have been considered a success. For example, we
					// updated the database, but failed to send an email about it.
					if (response._message) {
						// TODOTESTING: Remove this check once Ajax component bug is fixed
						//if (response._message[0].message != null) {
							alert(response._message[0].message);
						//}
					}

					// An error means that it failed, and we want to display that error message.
					// A redirect also means that it failed, but the message associated with that
					// will already be in the _message displayed above. In that case, we've done
					// everything we need to do. If neither of these are true, it was a success,
					// and we need to parse the results and send them to the callback function.
					if (response.error) {
						alert(response.error);
						console.log(response.error);
					} else if (!response._redirect) {
						callback($.parseJSON(response.content));
					}
				})
				.fail(function (e) {
					alert('An error occurred: ' + e.responseText);
					console.log(e);
				});
		},
		select: function (event, ui) {
			var trigger = $(this);

			// If the autocomplete is inside a dialog, close it.
			var dialog = trigger.closest('div.ui-dialog-content');
			if (dialog != undefined) {
				dialog.dialog('close');
			}

			// Check for how we might handle the selected item
			var disposition = trigger.attr('data-disposition') || 'replace';
			switch (disposition) {
				case 'replace':
					// We don't have to do anything here, as this is the default behaviour of the widget.
					break;

				case 'ajax_add_row':
					var url = trigger.attr('data-add-url');
					if (!url) {
						alert('URL required, but not found.');
						return false;
					}
					// Replace any placeholders with the corresponding values
					url = url.replace('__id__', ui.item.value);
					url = url.replace('__label__', ui.item.label);

					var selector = trigger.attr('data-add-selector');
					if (!selector) {
						alert('Selector required, but not found.');
						return false;
					}

					$.ajax({
						type: 'GET',
						url: url,
					})
						.done(function (response) {
							// Any time there is a message, we want to display it. It means *something* failed,
							// though the request may still have been considered a success. For example, we
							// updated the database, but failed to send an email about it.
							if (response._message) {
								// TODOTESTING: Remove this check once Ajax component bug is fixed
								//if (response._message[0].message != null) {
									alert(response._message[0].message);
								//}
							}

							// An error means that it failed, and we want to display that error message.
							// A redirect also means that it failed, but the message associated with that
							// will already be in the _message displayed above. In that case, we've done
							// everything we need to do. If neither of these are true, it was a success,
							// and we need to do something to the DOM as a result.
							if (response.error) {
								alert(response.error);
								console.log(response.error);
							} else if (!response._redirect) {
								$(selector + ' > tbody:first').append(response.content);
								tableReorder($(selector));
							}
						});
					break;
			}
		},
		 minLength: 2
	});

	/**
	 * Connect the jQuery sortable behaviour to any applicable tables.
	 */
	$('.sortable > tbody').sortable({
		axis: 'y',
		containment: '.sortable',
		cursor: 'move',
		handle: '.handle',
		items: '> tr',
		// Set widths of table cells during drag, to keep things from collapsing.
		// Adapted from http://stackoverflow.com/questions/1307705
		helper: function (event, ui) {
			var table = $(ui).closest('.sortable');
			$('td, th', table).each(function () {
				var cell = $(this);
				cell.width(cell.width());
			});
			return ui;
		},
		// Clear the widths set above, so that cells can resize again, and update
		// the sort fields.
		update: function (event, ui) {
			var table = $(ui.item).closest('.sortable');
			$('td, th', table).each(function () {
				var cell = $(this);
				cell.css('width','');
			});
			tableReorder(table);
		}
	});
	// TODOBOOTSTRAP: Add a handle icon :before any '.sortable .handle' elements, so people know it's draggable

	/**
	 * Last thing to do is actually call that initialize function.
	 */
	initializeStatus();
});

/**
 * Stat-tracking functions
 */

function statsInputChanged(input) {
	var total = 0;
	var table = input.closest('table');
	var id = input.data('stat-id');
	var th = table.find('th.stat_' + id);

	var handler = th.data('handler');
	if (typeof(handler) == 'undefined' || typeof(window[handler]) != 'function') {
		handler = false;
	}
	table.find('input.stat_' + id).each(function(){
		var val = parseFloat(zjQuery(this).val());
		if (!isNaN(val)) {
			if (handler) {
				total = window[handler](total, val);
			} else {
				total += val;
			}
		}
	});
//	if (!handler) {
//		total = Math.round(total*10)/10;
//	}

	var formatter = th.data('formatter');
	if (typeof(formatter) != 'undefined' && typeof(window[formatter]) == 'function') {
		total = window[formatter](total);
	}
	th.html(total);
}

// Handler for summing minutes played
function minutes_sum(total, value)
{
	var minutes = Math.floor(total) + Math.floor(value);
	var seconds = Math.round((total + value - minutes) * 100);
	minutes += Math.floor(seconds / 60);
	seconds %= 60;
	minutes += seconds / 100;
	return minutes;
}

// Handler for formatting minutes played
function minutes_format(total)
{
	var minutes = Math.floor(total);
	var seconds = Math.round((total - minutes) * 100);
	var ret = minutes.toString() + ':';
	if (seconds < 10) {
		ret += '0';
	}
	ret += seconds.toString();
	return ret;
}

// Handler for stats that don't logically sum (games played, percentages, etc.)
function null_sum() {
	return '';
}

/**
 * Spirit-related functions
 */

function suggestSpirit(index) {
	var sotg = 0;
	zjQuery('input:checked[id^=SpiritEntry' + index + 'Q]').each(function() {
		sotg += parseInt(zjQuery(this).val());
	});
	zjQuery('#SpiritEntry' + index + 'EnteredSotg').val(sotg);
}

/**
 * Functions for dealing with specific page requirements.
 */

function addQuestion() {
	zjQuery('#AddQuestion').val('');
	zjQuery('#AddQuestionDiv').dialog('open');
	zjQuery('#AddQuestion').focus();
	return false;
}
