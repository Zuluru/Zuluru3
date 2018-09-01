//
// Common functions and variables shared among various map pages
//

var map;

function initialize() {
	resizeMap();
	map = new google.maps.Map(document.getElementById('map'));
}

// Swiped this from the Google Maps page
function resizeMap() {
	var offset = 0;
	for (var elem = document.getElementById('map'); elem != null; elem = elem.offsetParent)
	{
		offset += elem.offsetTop;
	}
	var windowHeight = getWindowHeight();

	var height = windowHeight - offset - 10;
	if (height >= 0)
	{
		document.getElementById('map').style.height = height + 'px';
	}
}

// Swiped this from the Google Maps page
function getWindowHeight() {
	if (window.self && self.innerHeight)
	{
		return self.innerHeight;
	}
	if (document.documentElement && document.documentElement.clientHeight)
	{
		return document.documentElement.clientHeight;
	}
	return 0;
}

function makePosition(start, distance, angle) {
	distance *= 0.9144;	// convert from yards to metres
	return google.maps.geometry.spherical.computeOffset(start, distance, 90 - angle);
}

// Variables that must be set by the calling page
var name = '';
var address = '';
var full_address = '';
var fields = Array();
var current = 0;

// Parking locations
var parking = Array();

// Entrance locations
var entrances = Array();

function drawField(id) {
	var position = new google.maps.LatLng(fields[id].latitude, fields[id].longitude);
	fields[id].marker = createMarker(position, fields[id].num);

	fields[id].field_outline = new google.maps.Polygon({
		'map':map,
		'path':window[fields[id].sport + 'OutlinePositions'](id),
		'strokeColor':'#ffffff',
		'strokeWeight':2,
		'strokeOpacity':1.0,
		'fillColor':'#ff6060',
		'fillOpacity':0.4
	});

	fields[id].field_inlines = new Array();
	var inlines = window[fields[id].sport + 'InlinePositions'](id);
	for (var i = 0; i < inlines.length; i++) {
		fields[id].field_inlines[i] = new google.maps.Polyline({
			'map':map,
			'path':inlines[i],
			'strokeColor':'#ffffff',
			'strokeWeight':2
		});
	}
}

function createMarker(position, title) {
	var icon = new google.maps.MarkerImage(
		zuluru_img_path + 'blank-marker.png',
		new google.maps.Size(20, 34),
		new google.maps.Point(0, 0),
		new google.maps.Point(9, 34)
	);
	// TODOLATER: This is no longer supported???
	var shadow = new google.maps.MarkerImage(
		'https://www.google.com/mapfiles/shadow50.png',
		new google.maps.Size(37, 34),
		new google.maps.Point(0, 0),
		new google.maps.Point(9, 34)
	);

	var marker = new google.maps.Marker({
		'map':map,
		'position':position,
		'title':title.toString(),
		'icon':icon,
		'shadow':shadow
	});
	return marker;
}

function showParking(position) {
	var icon = new google.maps.MarkerImage(
		zuluru_img_path + 'parking_pin.png',
		new google.maps.Size(12, 20),
		new google.maps.Point(0, 0),
		new google.maps.Point(6, 20)
	);
	var shadow = new google.maps.MarkerImage(
		zuluru_img_path + 'mm_20_shadow.png',
		new google.maps.Size(22, 20),
		new google.maps.Point(0, 0),
		new google.maps.Point(6, 20)
	);

	return new google.maps.Marker({
		'map':map,
		'position':position,
		'icon':icon,
		'shadow':shadow
	});
}

function showEntrance(position) {
	var icon = new google.maps.MarkerImage(
		zuluru_img_path + 'entrance_pin.png',
		new google.maps.Size(12, 20),
		new google.maps.Point(0, 0),
		new google.maps.Point(6, 20)
	);
	var shadow = new google.maps.MarkerImage(
		zuluru_img_path + 'mm_20_shadow.png',
		new google.maps.Size(22, 20),
		new google.maps.Point(0, 0),
		new google.maps.Point(6, 20)
	);

	return new google.maps.Marker({
		'map':map,
		'position':position,
		'icon':icon,
		'shadow':shadow
	});
}

//
// Following functions copied/adapted from http://www.geocodezip.com/v3_polyline_example_arc.html
//

/* Based the on the Latitude/longitude spherical geodesy formulae & scripts
   at http://www.movable-type.co.uk/scripts/latlong.html
   (c) Chris Veness 2002-2010
*/
google.maps.LatLng.prototype.DestinationPoint = function (brng, dist) {
	var R = 6378137.0; // earth's mean radius in meters
	var brng = brng.toRad();
	var lat1 = this.lat().toRad(), lon1 = this.lng().toRad();
	var lat2 = Math.asin( Math.sin(lat1)*Math.cos(dist/R) +
						  Math.cos(lat1)*Math.sin(dist/R)*Math.cos(brng) );
	var lon2 = lon1 + Math.atan2(Math.sin(brng)*Math.sin(dist/R)*Math.cos(lat1),
								 Math.cos(dist/R)-Math.sin(lat1)*Math.sin(lat2));

	return new google.maps.LatLng(lat2.toDeg(), lon2.toDeg());
};

/**
 * Extend the Number object to convert degrees to radians
 *
 * @return {Number} Bearing in radians
 * @ignore
 */
Number.prototype.toRad = function () {
	return this * Math.PI / 180;
};

/**
 * Extend the Number object to convert radians to degrees
 *
 * @return {Number} Bearing in degrees
 * @ignore
 */
Number.prototype.toDeg = function () {
	return this * 180 / Math.PI;
};

function makeArc(center, initialBearing, finalBearing, radius) {
	var points = 32;
	radius *= 0.9144;	// convert from yards to metres
	var extp = new Array();

	initialBearing = 90 - initialBearing;
	finalBearing = 90 - finalBearing;

	var deltaBearing = (finalBearing - initialBearing)/points;
	for (var i=0; i < points+1; i++) {
		extp.push(center.DestinationPoint(initialBearing + i*deltaBearing, radius));
	}

	return extp;
}

function makeCircle(center, radius) {
	return makeArc(center, 0, 360, radius);
}
