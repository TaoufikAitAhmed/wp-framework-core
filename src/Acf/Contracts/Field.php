<?php

namespace themes\Wordpress\Framework\Core\Acf\Contracts;

interface Field
{
    /**
     * The field group.
     *
     * @return \StoutLogic\AcfBuilder\FieldsBuilder|array
     */
    public function fields();
}
