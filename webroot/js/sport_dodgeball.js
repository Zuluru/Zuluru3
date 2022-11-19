//
// Dodgeball-specific functions
//

function dodgeballMaxLength() { return 20; }
function dodgeballDefaultLength() { return dodgeballMaxLength(); }
function dodgeballMinLength() { return 12; }
function dodgeballMaxWidth() { return 15; }
function dodgeballDefaultWidth() { return dodgeballMaxWidth(); }
function dodgeballMinWidth() { return 9; }

function dodgeballLayoutText(id)
{
	return null;
}

function dodgeballOutlinePositions(id)
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

function dodgeballInlinePositions(id)
{
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	bb[0] = new Array;
	bb[0][0] = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);
	bb[0][1] = makePosition(bb[0][0], fields[id].width, 0 - fields[id].angle);
	return bb;
}

function dodgeballUpdateForm()
{
	zjQuery('#dodgeball_fields .show_angle').html(fields[current].angle);
	zjQuery('#dodgeball_fields .show_width').html(fields[current].width);
	zjQuery('#dodgeball_fields .show_length').html(fields[current].length);
}

function dodgeballSaveField()
{
	if (current != 0) {
		fields[current].angle = parseInt(zjQuery('#dodgeball_fields .show_angle').html());
		fields[current].width = parseInt(zjQuery('#dodgeball_fields .show_width').html());
		fields[current].length = parseInt(zjQuery('#dodgeball_fields .show_length').html());
	}
}
