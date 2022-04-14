<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Facility;
use App\Test\Factory\GameFactory;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Inflector;

class FacilityTest extends TestCase {

	/**
	 * Test subject 1
	 *
	 * @var \App\Model\Entity\Facility
	 */
	public $FacilitySunnybrook;
	/**
	 * Test subject 2
	 *
	 * @var \App\Model\Entity\Facility
	 */
	public $FacilityBroadacres;

	/**
	 * setUp method
	 */
	public function setUp(): void {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		parent::setUp();
		$facilities = TableRegistry::getTableLocator()->get('Facilities');
		$this->FacilitySunnybrook = $facilities->get(FACILITY_ID_SUNNYBROOK, [
			'contain' => ['Fields'],
		]);
		$this->FacilityBroadacres = $facilities->get(FACILITY_ID_BROADACRES, [
			'contain' => ['Fields'],
		]);

		// Configuration for organization DNE so we fake it out:
		// TODO: Handle by loading the settings fixtures?
		Configure::write('organization.winter_start', 'something-01-01');
		Configure::write('organization.winter_end', 'something-04-01');

		Configure::write('organization.winter_indoor_start', 'something-01-01');
		Configure::write('organization.winter_indoor_end', 'something-04-01');

		Configure::write('organization.spring_start', 'something-04-01');
		Configure::write('organization.spring_end', 'something-07-01');

		Configure::write('organization.spring_indoor_start', 'something-04-01');
		Configure::write('organization.spring_indoor_end', 'something-07-01');

		Configure::write('organization.summer_start', 'something-07-01');
		Configure::write('organization.summer_end', 'something-10-01');

		Configure::write('organization.summer_indoor_start', 'something-07-01');
		Configure::write('organization.summer_indoor_end', 'something-10-01');
		// No matter what, we make fall a current season for testing purposes
		$startMonthDay = FrozenTime::yesterday()->format('m-d');
		$endMonthDay = FrozenTime::tomorrow()->format('m-d');
		Configure::write('organization.fall_start', 'something-'.$startMonthDay);
		Configure::write('organization.fall_end', 'something-'.$endMonthDay);

		Configure::write('organization.fall_indoor_start', 'something-'.$startMonthDay);
		Configure::write('organization.fall_indoor_end', 'something-'.$endMonthDay);

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
		unset($this->FacilitySunnybrook);
		unset($this->FacilityBroadacres);

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

		$resultSunnybrook = $this->FacilitySunnybrook->permits;
		$resultBroadcres = $this->FacilityBroadacres->permits;
		$this->assertGreaterThan(0, count($resultSunnybrook), 'No Folder info provided from Sunnybrook');

		// All of Sunnybrook's should be empty and just give me dir info
		foreach ($resultSunnybrook as $seasonName => $fileDetails) {
			$this->assertEquals(1, count($fileDetails), 'Empty season file details or too many elements ');
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

		// All of Broadacres's should be empty and just give me dir info except for fall
		$foundFall = false;
		foreach ($resultBroadcres as $seasonName => $fileDetails) {
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
				$this->assertEquals(0, strcmp($fileDetails['file'], 'BRO.png'), 'Wrong file name provided');
				$this->assertEquals(0, strcmp($fileDetails['url'], "files/permits/$year/fall/BRO.png"), 'Wrong URL provided');
				$foundFall = true;
			} else {
				$this->fail('dir key not in file details and not Fall season as expected');
			}
		}
		// Make sure we actually found the Fall season for Broadacres
		$this->assertTrue($foundFall, 'No Fall Season found in Broadacres');
	}

}
