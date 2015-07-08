<?php
namespace CC\Http\Controllers\Users;

use CC\Like;
use TestCase;

class LikeControllerTest extends TestCase
{
    /**
     * Checks if the delete is aborted, if there is no like to delete.
     */
    public function testDestroyErrorMissingRow()
    {
        $response = $this->callProtected('DELETE', 'api/users/123423/like');

        $this->assertSame(404, $response->getStatusCode());
    } // function

    /**
     * Checks if the like is aborted with 401 if there is no login.
     * @return void
     */
    public function testDestroyErrorNoLogin()
    {
        $response = $this->call('DELETE', 'api/users/1/like');

        $this->assertSame(401, $response->getStatusCode());
    } // function

    /**
     * Checks if 404 is returned, if the users does not exist.
     * @return void
     */
    public function testDestroyErrorUserNotFound()
    {
        $response = $this->call('DELETE', 'api/users/' . uniqid() . '/like');

        $this->assertSame(404, $response->getStatusCode());
    } // function

    /**
     * Checks if an error is returned, if the destroy comes from the wrong from parameter.
     * @return void
     */
    public function testDestroyErrorWrongFrom()
    {
        $response = $this->callProtected(
            'DELETE',
            'api/users/1/like?from=1',
            [],
            [],
            [],
            [],
            [],
            ['email' => 'test@example.com', 'password' => 'password']
        );

        $this->assertSame(403, $response->getStatusCode());
    } // function

    /**
     * Checks if an error is not returned, if the destroy comes from the wrong from parameter but we are admin.
     * @return void
     */
    public function testDestroySuccessWrongFrom()
    {
        $response = $this->callProtected('DELETE', 'api/users/1/like?from=2');

        $this->assertNotSame(403, $response->getStatusCode());
    } // function

    /**
     * Checks if the like for myself is aborted.
     * @return void
     */
    public function testStoreErrorNoLikeForMyself()
    {
        $response = $this->callProtected('POST', 'api/users/1/like');

        $this->assertSame(403, $response->getStatusCode());
    } // function


    /**
     * Checks if the like is aborted, if there is no login.
     * @return void
     */
    public function testStoreErrorNoLogin()
    {
        $response = $this->call('POST', 'api/users/1/like');

        $this->assertSame(401, $response->getStatusCode());
    } // function

    /**
     * An error should be returned, if the normal user sets a wrong form value.
     * @return mixed
     */
    public function testStoreErrorWrongForm()
    {
        Like::truncate();

        $response = $this->callProtected(
            'POST',
            'api/users/2/like?from=1',
            [],
            [],
            [],
            [],
            [],
            ['email' => 'test@example.com', 'password' => 'password']
        );

        $this->assertSame(403, $response->getStatusCode());
    } // function

    /**
     * Checks the success of the login without a special from parameter.
     * @return \stdClass
     */
    public function testStoreSuccessNoFrom()
    {
        Like::truncate();

        $response = $this->callProtected('POST', 'api/users/2/like');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty($contentText = $response->getContent());
        $this->assertInstanceOf('stdClass', $contentObject = json_decode($contentText));
        $this->assertObjectHasAttribute('id', $contentObject);
        $this->assertObjectHasAttribute('target_id', $contentObject);
        $this->assertObjectHasAttribute('target_type', $contentObject);
        $this->assertObjectHasAttribute('user_id', $contentObject);
        $this->assertObjectNotHasAttribute('deleted_at', $contentObject);

        $this->assertSame(1, $contentObject->user_id);
        $this->assertSame(2, $contentObject->target_id);
        $this->assertSame('users', $contentObject->target_type);

        $this->assertTrue(Like::find($contentObject->id)->exists());

        return $contentObject;
    } // function

    /**
     * Checks if the doubled like is aborted with a 409.
     * @depends testStoreSuccessNoFrom
     * @param \stdClass $like
     * @return \stdClass
     */
    public function testStoreErrorDuplicate(\stdClass $like)
    {
        $response = $this->callProtected('POST', 'api/users/2/like');

        $this->assertSame(409, $response->getStatusCode());

        return $like;
    } // function

    /**
     * Checks if the delete is aborted, if there is no like to delete.
     * @return void
     */
    public function testStoreErrorMissingRow()
    {
        $response = $this->callProtected('POST', 'api/users/1123123213/like');

        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * Checks if the like is destroyed correctly.
     * @depends testStoreSuccessNoFrom
     * @param \stdClass $like
     * @return void
     */
    public function testDestroySuccess(\stdClass $like)
    {
        $this->assertTrue(Like::find($like->id)->exists());
        $response = $this->callProtected('DELETE', 'api/users/2/like');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull(Like::find($like->id));
    } // function

    /**
     * Checks the success of the login with a special from param.
     * @return \stdClass
     */
    public function testStoreSuccessWrongFrom()
    {
        Like::truncate();

        $response = $this->callProtected('POST', 'api/users/1/like?from=2');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty($contentText = $response->getContent());
        $this->assertInstanceOf('stdClass', $contentObject = json_decode($contentText));
        $this->assertObjectHasAttribute('id', $contentObject);
        $this->assertObjectHasAttribute('target_id', $contentObject);
        $this->assertObjectHasAttribute('target_type', $contentObject);
        $this->assertObjectHasAttribute('user_id', $contentObject);
        $this->assertObjectNotHasAttribute('deleted_at', $contentObject);

        $this->assertSame(2, $contentObject->user_id);
        $this->assertSame(1, $contentObject->target_id);
        $this->assertSame('users', $contentObject->target_type);

        $this->assertTrue(Like::find($contentObject->id)->exists());

        return $contentObject;
    } // function
}
