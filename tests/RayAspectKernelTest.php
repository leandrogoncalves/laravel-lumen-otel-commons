<?php

class RayAspectKernelTest extends \AspectTestCase
{
    /** @var \Picpay\LaravelAspect\RayAspectKernel */
    protected $kernel;
    public function setUp(): void
    {
        parent::setUp();
        $aspectConfigure = $this->app['config']->get('picpay-laravel-aop.aspect.drivers');
        $this->kernel = new \Picpay\LaravelAspect\RayAspectKernel(
            $this->app,
            $this->app['files'],
            $aspectConfigure['ray']
        );
    }

    public function testExceptionCaseNotFoundClassRegister()
    {
        $this->expectException(\Exception::class);
        $this->kernel->register('NotFoundClass');
    }
}

