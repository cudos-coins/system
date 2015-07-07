<?php

namespace CC\Http\Controllers\API;

use CC\APIKey;
use Closure;
use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use stdClass;
use TestCase;

/**
 * Testing of the controller.
 * @author blange <code@b3nl.de>
 * @category Controllers
 * @package CC\Http
 * @subpackage API
 * @version $id$
 */
class APIKeysControllerTest extends TestCase
{
    /**
     * Checks the hal structure of the response.
     * @param stdClass $result The response result.
     * @param string $routeName The route name.
     * @param int $skip How many articles are skipped?
     * @param int $take How many articles are taken?
     * @param bool $withRows Should there be a row?
     * @param array $urlParams The used url parameters.
     * @param callable $rowCallback An callback to check the single rows.
     * @return void
     */
    protected function checkHAL(
        stdClass $result,
        $routeName,
        $skip,
        $take,
        $withRows,
        array $urlParams = [],
        Closure $rowCallback = null
    ) {
        $this->assertObjectHasAttribute('_embedded', $result, 'Embedded data missing.');
        $this->assertObjectHasAttribute('_links', $result, 'Links missing.');
        $this->assertObjectHasAttribute('first', $result->_links, 'First link missing');
        $this->assertObjectHasAttribute('href', $result->_links->first, 'Href of first link missing.');
        $this->assertObjectHasAttribute('last', $result->_links, 'Last link missing.');
        $this->assertObjectHasAttribute('href', $result->_links->last, 'Href of last link missing.');
        $this->assertTrue((bool)$result->_links->last->href, 'No last link provided.');
        $this->assertObjectHasAttribute('self', $result->_links, 'Self link missing.');
        $this->assertObjectHasAttribute('href', $result->_links->self, 'Href of self link missing.');
        $this->assertTrue((bool)$result->_links->self->href, 'No self link provided.');
        $this->assertObjectHasAttribute('count', $result, 'No count element given.');
        $this->assertObjectHasAttribute('total', $result, 'No total element given.');

        $this->assertSame(
            $result->_links->first->href,
            route($routeName, array_merge($urlParams, ['skip' => 0])),
            'Wrong first link.'
        );

        $this->assertSame(
            $result->_links->self->href,
            route($routeName, $urlParams),
            'Wrong self link.'
        );

        if (!$withRows) {
            $this->assertSame(
                $result->_links->last->href,
                route($routeName, array_merge($urlParams, ['skip' => 0])),
                'Wrong last link.'
            );

            $this->assertSame(0, count($result->_embedded), 'There should be no data.');
            $this->assertSame(0, $result->count, 'Count should be 0.');
            $this->assertSame(0, $result->total, 'Total should be 0.');
        } // if
        else {
            $this->assertTrue((bool)count($result->_embedded), 'There should be data.');

            if ($result->total > $result->count) {
                $this->assertObjectHasAttribute('next', $result->_links, 'Next link missing');
                $this->assertObjectHasAttribute('href', $result->_links->next, 'Href of next link missing.');

                $this->assertSame(
                    $result->_links->next->href,
                    route($routeName, array_merge($urlParams, ['skip' => $skip + $take])),
                    'Wrong next link.'
                );

                $this->assertObjectHasAttribute('prev', $result->_links, 'Prev link missing');
                $this->assertObjectHasAttribute('href', $result->_links->prev, 'Href of prev link missing.');

                $this->assertSame(
                    $result->_links->prev->href,
                    route($routeName, array_merge($urlParams, ['skip' => $skip - $take])),
                    'Wrong prev link.'
                );
            } // if

            $this->assertSame(
                $result->_links->last->href,
                route(
                    $routeName,
                    array_merge($urlParams, ['skip' => $result->total - $take > 0 ? $result->total - $take : 0])
                ),
                'Wrong last link.'
            );

            foreach ($result->_embedded as $index => $row) {
                $rowCallback($index, $row);
            } // foreach
        } // if
    } // function

    /**
     * Returns the typical resource url.
     * @return string
     */
    protected function getResourceURL()
    {
        return '/api/api_keys/';
    } // function

    /**
     * Returns the route name.
     * @return string
     */
    protected function getRouteName()
    {
        return 'api.api_keys.index';
    } // functio
    
    /**
     * Returns a valid user filter.
     * @param bool $correctData Should the correct data be returned?
     * @return array
     */
    protected function getUserFilter($correctData = true)
    {
        return ['filter' => ['user_id' => $correctData ? 1 : 234234234]];
    } // function

    /**
     * Checks if the 404 is returned if the row is missing.
     * @return void
     */
    public function testDestroyMissing()
    {
        $response = $this->callProtected('DELETE', $this->getResourceUrl() . '' . uniqid());

        $this->assertEquals(404, $response->getStatusCode());
    } // function

    /**
     * Checks if an entry is deleted correctly.
     * @return void
     */
    public function testDestroySuccess()
    {
        $this->seed('APIKeySeeder');

        $response = $this->callProtected('DELETE', $this->getResourceUrl() . '1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('stdClass', $responseContent = json_decode($response->getContent()));
        $this->assertObjectNotHasAttribute('deleted_at', $responseContent);
        $this->assertObjectNotHasAttribute('hash', $responseContent);
        $this->assertObjectHasAttribute('created_at', $responseContent);
        $this->assertObjectHasAttribute('updated_at', $responseContent);
        $this->assertSame(1, $responseContent->id);
        $this->assertSame('desc 1', $responseContent->desc);

        $response = $this->callProtected('DELETE', $this->getResourceUrl() . '' . uniqid());

        $this->assertEquals(404, $response->getStatusCode());
    } // function

    /**
     * Checks if an 403 is returned, if the key is from another user.
     * @return void
     */
    public function testDestroyWrongUser()
    {
        $this->seed('APIKeySeeder');

        $response = $this->callProtected(
            'DELETE',
            $this->getResourceUrl() . '1',
            [],
            [],
            [],
            [],
            [],
            ['email' => 'test@example.com', 'password' => 'password']
        );

        $this->assertEquals(403, $response->getStatusCode());
    } // function

    /**
     * Checks if an 200 is returned, if the key is from another user but the user is admin.
     * @return void
     */
    public function testDestroyWrongUserButAdmin()
    {
        $this->seed('APIKeySeeder');

        $key = APIKey::findOrNew(1);
        $key->user_id = 2;
        $key->save();

        $response = $this->callProtected('DELETE', '/api/api_keys/1');
        $this->assertEquals(200, $response->getStatusCode());
    } // function

    /**
     * Checks if the correct status code is returned.
     * @return void
     */
    public function testIndexErrorNoUserFilter()
    {
        $response = $this->callProtected(
            'GET',
            $this->getResourceUrl(),
            [],
            [],
            [],
            [],
            [],
            ['email' => 'test@example.com', 'password' => 'password']
        );

        $this->assertEquals(400, $response->getStatusCode());
    } // function

    /**
     * Checks if the correct status code is returned.
     * @return void
     */
    public function testIndexErrorWrongUserFilter()
    {
        $response = $this->callProtected(
            'GET',
            $this->getResourceUrl(),
            $this->getUserFilter(false),
            [],
            [],
            [],
            [],
            ['email' => 'test@example.com', 'password' => 'password']
        );

        $this->assertEquals(403, $response->getStatusCode());
    } // function

    /**
     * Checks if the correct status code is returned.
     * @return void
     */
    public function testIndexSuccessCorrectUserFilter()
    {
        $response = $this->callProtected(
            'GET',
            $this->getResourceUrl(),
            ['filter' => ['user_id' => 2]],
            [],
            [],
            [],
            [],
            [],
            ['email' => 'test@example.com', 'password' => 'password']
        );

        $this->assertEquals(200, $response->getStatusCode());
    } // function

    /**
     * Checks if the correct result is rendered with no rows.
     * @return void
     */
    public function testIndexSuccessNoPagination()
    {
        $this->seed('APIKeySeeder');

        $test = $this;
        $response = $this->callProtected('GET', $this->getResourceUrl(), $this->getUserFilter(true));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('stdClass', $responseContent = json_decode($response->getContent()));

        $this->checkHAL(
            $responseContent,$this->getRouteName(), 0, 30, true, $this->getUserFilter(true),
            function ($index, stdClass $row) use ($test) {
                $test->assertObjectHasAttribute('id', $row, 'Row misses id.');
                $test->assertObjectHasAttribute('user_id', $row, 'Row misses user_id.');
                $test->assertObjectHasAttribute('desc', $row, 'Row misses desc.');
                $test->assertObjectHasAttribute('created_at', $row, 'Row misses created_at.');
                $test->assertObjectHasAttribute('updated_at', $row, 'Row misses updated_at.');
                $this->assertObjectNotHasAttribute('deleted_at', $row, 'Row should hide deleted_at.');
                $this->assertObjectNotHasAttribute('hash', $row, 'Row should hide hash.');

                $this->assertSame(10 - $index, $row->id, 'Wrong id.');
                $this->assertSame(1, $row->user_id, 'Wrong user_id.');
                $this->assertSame('desc ' . (10 - $index), $row->desc, 'Wrong desc.');
            }
        );
    } // function

    /**
     * Checks if the correct result is rendered with no rows.
     * @return void
     */
    public function testIndexSuccessNoRows()
    {
        DB::table('api_keys')->truncate();

        $response = $this->callProtected('GET', $this->getResourceUrl(), $this->getUserFilter(true));

        $this->assertEquals(404, $response->getStatusCode());
    } // function

    /**
     * Checks if the correct status code is returned.
     * @return void
     */
    public function testIndexSuccessNoUserFilter()
    {
        $this->seed('APIKeySeeder');

        $response = $this->callProtected('GET', $this->getResourceUrl());

        $this->assertEquals(200, $response->getStatusCode());
    } // function

    /**
     * Checks if the correct result is rendered with rows.
     * @return void
     */
    public function testIndexSuccessPagination()
    {
        $this->seed('APIKeySeeder');

        $test = $this;
        $response = $this->callProtected(
            'GET',
            $this->getResourceUrl(),
            array_merge($this->getUserFilter(true), ['limit' => 2, 'skip' => 2, 'sorting' => ['id' => 'asc']])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('stdClass', $responseContent = json_decode($response->getContent()));

        $this->checkHAL(
            $responseContent,
           $this->getRouteName(),
            2,
            2,
            true,
            array_merge($this->getUserFilter(true), ['limit' => 2, 'skip' => 2, 'sorting' => ['id' => 'asc']]),
            function ($index, stdClass $row) use ($test) {
                $test->assertObjectHasAttribute('id', $row, 'Row misses id.');
                $test->assertObjectHasAttribute('user_id', $row, 'Row misses user_id.');
                $test->assertObjectHasAttribute('desc', $row, 'Row misses desc.');
                $test->assertObjectHasAttribute('created_at', $row, 'Row misses created_at.');
                $test->assertObjectHasAttribute('updated_at', $row, 'Row misses updated_at.');
                $this->assertObjectNotHasAttribute('deleted_at', $row, 'Row should hide deleted_at.');
                $this->assertObjectNotHasAttribute('hash', $row, 'Row should hide hash.');

                $this->assertSame(3 + $index, $row->id, 'Wrong id.');
                $this->assertSame(1, $row->user_id, 'Wrong user_id.');
                $this->assertSame('desc ' . (3 + $index), $row->desc, 'Wrong desc.');
            }
        );
    } // function

    /**
     * Checks if an access key is returned.
     * @return void
     */
    public function testShowErrorWrongUser()
    {
        $this->seed('APIKeySeeder');

        $response = $this->callProtected(
            'GET',
            $this->getResourceUrl() . '1',
            [],
            [],
            [],
            [],
            [],
            ['email' => 'test@example.com', 'password' => 'password']
        );

        $this->assertEquals(403, $response->getStatusCode());
    } // function

    /**
     * Checks if an 404 is returned, if no matching id is given.
     * @return void
     */
    public function testShowMissing()
    {
        $response = $this->callProtected('GET', $this->getResourceUrl() . '' . uniqid());

        $this->assertEquals(404, $response->getStatusCode());
    } // function

    /**
     * Checks if an access key is returned.
     * @return void
     */
    public function testShowSuccess()
    {
        $this->seed('APIKeySeeder');

        $response = $this->callProtected('GET', $this->getResourceUrl() . '1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('stdClass', $responseContent = json_decode($response->getContent()));
        $this->assertObjectNotHasAttribute('deleted_at', $responseContent);
        $this->assertObjectNotHasAttribute('hash', $responseContent);
        $this->assertObjectHasAttribute('created_at', $responseContent);
        $this->assertObjectHasAttribute('updated_at', $responseContent);
        $this->assertSame(1, $responseContent->id);
        $this->assertSame('desc 1', $responseContent->desc);
    } // function

    /**
     * Checks if an access key is returned.
     * @return void
     */
    public function testShowSuccessWrongButAdmin()
    {
        $this->seed('APIKeySeeder');

        $key = APIKey::findOrNew(1);
        $key->user_id = 2;
        $key->save();

        $response = $this->callProtected('GET', $this->getResourceUrl() . '1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('stdClass', $responseContent = json_decode($response->getContent()));
        $this->assertObjectNotHasAttribute('deleted_at', $responseContent);
        $this->assertObjectNotHasAttribute('hash', $responseContent);
        $this->assertObjectHasAttribute('created_at', $responseContent);
        $this->assertObjectHasAttribute('updated_at', $responseContent);
        $this->assertSame(1, $responseContent->id);
        $this->assertSame('desc 1', $responseContent->desc);
    } // function

    /**
     * Checks the  store call without an user.
     * @return void
     */
    public function testStoreErrorNoToken()
    {
        $response = $this->call('POST', $this->getResourceUrl());

        $this->assertEquals(401, $response->getStatusCode());
    } // function

    /**
     * Checks the  store call without an user.
     * @return void
     */
    public function testStoreErrorNoUser()
    {
        $response = $this->callProtected('POST', $this->getResourceUrl());

        $this->assertEquals(400, $response->getStatusCode());
    } // function

    /**
     * Checks the  store call without an user.
     * @return void
     */
    public function testStoreErrorWrongToken()
    {
        $server['HTTP_X-' . str_replace(['/', '\\'], '', $this->getAppNamespace()) . '-ACCESS-TOKEN'] = uniqid();

        $response = $this->call('POST', $this->getResourceUrl(), [], [], [], $server);

        $this->assertEquals(403, $response->getStatusCode());
    } // function

    /**
     * Checks the store call with a wrong user.
     * @return void
     */
    public function testStoreErrorWrongUser()
    {
        $response = $this->callProtected(
            'POST',
            $this->getResourceUrl(),
            ['userId' => 2424243],
            [],
            [],
            [],
            [],
            ['email' => 'test@example.com', 'password' => 'password']
        );

        $this->assertEquals(403, $response->getStatusCode());
    } // function

    /**
     * Checks if the correct header is sent without a key.
     * @return void
     */
    public function testStoreNoKey()
    {
        $response = $this->call('POST', $this->getResourceUrl());

        $this->assertEquals(401, $response->getStatusCode());
    } // function

    /**
     * Checks the successfull store call.
     * @return void
     */
    public function testStoreSuccessNoDesc()
    {
        $response = $this->callProtected('POST', $this->getResourceUrl(), ['userId' => 1]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('stdClass', $responseContent = json_decode($response->getContent()));
        $this->assertObjectNotHasAttribute('deleted_at', $responseContent);
        $this->assertObjectNotHasAttribute('hash', $responseContent);
        $this->assertObjectHasAttribute('created_at', $responseContent);
        $this->assertObjectHasAttribute('updated_at', $responseContent);
        $this->assertTrue((bool)$responseContent->id);
        $this->assertTrue((bool)$responseContent->desc);
        $this->assertInternalType('integer', $responseContent->id);
        $this->asserTtrue((bool)$responseContent->key);
    } // function

    /**
     * Checks the successfull store call.
     * @return string
     */
    public function testStoreSuccessWithDesc()
    {
        $response = $this->callProtected('POST', $this->getResourceUrl(), ['desc' => $desc = uniqid(), 'userId' => 1]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf('stdClass', $responseContent = json_decode($response->getContent()));
        $this->assertObjectNotHasAttribute('deleted_at', $responseContent);
        $this->assertObjectNotHasAttribute('hash', $responseContent);
        $this->assertObjectHasAttribute('created_at', $responseContent);
        $this->assertObjectHasAttribute('updated_at', $responseContent);
        $this->assertTrue((bool)$responseContent->id);
        $this->assertInternalType('integer', $responseContent->id);
        $this->assertSame($desc, $responseContent->desc);
        $this->assertTrue((bool)$responseContent->key);

        return $desc;
    } // function

    /**
     * Checks if there is the conflict code for doubled descs.
     * @depends testStoreSuccessWithDesc
     * @param string $desc
     * @return void
     */
    public function testStoreConflict($desc)
    {
        $response = $this->callProtected('POST', $this->getResourceUrl(), ['desc' => $desc, 'userId' => 1]);

        $this->assertEquals(409, $response->getStatusCode());
    } // function

    /**
     * Checks the store call with a wrong user.
     * @return void
     */
    public function testStoreSuccessWrongUserButAdmin()
    {
        $this->seed('UserTableSeeder');

        $response = $this->callProtected('POST', $this->getResourceUrl(), ['userId' => 2]);

        $this->assertEquals(200, $response->getStatusCode());
    } // function

    /**
     * Checks that there is no update routine.
     * @return void
     */
    public function testUpdateMissing()
    {
        $response = $this->call('PUT', $this->getResourceUrl());

        $this->assertEquals(405, $response->getStatusCode());
    } // function
}
