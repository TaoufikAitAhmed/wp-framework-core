<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Providers\Fixtures\SubNameSpace2;

use Rareloop\Lumberjack\Post;

class CustomPostInSubNameSpace2 extends Post
{
    public static function getPostType()
    {
        return 'custom_post_2';
    }

    protected static function getPostTypeConfig()
    {
        return [
            'not' => 'empty',
        ];
    }
}
