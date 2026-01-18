<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;

class DontEnqueueStyle
{
    use Enqueuable;

    public function js(): array
    {
        return [];
    }

    public function css(): array
    {
        return [
            'main' => [
                'path' => 'css/app.css',
                'enqueue' => false,
            ],
            'other' => 'css/other.css',
        ];
    }
}
