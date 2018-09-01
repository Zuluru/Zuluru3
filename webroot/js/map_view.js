//
// Functions and variables for field viewing
//

// Objects for handling directions
var directionsService = new google.maps.DirectionsService();
var directionsDisplay;

function initializeView(id) {
	initialize();

	var field = fields[id];

	map.setCenter(new google.maps.LatLng(field.latitude, field.longitude));
	map.setZoom(field.zoom);
	map.setMapTypeId(google.maps.MapTypeId.HYBRID);

	drawFields();
	selectField(id);
	fields[id].window.open(map, fields[id].marker);

	for (var p in parking) {
		showParking (parking[p].position);
	}

	for (var p in entrances) {
		showEntrance (entrances[p].position);
	}

	directionsDisplay = new google.maps.DirectionsRenderer();
	directionsDisplay.setMap(map);
	directionsDisplay.setPanel(document.getElementById('directions'));
}

function drawFields() {
	for (var id in fields) {
		drawField(id);
		var text = '<h3>' + name + ' ' + fields[id].num + '</h3>' +
					'<p>' + address +
					'<br>Latitude: ' + Math.round(fields[id].latitude * 100000) / 100000  +
					'<br>Longitude: ' + Math.round(fields[id].longitude * 100000) / 100000 +
					'<br>Surface: ' + fields[id].surface +
					'</p>';
		var layout = window[fields[id].sport + 'LayoutText'](id);
		if (layout != null) {
			text += '<h4>Suggested field layout:</h4>' + layout + '</p>';
		}
		fields[id].window = new google.maps.InfoWindow({'content':text});

		openOnClick(fields[id].marker, fields[id].window);
	}
}

function openOnClick(marker, window) {
	google.maps.event.addListener(marker, 'click', function() {
		for (var id in fields) {
			fields[id].window.close();
		}
		window.open(map, marker);
	});
}

function selectField(id) {
	// Remove selection from the old field
	if (current != 0 && fields[current].length != 0) {
		fields[current].field_outline.setOptions({'fillColor':'#ff6060'});
	}

	if (fields[id].length != 0) {
		fields[id].field_outline.setOptions({'fillColor':'#60ff60'});
	}
	current = id;
}

// Array for decoding the failure codes
var reasons=[];
reasons[google.maps.DirectionsStatus.NOT_FOUND] = "At least one of the locations specified in the requests's origin, destination, or waypoints could not be geocoded.";
reasons[google.maps.DirectionsStatus.ZERO_RESULTS] = "No route could be found between the origin and destination.";
reasons[google.maps.DirectionsStatus.MAX_WAYPOINTS_EXCEEDED] = "Too many DirectionsWaypoints were provided in the DirectionsRequest. The maximum allowed waypoints is 8, plus the origin, and destination. Maps API for Business customers are allowed 23 waypoints, plus the origin, and destination.";
reasons[google.maps.DirectionsStatus.INVALID_REQUEST] = "The provided DirectionsRequest was invalid.";
reasons[google.maps.DirectionsStatus.OVER_QUERY_LIMIT] = "The webpage has sent too many requests within the allowed time period.";
reasons[google.maps.DirectionsStatus.REQUEST_DENIED] = "The webpage is not allowed to use the directions service.";
reasons[google.maps.DirectionsStatus.UNKNOWN_ERROR] = "A directions request could not be processed due to a server error. The request may succeed if you try again.";

function getDirections() {
	// Set up the walk and avoid highways options
	var travelMode = google.maps.TravelMode.DRIVING;
	if (document.getElementById('walk').checked) {
		travelMode = google.maps.TravelMode.WALKING;
	}

	var avoidHighways = false;
	if (document.getElementById('highways').checked) {
		avoidHighways = true;
	}

	var request = {
		'origin':document.getElementById('saddr').value,
		'destination':full_address,
		'provideRouteAlternatives':true,
		'avoidHighways':avoidHighways,
		'travelMode':travelMode
	};
	directionsService.route(request, function(response, status) {
		if (status == google.maps.DirectionsStatus.OK) {
			directionsDisplay.setDirections(response);
		} else {
			alert(reasons[status]);
		}
	});
}
