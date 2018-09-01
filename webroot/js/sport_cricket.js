//
// Cricket-specific functions
//

function cricketMaxLength() { return 180; }
function cricketDefaultLength() { return 150; }
function cricketMinLength() { return 100; }
function cricketMaxWidth() { return cricketMaxLength(); }
function cricketDefaultWidth() { return cricketDefaultLength(); }
function cricketMinWidth() { return 65; }

function cricketLayoutText(id)
{
	if (fields[id].length == 0) {
		return null;
	}
	return '<p>Width: ' + fields[id].width * 3 + ' feet' +
			'<br>Lengh: ' + fields[id].length * 3 + ' feet';
}

function cricketOutlinePositions(id)
{
	var position = fields[id].marker.getPosition();
	var radius = Math.min(fields[id].width, fields[id].length) / 2;
	var focus = fields[id].length / 2 - radius;

	var bb = new Array;
	var side = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);
	if (focus > 0) {
		bb.push(makePosition(side, focus, 90 - fields[id].angle));
		bb.push(makePosition(side, focus, 270 - fields[id].angle));
	}
	bb = bb.concat(makeArc(makePosition(position, focus, 270 - fields[id].angle), 180 - fields[id].angle, 360 - fields[id].angle, radius));
	if (focus > 0) {
		bb.push(makePosition(bb[bb.length-1], focus * 2, 90 - fields[id].angle));
	}
	bb = bb.concat(makeArc(makePosition(position, focus, 90 - fields[id].angle), 0 - fields[id].angle, 180 - fields[id].angle, radius));
	return bb;
}

function cricketInlinePositions(id)
{
	var position = fields[id].marker.getPosition();

	var bb = new Array;

	var side = makePosition(position, 30, 180 - fields[id].angle);
	bb[0] = new Array;
	bb[0].push(makePosition(side, 11, 90 - fields[id].angle));
	bb[0].push(makePosition(side, 11, 270 - fields[id].angle));
	bb[0] = bb[0].concat(makeArc(makePosition(position, 11, 270 - fields[id].angle), 180 - fields[id].angle, 360 - fields[id].angle, 30));
	bb[0].push(makePosition(bb[0][bb[0].length-1], 22, 90 - fields[id].angle));
	bb[0] = bb[0].concat(makeArc(makePosition(position, 11, 90 - fields[id].angle), 0 - fields[id].angle, 180 - fields[id].angle, 30));

	var side = makePosition(position, 1.67, 180 - fields[id].angle);
	bb[1] = new Array;
	bb[1][0] = makePosition(side, 12.33, 90 - fields[id].angle);
	bb[1][1] = makePosition(bb[1][0], 24.66, 270 - fields[id].angle);
	bb[1][2] = makePosition(bb[1][1], 3.34, 0 - fields[id].angle);
	bb[1][3] = makePosition(bb[1][2], 24.66, 90 - fields[id].angle);
	bb[1][4] = bb[1][0];

	return bb;
}

function cricketUpdateForm()
{
	jQuery('#cricket_fields .show_angle').html(fields[current].angle);
	jQuery('#cricket_fields .show_width').html(fields[current].width);
	jQuery('#cricket_fields .show_length').html(fields[current].length);
}

function cricketSaveField()
{
	if (current != 0) {
		fields[current].angle = parseInt(jQuery('#cricket_fields .show_angle').html());
		fields[current].width = parseInt(jQuery('#cricket_fields .show_width').html());
		fields[current].length = parseInt(jQuery('#cricket_fields .show_length').html());
	}
}
