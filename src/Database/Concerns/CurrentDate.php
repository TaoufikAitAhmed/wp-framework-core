<?php

namespace themes\Wordpress\Framework\Core\Database\Concerns;

trait CurrentDate
{
    /**
     * Gets current datetime.
     *
     * @return string
     */
    public function now(): string
    {
        return gmdate('Y-m-d H:i:s', time());
    }
}
