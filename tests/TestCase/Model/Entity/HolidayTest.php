<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Holiday;
use App\Test\Factory\HolidayFactory;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class HolidayTest extends TestCase {
    /**
	 * Test _getDate method
	 */
	public function testGetDate(): void {
        $holiday = HolidayFactory::make([
            'date' => new FrozenDate('December 25'),
            'name' => 'Christmas',
        ])->getEntity();
		$this->assertEquals(new FrozenDate('December 25'), $holiday->date);
	}

}
