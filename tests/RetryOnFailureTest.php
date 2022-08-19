<?php

/**
 * Class RetryOnFailureTest
 */
class RetryOnFailureTest extends AspectTestCase
{
    /** @var \Picpay\LaravelAspect\AspectManager $manager */
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new \Picpay\LaravelAspect\AspectManager($this->app);
        $this->resolveManager();
    }

    public function testShouldThrowWithRetry()
    {
        $concrete = $this->app->make(\__Test\AspectRetryOnFailure::class);
        try {
            $concrete->call();
        } catch (\Exception $e) {
            $this->assertSame(3, $concrete->counter);
            $concrete->counter = 0;
        }
        try {
            $concrete->call();
        } catch (\Exception $e) {
            $this->assertSame(3, $concrete->counter);
            $concrete->counter = 0;
        }

        try {
            $concrete->ignoreException();
        } catch (\Exception $e) {
            $this->assertSame(1, $concrete->counter);
        }
    }

    protected function resolveManager()
    {
        $aspect = $this->manager->driver('ray');
        $aspect->register(\__Test\RetryOnFailureModule::class);
        $aspect->weave();
    }
}
