<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;

class ScriptWithNameAndConfiguration
{
    use Enqueuable;

    public function js(): array
    {
        return [
            'main' => [
                'path' => 'js/app.js',
            ],
        ];
    }

    public function css(): array
    {
        return [];
    }
}
