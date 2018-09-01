//
// Baseball-specific functions
//

function baseballMaxLength() { return 145; }
function baseballDefaultLength() { return 120; }
function baseballMinLength() { return 80; }
function baseballMaxWidth() { return 30; }
function baseballDefaultWidth() { return baseballMaxWidth(); }
function baseballMinWidth() { return 15; }

function baseballLayoutText(id)
{
	if (fields[id].length == 0) {
		return null;
	}
	return '<p>Base paths: ' + fields[id].width * 3 + ' feet' +
			'<br>Outfield: ' + fields[id].length * 3 + ' feet';
}

function baseballMound(width)
{
	return width * 60.5 / 90;
}

function baseballOutlinePositions(id)
{
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	var home = makePosition(position, baseballMound(fields[id].width), 270 - fields[id].angle);
	bb[0] = home;
	bb[1] = makePosition(bb[0], fields[id].length, 45 - fields[id].angle);
	bb = bb.concat(makeArc(home, 45 - fields[id].angle, 135 - fields[id].angle, fields[id].length));
	bb.push(home);
	return bb;
}

function baseballInlinePositions(id)
{
	var position = fields[id].marker.getPosition();

	var bb = new Array;
	var home = makePosition(position, baseballMound(fields[id].width), 270 - fields[id].angle);

	bb[0] = new Array;
	bb[0][0] = makePosition(home, fields[id].width, 45 - fields[id].angle);
	bb[0][1] = makePosition(bb[0][0], fields[id].width, 135 - fields[id].angle);
	bb[0][2] = makePosition(bb[0][1], fields[id].width, 225 - fields[id].angle);

	bb[1] = makeArc(makePosition(home, baseballMound(fields[id].width), 90 - fields[id].angle), 19 - fields[id].angle, 161 - fields[id].angle, fields[id].width * 95 / 90);

	return bb;
}

function baseballUpdateForm()
{
	jQuery('#baseball_fields .show_angle').html(fields[current].angle);
	jQuery('#baseball_fields .show_width').html(fields[current].width);
	jQuery('#baseball_fields .show_length').html(fields[current].length);
}

function baseballSaveField()
{
	if (current != 0) {
		fields[current].angle = parseInt(jQuery('#baseball_fields .show_angle').html());
		fields[current].width = parseInt(jQuery('#baseball_fields .show_width').html());
		fields[current].length = parseInt(jQuery('#baseball_fields .show_length').html());
	}
}

// Handler for summing innings pitched on the stats entry page
function innings_sum(total, value)
{
	var innings = Math.floor(total) + Math.floor(value);
	var outs = Math.round((total + value - innings) * 10);
	innings += Math.floor(outs / 3);
	outs %= 3;
	innings += outs / 10;
	return innings;
}