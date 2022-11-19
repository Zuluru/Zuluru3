//
// Functions and variables for field editing
//

var leaguelat = 0.0;
var leaguelng = 0.0;
var lastParkingId = 0;
var lastEntranceId = 0;

function initializeEdit(id) {
	initialize();

	var field = fields[id];

	zjQuery('.sport_specific_fields').css('display', 'none');
	zjQuery('#' + field.sport + '_fields').css('display', '');

	if (field.latitude != undefined) {
		position = new google.maps.LatLng(field.latitude, field.longitude);
		map.setCenter(position);
		map.setZoom(field.zoom);
		map.setMapTypeId(google.maps.MapTypeId.HYBRID);

		drawFields();
		selectField(id);
	} else {
		var gc = new google.maps.Geocoder();
		gc.geocode({
			'address':full_address,
			'location':new google.maps.LatLng(leaguelat, leaguelng)
		}, function(result, status) { centerByAddress(id, result, status); });
	}

	for (lastParkingId in parking) {
		parking[lastParkingId].marker = showParking (parking[lastParkingId].position);
		parking[lastParkingId].marker.setOptions({'draggable':true});
		deleteParkingOnClick(parking[lastParkingId].marker, lastParkingId);
	}

	for (lastEntranceId in entrances) {
		entrances[lastEntranceId].marker = showEntrance (entrances[lastEntranceId].position);
		entrances[lastEntranceId].marker.setOptions({'draggable':true});
		deleteEntranceOnClick(entrances[lastEntranceId].marker, lastEntranceId);
	}
}

function deleteParkingOnClick(marker, id) {
	google.maps.event.addListener(marker, 'click', function() {
		deleteParking(id);
	});
}

function deleteEntranceOnClick(marker, id) {
	google.maps.event.addListener(marker, 'click', function() {
		deleteEntrance(id);
	});
}

function drawFields() {
	for (var id in fields) {
		drawField(id);
		// This needs to go in a separate function for closures to work correctly
		selectOnClick(fields[id].marker, id);
	}
}

function selectOnClick(marker, id) {
	google.maps.event.addListener(marker, 'click', function() {
		selectField(id);
	});
}

var drag_listener = null;

function selectField(id) {
	if (current != 0) {
		// Remove selection colouring and listener from the old field
		fields[current].field_outline.setOptions({'fillColor':'#ff6060'});
		fields[current].marker.setOptions({'draggable':false});
		google.maps.event.removeListener(drag_listener);

		// Save any layout changes into the form
		window[fields[current].sport + 'SaveField']();
	}

	current = id;

	zjQuery('.sport_specific_fields').css('display', 'none');
	zjQuery('#' + fields[id].sport + '_fields').css('display', '');

	// Update the display with data about the selected field
	zjQuery('#show_num').html(fields[id].num + ' (id ' + id + ')');
	window[fields[id].sport + 'UpdateForm']();

	// Add selection colouring and listener to the new field
	fields[id].field_outline.setOptions({'fillColor':'#60ff60'});
	fields[id].marker.setOptions({'draggable':true});
	drag_listener = google.maps.event.addListener(fields[id].marker, 'drag', redraw);
}

// Array for decoding the failure codes
var reasons=[];
reasons[google.maps.GeocoderStatus.ERROR] = "There was a problem contacting the Google servers.";
reasons[google.maps.GeocoderStatus.INVALID_REQUEST] = "This GeocoderRequest was invalid.";
reasons[google.maps.GeocoderStatus.OVER_QUERY_LIMIT] = "The webpage has gone over the requests limit in too short a period of time.";
reasons[google.maps.GeocoderStatus.REQUEST_DENIED] = "The webpage is not allowed to use the geocoder.";
reasons[google.maps.GeocoderStatus.UNKNOWN_ERROR] = "A geocoding request could not be processed due to a server error. The request may succeed if you try again.";
reasons[google.maps.GeocoderStatus.ZERO_RESULTS] = "No result was found for this GeocoderRequest.";

function centerByAddress(id, result, status) {
	var position;
	if (status == google.maps.GeocoderStatus.OK) {
		position = result[0].geometry.location;
	} else {
		position = new google.maps.LatLng(leaguelat, leaguelng);
		alert('Field has no lat/long yet, and the street address was not found by Google.\nUsing default lat/long from "global settings" as a fallback.\nReason given by Google was "' + reasons[status] + '"');
	}

	fields[id].latitude = position.lat();
	fields[id].longitude = position.lng();
	fields[id].length = window[fields[id].sport + 'DefaultLength']();
	fields[id].width = window[fields[id].sport + 'DefaultWidth']();
	fields[id].angle = 0;

	map.setCenter(position);
	map.setZoom(17);
	map.setMapTypeId(google.maps.MapTypeId.HYBRID);

	drawFields();
	selectField(id);
}

function redraw() {
	fields[current].field_outline.setPath(window[fields[current].sport + 'OutlinePositions'](current));
	var inlines = window[fields[current].sport + 'InlinePositions'](current);
	for (var i = 0; i < inlines.length; i++) {
		fields[current].field_inlines[i].setPath(inlines[i]);
	}
}

function updateAngle(val) {
	fields[current].angle += val;
	if (fields[current].angle <= -180)
		fields[current].angle += 360;
	if (fields[current].angle > 180)
		fields[current].angle -= 360;
	redraw();

	window[fields[current].sport + 'UpdateForm']();

	// Avoid form submission
	return false;
}

function updateWidth(val) {
	fields[current].width += val;
	if (fields[current].width < window[fields[current].sport + 'MinWidth']())
		fields[current].width = window[fields[current].sport + 'MinWidth']();
	if (fields[current].width > window[fields[current].sport + 'MaxWidth']())
		fields[current].width = window[fields[current].sport + 'MaxWidth']();
	redraw();

	window[fields[current].sport + 'UpdateForm']();

	// Avoid form submission
	return false;
}

function updateLength(val) {
	fields[current].length += val;
	if (fields[current].length < window[fields[current].sport + 'MinLength']())
		fields[current].length = window[fields[current].sport + 'MinLength']();
	if (fields[current].length > window[fields[current].sport + 'MaxLength']())
		fields[current].length = window[fields[current].sport + 'MaxLength']();
	redraw();

	window[fields[current].sport + 'UpdateForm']();

	// Avoid form submission
	return false;
}

var parking_listener = null;
var entrance_listener = null;

function addParking() {
	if (parking_listener != null) {
		google.maps.event.removeListener(parking_listener);
	}

	//zjQuery('#map').css('cursor','crosshair');
	parking_listener = google.maps.event.addListener(map, 'click', function(event) {
		addParkingClick(event);
	});

	// Avoid form submission
	return false;
}

function addParkingClick(event) {
	google.maps.event.removeListener(parking_listener);
	parking_listener = null;
	//zjQuery('#map').css('cursor','auto');

	++ lastParkingId;
	var marker = showParking (event.latLng);
	marker.setOptions({'draggable':true});
	deleteParkingOnClick(marker, lastParkingId);
	parking[lastParkingId] = { 'marker': marker };
}

function deleteParking(id) {
	if (confirm('Are you sure you want to delete this parking label?')) {
		parking[id].marker.setMap(null);
		delete(parking[id]);
	}

	// Avoid form submission
	return false;
}

function addEntrance() {
	if (entrance_listener != null) {
		google.maps.event.removeListener(entrance_listener);
	}

	//zjQuery('#map').css('cursor','crosshair');
	entrance_listener = google.maps.event.addListener(map, 'click', function(event) {
		addEntranceClick(event);
	});

	// Avoid form submission
	return false;
}

function addEntranceClick(event) {
	google.maps.event.removeListener(entrance_listener);
	entrance_listener = null;
	//zjQuery('#map').css('cursor','auto');

	++ lastEntranceId;
	var marker = showEntrance (event.latLng);
	marker.setOptions({'draggable':true});
	deleteEntranceOnClick(marker, lastEntranceId);
	entrances[lastEntranceId] = { 'marker': marker };
}

function deleteEntrance(id) {
	if (confirm('Are you sure you want to delete this entrance label?')) {
		entrances[id].marker.setMap(null);
		delete(entrances[id]);
	}

	// Avoid form submission
	return false;
}

function check() {
	if (!confirm('Is the zoom level set properly for this view?')) {
		return false;
	}

	// Save any layout changes into the form
	window[fields[current].sport + 'SaveField']();
	for (var id in fields) {
		zjQuery('#fields-' + id + '-zoom').val(map.getZoom());
		if (fields[id].length != 0) {
			zjQuery('#fields-' + id + '-angle').val(fields[id].angle);
			zjQuery('#fields-' + id + '-width').val(fields[id].width);
			zjQuery('#fields-' + id + '-length').val(fields[id].length);
		}
		zjQuery('#fields-' + id + '-latitude').val(fields[id].marker.getPosition().lat());
		zjQuery('#fields-' + id + '-longitude').val(fields[id].marker.getPosition().lng());
	}

	// Combine the parking details
	var parkingString = '';
	for (var p in parking) {
		if (parking[p] != undefined) {
			var position = parking[p].marker.getPosition();
			parkingString += position.lat() + ',' + position.lng() + '/';
		}
	}
	parkingString = parkingString.substring(0, parkingString.length - 1);
	zjQuery('#parking').val(parkingString);

	// Combine the entrance details
	var entranceString = '';
	for (var p in entrances) {
		if (entrances[p] != undefined) {
			var position = entrances[p].marker.getPosition();
			entranceString += position.lat() + ',' + position.lng() + '/';
		}
	}
	entranceString = entranceString.substring(0, entranceString.length - 1);
	zjQuery('#entrances').val(entranceString);
}
