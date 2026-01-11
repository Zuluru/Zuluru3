<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SubRequestsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SubRequestsTable Test Case
 */
class SubRequestsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SubRequestsTable
     */
    protected $SubRequests;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('SubRequests') ? [] : ['className' => SubRequestsTable::class];
        $this->SubRequests = $this->getTableLocator()->get('SubRequests', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->SubRequests);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
