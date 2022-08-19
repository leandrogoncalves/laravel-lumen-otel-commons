<?php

/**
 * Class PostConstructTest
 */
class PostConstructTest extends \AspectTestCase
{
    /** @var \Picpay\LaravelAspect\AspectManager $manager */
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new \Picpay\LaravelAspect\AspectManager($this->app);
        $this->resolveManager();
    }

    public function testShouldProceedPostConstructSumVariable()
    {
        /** @var \__Test\AspectPostConstruct $class */
        $class = $this->app->make(\__Test\AspectPostConstruct::class, ['a' => 1]);
        $this->assertInstanceOf(\__Test\AspectPostConstruct::class, $class);
        $this->assertSame(2, $class->getA());
    }

    protected function resolveManager()
    {
        /** @var \Picpay\LaravelAspect\RayAspectKernel $aspect */
        $aspect = $this->manager->driver('ray');
        $aspect->register(\__Test\PostConstructModule::class);
        $aspect->weave();
    }
}
