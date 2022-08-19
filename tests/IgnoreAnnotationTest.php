<?php

/**
 * Class IgnoreAnnotationTest
 */
class IgnoreAnnotationTest extends AspectTestCase
{
    /** @var \Picpay\LaravelAspect\AspectManager $manager */
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new \Picpay\LaravelAspect\AspectManager($this->app);
        $this->resolveManager();
    }

    public function testGenerateCacheNameRemoveNullKey()
    {
        /** @var \__Test\AnnotationStub $class */
        $class = $this->app->make(\__Test\AnnotationStub::class);
        $this->assertNull($class->testing());
    }


    /**
     *
     */
    protected function resolveManager()
    {
        /** @var \Picpay\LaravelAspect\RayAspectKernel $aspect */
        $aspect = $this->manager->driver('ray');
        $aspect->register(\__Test\LogExceptionsModule::class);
        $aspect->weave();
    }
}
