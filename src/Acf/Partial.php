<?php

declare(strict_types=1);

namespace themes\Wordpress\Framework\Core\Acf;

abstract class Partial extends Composer
{
    /**
     * Compose and register the defined field groups with ACF.
     *
     * @return array|\StoutLogic\AcfBuilder\FieldsBuilder|void
     */
    public function compose()
    {
        $fields = $this->fields();

        if (empty($fields)) {
            return;
        }

        return $fields;
    }
}
