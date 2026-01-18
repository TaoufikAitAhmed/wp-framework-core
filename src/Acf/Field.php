<?php

declare(strict_types=1);

namespace themes\Wordpress\Framework\Core\Acf;

abstract class Field extends Composer
{
    /**
     * Compose and register the defined field groups with ACF.
     *
     * @return void|Field
     */
    public function compose()
    {
        if (empty($this->fields)) {
            return;
        }

        $this->register();

        return $this;
    }
}
