<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Controller;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;
use themes\Wordpress\Framework\Core\Controller;

class DontEnqueueController extends Controller
{
    use Enqueuable;

    /** @inheritdoc */
    protected bool $enqueue = false;

    public function js(): array
    {
        return [
            'main' => 'js/app.js',
        ];
    }

    public function css(): array
    {
        return [
            'main' => 'css/app.css',
        ];
    }
}
