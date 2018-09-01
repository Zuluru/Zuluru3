//
// Functions and variables for the "all fields" view
//

var bounds = new google.maps.LatLngBounds();

function initializeOverview() {
	initialize();

	drawFields();

	map.fitBounds(bounds);
	map.setMapTypeId(google.maps.MapTypeId.HYBRID);
}

function drawFields() {
	for (var id in fields) {
		drawField(id);
		var text = '<h3>' + fields[id].name + ' (' + fields[id].code + ')</h3>' +
					'<p>' + fields[id].location_street +
					'<br>Latitude: ' + Math.round(fields[id].latitude * 100000) / 100000  +
					'<br>Longitude: ' + Math.round(fields[id].longitude * 100000) / 100000 +
					'<br>Surface: ' + fields[id].surface +
					'</p>';

		fields[id].window = new google.maps.InfoWindow({'content':text});

		openOnClick(fields[id].marker, fields[id].window);
	}
}

function drawField(id) {
	var position = new google.maps.LatLng(fields[id].latitude, fields[id].longitude);
	fields[id].marker = createMarker(position, fields[id].name);
	bounds.extend(position);
}

function openOnClick(marker, window) {
	google.maps.event.addListener(marker, 'click', function() {
		for (var id in fields) {
			fields[id].window.close();
		}
		window.open(map, marker);
	});
}

function openField(open) {
	for (var id in fields) {
		fields[id].window.close();
	}
	fields[open].window.open(map, fields[open].marker);
}
