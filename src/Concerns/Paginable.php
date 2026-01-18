<?php

namespace themes\Wordpress\Framework\Core\Concerns;

trait Paginable
{
    /**
     * Get paged variable.
     *
     * @return int
     */
    protected function getPaged(): int
    {
        global $paged;

        if (!isset($paged) || !$paged) {
            $paged = 1;
        }

        return $paged;
    }
}
