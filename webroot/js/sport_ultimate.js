//
// Ultimate-specific functions
//

function ultimateMaxLength() { return 110; }
function ultimateDefaultLength() { return ultimateMaxLength(); }
function ultimateMinLength() { return 30; }
function ultimateMaxWidth() { return 40; }
function ultimateDefaultWidth() { return ultimateMaxWidth(); }
function ultimateMinWidth() { return 20; }

function ultimateFieldLength(length)
{
	return length - ultimateEndzoneLength(length) * 2;
}

function ultimateEndzoneLength(length)
{
	return Math.floor(length * 40 / 110 / 2);
}

function ultimateLayoutText(id)
{
	if (fields[id].length == 0) {
		return null;
	}
	return '<p>Field width: ' + fields[id].width + ' yards' +
			'<br>Playing Field Proper length: ' + ultimateFieldLength(fields[id].length) + ' yards' +
			'<br>End Zone length: ' + ultimateEndzoneLength(fields[id].length) + ' yards';
}

function ultimateOutlinePositions(id)
{
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	var side = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);
	bb[0] = makePosition(side, fields[id].length / 2, 270 - fields[id].angle);
	bb[1] = makePosition(bb[0], fields[id].width, 0 - fields[id].angle);
	bb[2] = makePosition(bb[1], fields[id].length, 90 - fields[id].angle);
	bb[3] = makePosition(bb[2], fields[id].width, 180 - fields[id].angle);
	return bb;
}

function ultimateInlinePositions(id)
{
	var length = ultimateFieldLength(fields[id].length);
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	var side = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);

	bb[0] = new Array;
	bb[0][0] = makePosition(side, length / 2, 270 - fields[id].angle);
	bb[0][1] = makePosition(bb[0][0], fields[id].width, 0 - fields[id].angle);

	bb[1] = new Array;
	bb[1][0] = makePosition(side, length / 2, 90 - fields[id].angle);
	bb[1][1] = makePosition(bb[1][0], fields[id].width, 0 - fields[id].angle);

	return bb;
}

function ultimateUpdateForm()
{
	jQuery('#ultimate_fields .show_angle').html(fields[current].angle);
	jQuery('#ultimate_fields .show_width').html(fields[current].width);
	jQuery('#ultimate_fields .show_length').html(fields[current].length);
	jQuery('#ultimate_fields .show_field').html(ultimateFieldLength(fields[current].length));
	jQuery('#ultimate_fields .show_endzone').html(ultimateEndzoneLength(fields[current].length));
}

function ultimateSaveField()
{
	if (current != 0) {
		fields[current].angle = parseInt(jQuery('#ultimate_fields .show_angle').html());
		fields[current].width = parseInt(jQuery('#ultimate_fields .show_width').html());
		fields[current].length = parseInt(jQuery('#ultimate_fields .show_length').html());
	}
}
