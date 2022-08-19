<?php
/**
 * for test
 */

namespace __Test;

use Picpay\LaravelAspect\Annotation\Transactional;

/**
 * Class AspectTransactional
 * @package Test
 */
class AspectTransactionalString
{
    /**
     * @Transactional(value="testing")
     *
     * @return string
     */
    public function start()
    {
        return 'testing';
    }
}
