<?php
namespace Phramz\Staticfiles\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Phramz\Staticfiles\HttpServer as BaseHttpServer;

class HttpServer extends BaseHttpServer
{
    /**
     * @var HttpKernelInterface
     */
    protected $app = null;

    /**
     * @var bool
     */
    protected $ignoreNotFound = false;

    /**
     * @param HttpKernelInterface $app the next app
     * @param string $webroot the root-folder from which files are served
     * @param string $defaultMimetype the mime type for non-guessable file mimes (default: 'application/octed-stream')
     * @param array $excludeExt array of file extension that are ignored (default: ['php'])
     * @param bool $ignoreNotFound if true requests to non existing ressources will be passed
     *                             to the next app in stack. (default: true)
     */
    public function __construct(
        HttpKernelInterface $app,
        $webroot,
        $defaultMimetype = 'application/octed-stream',
        array $excludeExt = ['php'],
        $ignoreNotFound = true
    ) {
        parent::__construct($webroot, $defaultMimetype, $excludeExt);

        $this->app = $app;
        $this->ignoreNotFound = $ignoreNotFound;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $response = parent::handle($request, $type, $catch);

        if ($this->ignoreNotFound && $response->getStatusCode() == Response::HTTP_NOT_FOUND) {
            $this->logger->debug(
                'passing request to the next app, due to 404 response',
                ['uri' => $request->getRequestUri()]
            );

            return $this->app->handle($request, $type, $catch);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(Request $request, Response $response)
    {
        parent::terminate($request, $response);

        if ($this->app instanceof TerminableInterface) {
            $this->app->terminate($request, $response);
        }
    }
}
