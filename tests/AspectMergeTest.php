<?php

/**
 * AspectMergeTest.php
 */
class AspectMergeTest extends \AspectTestCase
{
    /** @var \Picpay\LaravelAspect\AspectManager $manager */
    protected $manager;

    protected static $instance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new \Picpay\LaravelAspect\AspectManager($this->app);
        $this->resolveManager();
    }

    public function testCacheAspects()
    {
        /** @var \__Test\AspectMerge $cache */
        $cache = $this->app->make(\__Test\AspectMerge::class);
        $cache->caching(1);
        $result = $this->app['cache']->tags(['testing1', 'testing2'])->get('caching:1');
        $this->assertNull($result);
    }

    /**
     *
     */
    protected function resolveManager()
    {
        /** @var \Picpay\LaravelAspect\RayAspectKernel $aspect */
        $aspect = $this->manager->driver('ray');
        $aspect->register(\__Test\CacheableModule::class);
        $aspect->register(\__Test\CacheEvictModule::class);
        $aspect->weave();
    }
}
