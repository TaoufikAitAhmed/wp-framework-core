<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Style;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;

class StyleWithoutName
{
    use Enqueuable;

    public function js(): array
    {
        return [];
    }

    public function css(): array
    {
        return ['css/app.css'];
    }
}
