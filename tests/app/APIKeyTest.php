<?php

namespace CC;

use TestCase;

/**
 * Testing of the api key model.
 * @author blange <code@b3nl.de>
 * @category models
 * @package CC
 * @version $id$
 */
class APIKeyTest extends TestCase
{
    /**
     * The fixture for this test.
     * @var APIKey|void
     */
    protected $fixture = null;

    /**
     * Sets the test up.
     * @return void
     */
    public function setUp()
    {
        $return = parent::setUp();

        $this->fixture = new APIKey();

        return $return;
    } // function

    /**
     * Checks the type of the model instance.
     * @return void
     */
    public function testType()
    {
        $this->assertInstanceOf('\\Illuminate\\Database\\Eloquent\\Model', $this->fixture);
    } // function

    /**
     * Checks the getter for the user.
     * @return void
     */
    public function testUserGetter()
    {
        $this->assertInstanceOf(
            'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            $this->fixture->user()
        );
    } // function
}
