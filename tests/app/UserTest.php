<?php

namespace CC;

use TestCase;

/**
 * Testing of the access controller.
 * @author blange <code@b3nl.de>
 * @category Controllers
 * @package CC\Http
 * @subpackage API
 * @version $id$
 */
class UserTest extends TestCase
{
    /**
     * The fixture for this test.
     * @var User|void
     */
    protected $fixture = null;

    /**
     * Sets the test up.
     * @return void
     */
    public function setUp()
    {
        $return = parent::setUp();

        $this->fixture = new User();

        return $return;
    } // function

    /**
     * Checks if there is an api key getter.
     * @return void
     */
    public function testApiKeysGetter()
    {
        /** @var \\Illuminate\\Database\\Eloquent\\Relations\\HasMany $collection */
        $this->assertInstanceOf(
            '\\Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            $collection = $this->fixture->api_keys()
        );

        $this->assertInstanceOf('\\CC\\APIKey', $collection->getRelated());
    } // function

    /**
     * Checks the getter for the companies.
     * @return void
     */
    public function testCompaniesMethod()
    {
        $this->assertInstanceOf(
            'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            $collection = $this->fixture->companies()
        );

        $this->assertInstanceOf('\\CC\\Company', $collection->getRelated());
    } // function

    /**
     * Checks the return for the transactions getter.
     * @return void
     */
    public function testTransactionsGetterInstance()
    {
        $this->assertInstanceOf(
            'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            $collection = $this->fixture->transactions()
        );

        $this->assertInstanceOf('\\CC\\Transaction', $collection->getRelated());
    } // function
}
