<?php
namespace App\Test\Fixture;

use Cake\Core\Configure;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * FacilitiesFixture
 *
 */
class FacilitiesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'facilities'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'is_open' => true,
				'name' => 'Sunnybrook',
				'code' => 'SUN',
				'sport' => 'ultimate',
				'location_street' => '1116 Leslie St.',
				'location_city' => 'Toronto',
				'location_province' => 'Ontario',
				'parking' => '43.724390,-79.353640/43.725212,-79.359666/43.724460,-79.360049/43.720614,-79.359645',
				'region_id' => REGION_ID_EAST,
				'driving_directions' => 'Follow the DVP and exit at Eglinton Ave. westbound. Proceed to Leslie St. and turn right (north). The park entrance is immediately on your left, 50m from Eglinton. Upon entering the park, follow the main road all the way to the end (stay right at the first fork). The road is quite long and winds through the park, past several parking lots & picnic areas, past the stables, up a hill and into the open.',
				'parking_details' => 'For the field hockey fields, use the parking lot at the end of the road (hidden in the woods). For the cricket fields, use the parking lots on either side of the road at the top of the hill. For the rubgy fields or greenspace, you can park in the lower lot (see the map for location) and climb the stairs, or fight for space with people headed to the cricket fields.',
				'transit_directions' => 'Take the subway to Eglinton station and take any bus (i.e. 34+, 51, 54+, or 100) eastbound along Eglinton Ave. Get off at Leslie St. and cross the street to the park entrance. At the bottom of the hill, where the park road curves left (west) there is a path through the woods to the playing fields. If you cannot find this shortcut, display a disc conspicuously while walking along the road, in the hope that a fellow player will stop and offer you a lift. It is a long, long hike.',
				'biking_directions' => null,
				'washrooms' => 'The pavilion that is on the north side of the field (adjacent to Cricket North) has public washrooms.',
				'public_instructions' => 'The field hockey fields are located at the very end of the park road.</p><p>The rugby fields can accomodate two narrow ultimate fields each.</p><p>There are three fields north of the road on the cricket pitch. Fields North 1 and North 2 should be set up on the west side of the cricket pitch strip and field North 3 on the east side of the strip.</p>',
				'site_instructions' => null,
				'sponsor' => null,
				'entrances' => null,
			],
			[
				'is_open' => true,
				'name' => 'Broadacres JS',
				'code' => 'BRO',
				'sport' => 'ultimate',
				'location_street' => '45 Crendon Dr.',
				'location_city' => 'Toronto',
				'location_province' => 'Ontario',
				'parking' => '43.647411,-79.571840/43.648719,-79.571271',
				'region_id' => REGION_ID_WEST,
				'driving_directions' => 'Take the 427 and exit at Burnhamthorpe Rd. Exit west on Burnhamthorpe (this will be left if coming from the south, right if you\'re coming from the north). Proceed on Burnhamthorpe and turn right (north) onto Renforth Dr. The first right on Renforth will be Crendon Dr, turn here, and the school will be up on the right hand side past a couple of streets.',
				'parking_details' => null,
				'transit_directions' => 'Take the Bloor-Danforth line to Islington Station. Board the 50 bus Westbound. Get off at The West Mall and walk north for a few minutes, and you can cut through Broadacres Park.',
				'biking_directions' => null,
				'washrooms' => null,
				'public_instructions' => null,
				'site_instructions' => null,
				'sponsor' => null,
				'entrances' => null
			],
			[
				'is_open' => true,
				'name' => 'Bloor CI',
				'code' => 'BCI',
				'sport' => 'ultimate',
				'location_street' => '1141 Bloor St. W.',
				'location_city' => 'Toronto',
				'location_province' => 'Ontario',
				'parking' => '43.658638,-79.437614/43.658099,-79.437305',
				'region_id' => REGION_ID_WEST,
				'driving_directions' => 'Bloor CI is right on the southwest corner of Bloor Street and Dufferin Street.',
				'parking_details' => 'There is very limited parking by the tennis courts and on the street.',
				'transit_directions' => 'Take the subway to the Dufferin station on the Bloor-Danforth Line. Bloor CI is on the southwest corner of Bloor and Dufferin, you should see it when you come to street level. The fields are right behind the school.',
				'biking_directions' => null,
				'washrooms' => null,
				'public_instructions' => null,
				'site_instructions' => null,
				'sponsor' => null,
				'entrances' => null
			],
			[
				'is_open' => false,
				'name' => 'Marilyn Bell',
				'code' => 'MAR',
				'sport' => 'ultimate',
				'location_street' => '851 Lake Shore Blvd W.',
				'location_city' => 'Toronto',
				'location_province' => 'Ontario',
				'parking' => '43.632811,-79.437874',
				'region_id' => REGION_ID_WEST,
				'driving_directions' => 'Coming eastbound on Lake Shore, turn right onto Aquatic Drive.',
				'parking_details' => null,
				'transit_directions' => null,
				'biking_directions' => null,
				'washrooms' => null,
				'public_instructions' => null,
				'site_instructions' => null,
				'sponsor' => null,
				'entrances' => null
			],
			[
				'is_open' => true,
				'name' => 'Central Tech Stadium',
				'code' => 'CTS',
				'sport' => 'ultimate',
				'location_street' => '725 Bathurst St.',
				'location_city' => 'Toronto',
				'location_province' => 'Ontario',
				'parking' => '',
				'region_id' => REGION_ID_SOUTH,
				'driving_directions' => 'Head south on Bathurst St. from Bloor St. The school is at the corner of Bathurst and Harbord St.',
				'parking_details' => 'The school parking lot is on the east side of the school off of Borden Street.',
				'transit_directions' => 'Take the Bloor line to the Bathurst station. Walk south on Bathurst, it\'s just two short blocks to the field.',
				'biking_directions' => null,
				'washrooms' => null,
				'public_instructions' => null,
				'site_instructions' => null,
				'sponsor' => null,
				'entrances' => null
			],
		];

		if (!defined('FACILITY_ID_SUNNYBROOK')) {
			$i = 0;
			define('FACILITY_ID_SUNNYBROOK', ++$i);
			define('FACILITY_ID_BROADACRES', ++$i);
			define('FACILITY_ID_BLOOR', ++$i);
			define('FACILITY_ID_MARILYN_BELL', ++$i);
			define('FACILITY_ID_CENTRAL_TECH', ++$i);
		}

		parent::init();
	}

}
