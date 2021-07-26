<?php

use App\Kernel;
use App\CacheKernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool)$context['APP_DEBUG']);

    // Wrap the default Kernel with the CacheKernel one in 'prod' environment
    if (true || 'prod' === $kernel->getEnvironment()) {
        $kernel = new CacheKernel($kernel);
    }

    return $kernel;
};
