<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\UserGroup;
use App\Test\Factory\UserGroupFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class UserGroupTest extends TestCase {

	/**
	 * Test _getLongName method
	 */
	public function testGetLongName(): void {
	    $description = 'Foo';

	    $group = UserGroupFactory::make()->getEntity();
        $this->assertEquals($group->name, $group->long_name);

        $group = UserGroupFactory::make(compact('description'))->getEntity();
        $this->assertEquals($group->name . ': ' . $group->description, $group->long_name);
	}

}
