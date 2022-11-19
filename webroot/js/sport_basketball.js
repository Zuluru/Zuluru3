//
// Basketball-specific functions
//

function basketballMaxLength() { return 31; }
function basketballDefaultLength() { return basketballMaxLength(); }
function basketballMinLength() { return 16; }
function basketballMaxWidth() { return 17; }
function basketballDefaultWidth() { return basketballMaxWidth(); }
function basketballMinWidth() { return 9; }

function basketballKeyLength(length)
{
	return Math.min(length * 0.3, 19 / 3);
}

function basketballKeyWidth(width)
{
	return Math.min(width * 0.3, 4);
}

function basketballThreePointRadius(length, width)
{
	return basketballKeyLength(length) + basketballKeyWidth(width) / 2 - 63 / 36;
}

function basketballLayoutText(id)
{
	return null;
}

function basketballOutlinePositions(id)
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

function basketballInlinePositions(id)
{
	var key_length = basketballKeyLength(fields[id].length);
	var key_width = basketballKeyWidth(fields[id].width);
	var three_point_radius = basketballThreePointRadius(fields[id].length, fields[id].width);
	var position = fields[id].marker.getPosition();
	var baseline1 = makePosition(position, fields[id].length / 2, 90 - fields[id].angle);
	var baseline2 = makePosition(position, fields[id].length / 2, 270 - fields[id].angle);

	var bb = new Array;

	// Centre line
	bb[0] = new Array;
	bb[0][0] = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);
	bb[0][1] = makePosition(bb[0][0], fields[id].width, 0 - fields[id].angle);

	// One key...
	bb[1] = new Array;
	bb[1][0] = makePosition(baseline1, key_width / 2, 180 - fields[id].angle);
	bb[1][1] = makePosition(bb[1][0], key_length, 270 - fields[id].angle);
	bb[1][2] = makePosition(bb[1][1], key_width, 0 - fields[id].angle);
	bb[1][3] = makePosition(bb[1][2], key_length, 90 - fields[id].angle);

	// ...and that key's free throw circle
	bb[2] = makeArc(makePosition(baseline1, key_length, 270 - fields[id].angle), 360 - fields[id].angle, 180 - fields[id].angle, key_width / 2);

	// Other key...
	bb[3] = new Array;
	bb[3][0] = makePosition(baseline2, key_width / 2, 180 - fields[id].angle);
	bb[3][1] = makePosition(bb[3][0], key_length, 90 - fields[id].angle);
	bb[3][2] = makePosition(bb[3][1], key_width, 0 - fields[id].angle);
	bb[3][3] = makePosition(bb[3][2], key_length, 270 - fields[id].angle);

	// ...and that key's free throw circle
	bb[4] = makeArc(makePosition(baseline2, key_length, 90 - fields[id].angle), 180 - fields[id].angle, 0 - fields[id].angle, key_width / 2);

	if (three_point_radius < fields[id].width / 2) {
		bb[5] = makeArc(makePosition(baseline1, 63 / 36, 270 - fields[id].angle), 360 - fields[id].angle, 180 - fields[id].angle, three_point_radius);
		bb[5].unshift(makePosition(baseline1, three_point_radius, 0 - fields[id].angle));
		bb[5].push(makePosition(baseline1, three_point_radius, 180 - fields[id].angle));

		bb[6] = makeArc(makePosition(baseline2, 63 / 36, 90 - fields[id].angle), 180 - fields[id].angle, 0 - fields[id].angle, three_point_radius);
		bb[6].unshift(makePosition(baseline2, three_point_radius, 180 - fields[id].angle));
		bb[6].push(makePosition(baseline2, three_point_radius, 0 - fields[id].angle));
	} else {
		bb[5] = bb[6] = new Array();
	}

	return bb;
}

function basketballUpdateForm()
{
	zjQuery('#basketball_fields .show_angle').html(fields[current].angle);
	zjQuery('#basketball_fields .show_width').html(fields[current].width);
	zjQuery('#basketball_fields .show_length').html(fields[current].length);
}

function basketballSaveField()
{
	if (current != 0) {
		fields[current].angle = parseInt(zjQuery('#basketball_fields .show_angle').html());
		fields[current].width = parseInt(zjQuery('#basketball_fields .show_width').html());
		fields[current].length = parseInt(zjQuery('#basketball_fields .show_length').html());
	}
}
