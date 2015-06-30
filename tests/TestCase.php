<?php
use Illuminate\Console\AppNamespaceDetectorTrait;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    use AppNamespaceDetectorTrait;

    /**
     * An cached access key.
     * @var string
     */
    protected $access_key = '';

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Call the given protected URI and return the Response.
     * @param  string  $method
     * @param  string  $uri
     * @param  array   $parameters
     * @param  array   $cookies
     * @param  array   $files
     * @param  array   $server
     * @param  string  $content
     * @return \Illuminate\Http\Response
     */
    protected function callProtected(
        $method,
        $uri,
        $parameters = [],
        $cookies = [],
        $files = [],
        $server = [],
        $content = null
    ) {
        $server['HTTP_X-' . str_replace(['/', '\\'], '', $this->getAppNamespace()) . '-ACCESS-TOKEN'] =
            $this->getAccessKey();

        return $this->call($method, $uri, $parameters, $cookies, $files, $server, $content);
    } // function

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Returns an access key.
     * @return mixed
     */
    protected function getAccessKey()
    {
        if (!$this->access_key) {
            $this->seed('UserTableSeeder');

            $response = $this->call('POST', '/api/access_token', ['email' => 'foo@bar.com', 'password' => 'password']);
            $responseContent = $response->getOriginalContent();

            $this->access_key = $responseContent['token']['value'];
        } // if

        return $this->access_key;
    } // function
}
