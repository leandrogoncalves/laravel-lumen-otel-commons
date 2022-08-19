<?php


namespace __Test;

use Picpay\LaravelAspect\Annotation\LogExceptions;

/**
 * Class AnnotationStub
 * for tests
 * @Resource
 */
class AnnotationStub
{
    /**
     * @LogExceptions
     * @Get
     * @param null $id
     * @return null
     */
    public function testing($id = null)
    {
        return $id;
    }
}
