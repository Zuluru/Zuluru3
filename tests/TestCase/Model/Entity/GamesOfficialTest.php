<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\GamesOfficial;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\GamesOfficial Test Case
 */
class GamesOfficialTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\GamesOfficial
     */
    protected $GamesOfficial;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->GamesOfficial = new GamesOfficial();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->GamesOfficial);

        parent::tearDown();
    }
}
