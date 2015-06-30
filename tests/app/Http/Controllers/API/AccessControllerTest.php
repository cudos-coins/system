<?php

namespace CC\Http\Controllers\API;

use Illuminate\Support\Facades\Crypt;
use TestCase;

/**
 * Testing of the access controller.
 * @author blange <code@b3nl.de>
 * @category Controllers
 * @package CC\Http
 * @subpackage API
 * @version $id$
 */
class AccessControllerTest extends TestCase
{
    /**
     * Checks that the get route is deactivated.
     * @return void
     */
    public function testMissingGETRoute()
    {
        $response = $this->call('GET', '/api/access_token');

        $this->assertEquals(405, $response->getStatusCode());
    } // function

    /**
     * Checks the successfull acquisitions of a token.
     * @return string
     */
    public function testStoreSuccess()
    {
        $this->seed('UserTableSeeder');

        $response = $this->call('POST', '/api/access_token', ['email' => 'foo@bar.com', 'password' => 'password']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(is_array($responseContent = $response->getOriginalContent()));
        $this->assertArrayHasKey('token', $responseContent);
        $this->assertArrayHasKey('ttl', $responseContent['token']);
        $this->assertArrayHasKey('value', $responseContent['token']);
        $this->assertSame(getenv('AUTH_TTL'), $responseContent['token']['ttl']);
        $this->assertTrue((bool)Crypt::decrypt($responseContent['token']['value']));

        return $responseContent['token']['value'];
    } // function

    /**
     * Checks that 403 is returned if there are wrong user credentials.
     * @return void
     */
    public function testStoreWithoutCorrectUser()
    {
        $response = $this->call('POST', '/api/access_token', ['email' => 'test@example.com', 'password' => uniqid()]);

        $this->assertEquals(403, $response->getStatusCode());
    } // function

    /**
     * Checks if a 404 error is returned, if there is no correct data.
     * @return void
     */
    public function testStoreWithoutData()
    {
        $response = $this->call('POST', '/api/access_token');

        $this->assertEquals(404, $response->getStatusCode());
    } // function

    /**
     * Checks if the update is done correctly.
     * @depends testStoreSuccess
     * @return void
     * @todo Maybe cache the nonce or the token itself in the database to enable additional checks.
     */
    public function testUpdateWithCorrectData($token)
    {
        $response = $this->call(
            'PUT', '/api/access_token', [], [], [],
            ['HTTP_X-' . str_replace(['/', '\\'], '', $this->getAppNamespace()) . '-ACCESS-TOKEN' => $token]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(is_array($responseContent = $response->getOriginalContent()));
        $this->assertArrayHasKey('token', $responseContent);
        $this->assertArrayHasKey('ttl', $responseContent['token']);
        $this->assertArrayHasKey('value', $responseContent['token']);
        $this->assertSame(getenv('AUTH_TTL'), $responseContent['token']['ttl']);
        $this->assertTrue((bool)Crypt::decrypt($responseContent['token']['value']));
        $this->assertNotSame($token, $responseContent['token']['value']);
    } // function

    /**
     * Checks if the update is done correctly and reuses the relatively new access token.
     * @depends testStoreSuccess
     * @return void
     */
    public function testUpdateWithCorrectDataReuse($token)
    {

    } // function

    /**
     * Checks if a 404 error is returned, if there is no correct data.
     * @return void
     */
    public function testUpdateWithoutData()
    {
        $response = $this->call('PUT', '/api/access_token');

        $this->assertEquals(401, $response->getStatusCode());
    } // function

    /**
     * Checks if 403 is returned, if the access code is wrong.
     * @return void
     */
    public function testUpdateWithWrongData()
    {
        $response = $this->call(
            'PUT', '/api/access_token', [], [], [],
            ['HTTP_X-' . str_replace(['/', '\\'], '', $this->getAppNamespace()) . '-ACCESS-TOKEN' => uniqid()]
        );

        $this->assertEquals(403, $response->getStatusCode());
    } // function
}
