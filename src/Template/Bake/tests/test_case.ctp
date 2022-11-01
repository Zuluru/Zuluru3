<%
/**
 * Test Case bake template
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Utility\Inflector;

$isController = strtolower($type) === 'controller';
$isTable = strtolower($type) === 'table';

if ($isController) {
	$roles = [
		'AsAdmin' => ' as an admin',
		'AsManager' => ' as a manager',
		'AsCoordinator' => ' as a coordinator',
		'AsCaptain' => ' as a captain',
		'AsPlayer' => ' as a player',
		'AsVisitor' => ' as someone else',
		'AsAnonymous' => ' without being logged in',
	];
} else {
	$roles = ['' => ''];
	if (!$isTable) {
		$uses[] = 'Cake\TestSuite\TestCase';
	}
}
sort($uses);
%>
<?php
namespace <%= $baseNamespace; %>\Test\TestCase\<%= $subNamespace %>;

<% foreach ($uses as $dependency): %>
use <%= $dependency; %>;
<% endforeach; %>

/**
 * <%= $fullClassName %> Test Case
 */
<% if ($isController): %>
class <%= $className %>Test extends ControllerTestCase {
<% elseif ($isTable): %>
class <%= $className %>Test extends TableTestCase {
<% else: %>
class <%= $className %>Test extends TestCase {
<% endif; %>
<% if (!empty($properties)): %>
<% foreach ($properties as $propertyInfo): %>

	/**
	 * <%= $propertyInfo['description'] %>
	 *
	 * @var <%= $propertyInfo['type'] %>
	 */
	public $<%= $propertyInfo['name'] %><% if (isset($propertyInfo['value'])): %> = <%= $propertyInfo['value'] %><% endif; %>;
<% endforeach; %>
<% endif; %>
<% if (!empty($fixtures)): %>

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [<%= $this->Bake->stringifyList(array_values($fixtures)) %>];
<% endif; %>
<% if (!empty($construction)): %>

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
	<%- if ($preConstruct): %>
		<%= $preConstruct %>
	<%- endif; %>
		$this-><%= $subject . ' = ' . $construction %>
	<%- if ($postConstruct): %>
		<%= $postConstruct %>
	<%- endif; %>
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this-><%= $subject %>);

		parent::tearDown();
	}
<% endif; %>
<% foreach ($methods as $method): %>
<% if (!in_array($method, ['isAuthorized', 'beforeFilter', 'afterFilter'])): %>
<% foreach ($roles as $role => $desc): %>

	/**
	 * Test <%= $method %> method<%= $desc %>
	 */
	public function test<%= Inflector::camelize($method) %><%= $role %>(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}
<% endforeach; %>
<% endif; %>
<% endforeach; %>
<% if (empty($methods)): %>

	/**
	 * Test initial setup
	 */
	public function testInitialization(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}
<% endif; %>

}
