<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;

class ScriptWithData
{
    use Enqueuable;

    public function js(): array
    {
        return [
            'main' => [
                'path' => 'js/app.js',
                'data' => [
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    public function css(): array
    {
        return [];
    }
}
