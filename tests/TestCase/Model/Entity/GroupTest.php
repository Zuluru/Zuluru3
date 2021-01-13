<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Group;
use App\Test\Factory\GroupFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class GroupTest extends TestCase {

	/**
	 * Test _getLongName method
	 *
	 * @return void
	 */
	public function testGetLongName() {
	    $description = 'Foo';

	    $group = GroupFactory::make()->getEntity();
        $this->assertEquals($group->name, $group->long_name);

        $group = GroupFactory::make(compact('description'))->getEntity();
        $this->assertEquals($group->name . ': ' . $group->description, $group->long_name);
	}

}
