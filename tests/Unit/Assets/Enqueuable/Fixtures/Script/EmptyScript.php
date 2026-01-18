<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;

class EmptyScript
{
    use Enqueuable;

    public function js(): array
    {
        return [];
    }

    public function css(): array
    {
        return [];
    }
}
