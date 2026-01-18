<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers\Fixtures\SubNameSpace1;

use Rareloop\Lumberjack\Post;

class CustomPostInSubNameSpace1 extends Post
{
    public static function getPostType()
    {
        return 'custom_post_1';
    }

    protected static function getPostTypeConfig()
    {
        return [
            'not' => 'empty',
        ];
    }
}
