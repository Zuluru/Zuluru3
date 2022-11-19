//
// Rugby-specific functions
//

function rugbyMaxLength() { return 134; }
function rugbyDefaultLength() { return rugbyMaxLength(); }
function rugbyMinLength() { return 60; }
function rugbyMaxWidth() { return 74; }
function rugbyDefaultWidth() { return rugbyMaxWidth(); }
function rugbyMinWidth() { return 35; }

function rugbyFieldLength(length)
{
	return length - rugbyInGoalLength(length) * 2;
}

function rugbyInGoalLength(length)
{
	if (length >= 122) {
		return Math.floor((length - 110) / 2);
	}
	return 6;
}

function rugbyLayoutText(id)
{
	if (fields[id].length == 0) {
		return null;
	}
	return '<p>Field width: ' + fields[id].width + ' yards' +
			'<br>Field of Play length: ' + rugbyFieldLength(fields[id].length) + ' yards' +
			'<br>In Goal Area length: ' + rugbyInGoalLength(fields[id].length) + ' yards';
}

function rugbyOutlinePositions(id)
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

function rugbyInlinePositions(id)
{
	var length = rugbyFieldLength(fields[id].length);
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

function rugbyUpdateForm()
{
	zjQuery('#rugby_fields .show_angle').html(fields[current].angle);
	zjQuery('#rugby_fields .show_width').html(fields[current].width);
	zjQuery('#rugby_fields .show_length').html(fields[current].length);
	zjQuery('#rugby_fields .show_field').html(rugbyFieldLength(fields[current].length));
	zjQuery('#rugby_fields .show_ingoal').html(rugbyInGoalLength(fields[current].length));
}

function rugbySaveField()
{
	if (current != 0) {
		fields[current].angle = parseInt(zjQuery('#rugby_fields .show_angle').html());
		fields[current].width = parseInt(zjQuery('#rugby_fields .show_width').html());
		fields[current].length = parseInt(zjQuery('#rugby_fields .show_length').html());
	}
}
