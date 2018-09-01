//
// Football-specific functions
//
// This uses CFL dimensions; need a clean way to differentiate from NFL
//

function footballMaxLength() { return 150; }
function footballDefaultLength() { return footballMaxLength(); }
function footballMinLength() { return 50; }
function footballMaxWidth() { return 65; }
function footballDefaultWidth() { return footballMaxWidth(); }
function footballMinWidth() { return 25; }

function footballFieldLength(length)
{
	return length - footballEndzoneLength(length) * 2;
}

function footballEndzoneLength(length)
{
	return Math.floor(length * 40 / 150 / 2);
}

function footballLayoutText(id)
{
	if (fields[id].length == 0) {
		return null;
	}
	return '<p>Field width: ' + fields[id].width + ' yards' +
			'<br>Playing Field length: ' + footballFieldLength(fields[id].length) + ' yards' +
			'<br>End Zone length: ' + footballEndzoneLength(fields[id].length) + ' yards';
}

function footballOutlinePositions(id)
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

function footballInlinePositions(id)
{
	var length = footballFieldLength(fields[id].length);
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	var side = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);

	bb[0] = new Array;
	bb[0][0] = makePosition(side, length / 2, 270 - fields[id].angle);
	bb[0][1] = makePosition(bb[0][0], fields[id].width, 0 - fields[id].angle);

	bb[1] = new Array;
	bb[1][0] = side;
	bb[1][1] = makePosition(bb[1][0], fields[id].width, 0 - fields[id].angle);

	bb[2] = new Array;
	bb[2][0] = makePosition(side, length / 2, 90 - fields[id].angle);
	bb[2][1] = makePosition(bb[2][0], fields[id].width, 0 - fields[id].angle);

	return bb;
}

function footballUpdateForm()
{
	jQuery('#football_fields .show_angle').html(fields[current].angle);
	jQuery('#football_fields .show_width').html(fields[current].width);
	jQuery('#football_fields .show_length').html(fields[current].length);
	jQuery('#football_fields .show_field').html(footballFieldLength(fields[current].length));
	jQuery('#football_fields .show_endzone').html(footballEndzoneLength(fields[current].length));
}

function footballSaveField()
{
	if (current != 0) {
		fields[current].angle = parseInt(jQuery('#football_fields .show_angle').html());
		fields[current].width = parseInt(jQuery('#football_fields .show_width').html());
		fields[current].length = parseInt(jQuery('#football_fields .show_length').html());
	}
}
