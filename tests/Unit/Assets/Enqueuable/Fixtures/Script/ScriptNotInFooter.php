<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;

class ScriptNotInFooter
{
    use Enqueuable;

    public function js(): array
    {
        return [
            'main' => [
                'path' => 'js/app.js',
                'in_footer' => false,
            ],
        ];
    }

    public function css(): array
    {
        return [];
    }
}
