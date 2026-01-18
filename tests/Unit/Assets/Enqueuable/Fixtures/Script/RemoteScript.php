<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Assets\Enqueuable\Fixtures\Script;

use themes\Wordpress\Framework\Core\Assets\Concerns\Enqueuable;

class RemoteScript
{
    use Enqueuable;

    public function js(): array
    {
        return [
            'remote' => 'https://remote.com/plugin/plugin.js',
        ];
    }

    public function css(): array
    {
        return [];
    }
}
