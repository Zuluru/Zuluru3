//
// Volleyball-specific functions
//

function volleyballMaxLength() { return 20; }
function volleyballDefaultLength() { return volleyballMaxLength(); }
function volleyballMinLength() { return 14; }
function volleyballMaxWidth() { return 10; }
function volleyballDefaultWidth() { return volleyballMaxWidth(); }
function volleyballMinWidth() { return 7; }

function volleyballLayoutText(id)
{
	return null;
}

function volleyballOutlinePositions(id)
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

function volleyballInlinePositions(id)
{
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	bb[0] = new Array;
	bb[0][0] = makePosition(position, fields[id].width / 2, 180 - fields[id].angle);
	bb[0][1] = makePosition(bb[0][0], fields[id].width, 0 - fields[id].angle);
	return bb;
}

function volleyballUpdateForm()
{
	jQuery('#volleyball_fields .show_angle').html(fields[current].angle);
	jQuery('#volleyball_fields .show_width').html(fields[current].width);
	jQuery('#volleyball_fields .show_length').html(fields[current].length);
}

function volleyballSaveField()
{
	if (current != 0) {
		fields[current].angle = parseInt(jQuery('#volleyball_fields .show_angle').html());
		fields[current].width = parseInt(jQuery('#volleyball_fields .show_width').html());
		fields[current].length = parseInt(jQuery('#volleyball_fields .show_length').html());
	}
}
