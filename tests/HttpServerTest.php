<?php
namespace Phramz\Staticfiles\Middleware\Tests;

use Phramz\Staticfiles\Middleware\HttpServer;
use Phramz\Staticfiles\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @covers Phramz\Staticfiles\Middleware\HttpServer
 */
class HttpServerTest extends AbstractTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockApp = null;

    protected function setUp()
    {
        $this->mockApp = $this->getMockBuilder('Phramz\Staticfiles\Tests\Mock\AbstractTerminableHttpKernel')
            ->setMethods(['handle', 'terminate'])
            ->getMockForAbstractClass();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(
            'Phramz\Staticfiles\HttpServer',
            new HttpServer($this->mockApp, '/')
        );
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testIgnoreNotFound($uri, $canHandle, $status, $content, $contentType)
    {
        $mockRequest = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->setMethods(['getRequestUri'])
            ->getMockForAbstractClass();

        $mockRequest->expects($this->any())
            ->method('getRequestUri')
            ->will($this->returnValue($uri));

        $app = new HttpServer($this->mockApp, static::getFixturesDirectory(), 'default', ['someext'], true);

        if (!$canHandle) {
            $this->mockApp->expects($this->once())
                ->method('handle')
                ->with($mockRequest, HttpKernelInterface::MASTER_REQUEST, true);
        }

        $response = $app->handle($mockRequest);

        if ($canHandle) {
            $this->assertEquals($status, $response->getStatusCode());
            $this->assertEquals($content, $response->getContent());
            if (null !== $contentType) {
                $this->assertEquals($contentType, $response->headers->get('Content-type'));
            }
        }
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle($uri, $canHandle, $status, $content, $contentType)
    {
        $mockRequest = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->setMethods(['getRequestUri'])
            ->getMockForAbstractClass();

        $mockRequest->expects($this->any())
            ->method('getRequestUri')
            ->will($this->returnValue($uri));

        $app = new HttpServer($this->mockApp, static::getFixturesDirectory(), 'default', ['someext'], false);

        if (!$canHandle) {
            $this->mockApp->expects($this->never())
                ->method('handle');
        }

        $response = $app->handle($mockRequest);

        if ($canHandle) {
            $this->assertEquals($status, $response->getStatusCode());
            $this->assertEquals($content, $response->getContent());
            if (null !== $contentType) {
                $this->assertEquals($contentType, $response->headers->get('Content-type'));
            }
        } else {
            $this->assertEquals(404, $response->getStatusCode());
        }
    }

    public function testTerminate()
    {
        $request = new Request();
        $response = new Response();

        $app = new HttpServer($this->mockApp, '/');
        $this->mockApp->expects($this->once())
            ->method('terminate')
            ->with($request, $response);

        $app->terminate($request, $response);
    }
}
