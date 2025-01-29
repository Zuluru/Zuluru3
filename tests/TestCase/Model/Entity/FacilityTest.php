<?php
namespace App\Test\TestCase\Model\Entity;

use App\Test\Factory\FacilityFactory;
use App\Test\Factory\LeagueFactory;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;
use Cake\Utility\Inflector;

class FacilityTest extends TestCase {

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();

		// No matter what, we make fall a current season for testing purposes
		$startMonthDay = FrozenTime::yesterday()->format('m-d');
		$endMonthDay = FrozenTime::tomorrow()->format('m-d');

		// Configuration for organization DNE so we fake it out:
		Configure::write(['organization' => [
			'winter_start' => 'something-01-01',
			'winter_end' => 'something-04-01',
			'winter_indoor_start' => 'something-01-01',
			'winter_indoor_end' => 'something-04-01',
			'spring_start' => 'something-04-01',
			'spring_end' => 'something-07-01',
			'spring_indoor_start' => 'something-04-01',
			'spring_indoor_end' => 'something-07-01',
			'summer_start' => 'something-07-01',
			'summer_end' => 'something-10-01',
			'summer_indoor_start' => 'something-07-01',
			'summer_indoor_end' => 'something-10-01',
			'fall_start' => 'something-'.$startMonthDay,
			'fall_end' => 'something-'.$endMonthDay,
			'fall_indoor_start' => 'something-'.$startMonthDay,
			'fall_indoor_end' => 'something-'.$endMonthDay,
		]]);

		// Facilities use the file system for locating permits; point to a local test folder
		// TODO: Handle in the bootstrap?
		Configure::write('App.paths.files', TESTS . 'test_app' . DS . 'webroot' . DS . 'files');

		// Copy the test permit from the known folder to this year's
		$permit_path = Configure::read('App.paths.files') . DS . 'permits';
		$folder = new Folder($permit_path . DS . '0000');
		$folder->copy($permit_path . DS . FrozenTime::now()->year);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		// Delete the temporary permit copy
		$permit_path = Configure::read('App.paths.files') . DS . 'permits';
		$folder = new Folder($permit_path . DS . FrozenTime::now()->year);
		$folder->delete();

		parent::tearDown();
	}

	/**
	 * Test _getPermits method
	 */
	public function testGetPermits(): void {
		$year = FrozenTime::now()->year;

		[$abc, $xyz] = FacilityFactory::make([
			['code' => 'ABC'],
			['code' => 'XYZ'],
		])
			->persist();

		LeagueFactory::make(['season' => 'Fall', 'is_open' => true])->persist();

		$this->assertGreaterThan(0, count($xyz->permits), 'No Folder info provided from XYZ');

		// All of XYZ's should be empty and just give me dir info
		foreach ($xyz->permits as $seasonName => $fileDetails) {
			$this->assertCount(1, $fileDetails, 'Empty season file details or too many elements ');
			if (array_key_exists('dir', $fileDetails)) {
				// Validate the end of the dir name
				$dirName = $fileDetails['dir'];
				$endOfName = 'permits' . DS . $year . DS . Inflector::underscore($seasonName);
				$endPos = strpos($dirName, $endOfName);
				$this->assertNotFalse($endPos, 'Dir name lacks expected text');
			} else {
				$this->fail('dir key not in file details as expected');
			}
		}

		// All of ABC's should be empty and just give me dir info except for fall
		$foundFall = false;
		foreach ($abc->permits as $seasonName => $fileDetails) {
			$this->assertGreaterThan(0, count($fileDetails), 'Empty season file details');
			if (array_key_exists('dir', $fileDetails)) {
				// Validate the end of the dir name
				$dirName = $fileDetails['dir'];
				$endOfName = 'permits' . DS . $year . DS . Inflector::underscore($seasonName);
				$endPos = strpos($dirName, $endOfName);
				$this->assertNotFalse($endPos, 'Dir name lacks expected text');
			} else if (strcmp('Fall', $seasonName) === 0) {// Validate the file is in there
				$this->assertNotFalse(array_key_exists('file', $fileDetails), 'File name not provided');
				$this->assertNotFalse(array_key_exists('url', $fileDetails), 'URL not provided');
				$this->assertEquals(0, strcmp($fileDetails['file'], 'ABC.png'), 'Wrong file name provided');
				$this->assertEquals(0, strcmp($fileDetails['url'], "files/permits/$year/fall/ABC.png"), 'Wrong URL provided');
				$foundFall = true;
			} else {
				$this->fail('dir key not in file details and not Fall season as expected');
			}
		}
		// Make sure we actually found the Fall season for ABC
		$this->assertTrue($foundFall, 'No Fall Season found in ABC');
	}

}
