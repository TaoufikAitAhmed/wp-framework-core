<?php

namespace themes\Wordpress\Framework\Core\Acf\Contracts;

interface Block
{
    /**
     * Data to be passed to the rendered block view.
     *
     * @return array
     */
    public function with();
}
