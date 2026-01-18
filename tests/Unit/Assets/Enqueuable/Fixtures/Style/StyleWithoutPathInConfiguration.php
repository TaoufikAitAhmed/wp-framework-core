<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;

class StyleWithoutPathInConfiguration
{
    use Enqueuable;

    public function js(): array
    {
        return [];
    }

    public function css(): array
    {
        return [
            'main' => ['css/app.cs'],
        ];
    }
}
