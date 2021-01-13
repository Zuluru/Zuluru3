<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Field;
use App\Test\Factory\FieldFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class FieldTest extends TestCase
{
    /**
	 * Test _getLongName and _getLongCode method
	 *
	 * @return void
	 */
	public function testGetLongName()
    {
        $field = FieldFactory::make(['num' => 'Field Hockey 1'])
            ->with('Facilities', [
                'name' => 'Sunnybrook',
                'code' => 'SUN'
            ])
            ->persist();
        $this->assertEquals('Sunnybrook Field Hockey 1', $field->long_name);
        $this->assertEquals('SUN Field Hockey 1', $field->long_code);
    }
}
