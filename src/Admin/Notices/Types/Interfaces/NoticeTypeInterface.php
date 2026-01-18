<?php

declare(strict_types=1);

namespace themes\Wordpress\Framework\Core\Admin\Notices\Types\Interfaces;

interface NoticeTypeInterface
{
    /**
     * Return the class name of the notice
     *
     * @return string
     */
    public static function getClassName(): string;
}
