<?php

declare(strict_types=1);

namespace themes\Wordpress\Framework\Core\Admin\Notices\Types;

use themes\Wordpress\Framework\Core\Admin\Notices\Types\Interfaces\NoticeTypeInterface;

abstract class Warning implements NoticeTypeInterface
{
    /**
     * @inheritDoc
     */
    public static function getClassName(): string
    {
        return 'notice-warning';
    }
}
