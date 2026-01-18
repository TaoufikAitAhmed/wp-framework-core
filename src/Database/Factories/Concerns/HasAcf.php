<?php

namespace themes\Wordpress\Framework\Core\Database\Factories\Concerns;

use themes\Wordpress\Framework\Core\Database\Factories\Factory;
use BadMethodCallException;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

trait HasAcf
{
    use HasImage;

    /**
     * Create a model with some ACF datas.
     *
     * @param array $acf
     *
     * @return $this
     */
    public function withAcf(array $acf): self
    {
        return $this->state(function () use ($acf) {
            return [
                'acf' => collect($acf)->map(function ($acf) {
                    if (is_array($acf)) {
                        throw new BadMethodCallException(
                            sprintf(
                                'Do not call [new] or [make] method from a factory inside the [withAcf] method of %s factory.',
                                get_class($this)
                            )
                        );
                    }

                    try {
                        $reflection = new ReflectionClass($acf);
                    } catch (ReflectionException $e) {
                        throw new InvalidArgumentException("The component [{$acf}] must be a class and a subclass of Factory.");
                    }

                    if (!$reflection->isSubclassOf(Factory::class)) {
                        $acf = get_class($acf);
                        throw new InvalidArgumentException("The component [{$acf}] must be a subclass of Factory.");
                    }

                    return $acf->make();
                })->all(),
            ];
        });
    }

    /**
     * Save ACF Fields to factory.
     *
     * @param string|int $post
     * @param array      $rawAttributes
     *
     * @return void
     */
    protected function saveAcfFields($post, array $rawAttributes): void
    {
        $data = $this->normalizeAcfData($rawAttributes);

        if (!$data || !class_exists('acf')) {
            return;
        }

        foreach ($data as $name => $value) {
            $field = acf_get_field($name);
            if (!$field) {
                continue;
            }

            // If the ACF Field is an image.
            if ($field['type'] === 'image' && $value) {
                update_field($field['key'], $this->generateImage($value), $post);
                continue;
            }

            // If somewhere in an array we still have an '(image)' string, generate the image
            // To ensure developer will have the images generated.
            if (is_array($value)) {
                $this->deepGenerateImage($value);
            }

            update_field($field['key'], $value, $post);
        }
    }

    /**
     * Deep generate recursively an image in an array.
     *
     * @param array $array
     *
     * @return void
     */
    protected function deepGenerateImage(array &$array): void
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->deepGenerateImage($value);
                continue;
            }
            if (!Str::contains($value, '(image)')) {
                continue;
            }
            $value = $this->generateImage($value);
        }
    }

    /**
     * Normalize ACF array.
     *
     * @param array $rawAttributes
     *
     * @return array|null
     */
    protected function normalizeAcfData(array $rawAttributes): ?array
    {
        if (empty($rawAttributes['acf']) || !is_array($rawAttributes['acf'])) {
            return null;
        }

        if (isset($rawAttributes['acf'][0]) && count($rawAttributes['acf']) === 1) {
            return $rawAttributes['acf'][0];
        }

        if (!is_numeric(array_key_first($rawAttributes['acf']))) {
            return $rawAttributes['acf'];
        }

        $acfArray = [];

        foreach ($rawAttributes['acf'] as $key => $value) {
            $acfArray = array_merge($acfArray, $value);
        }

        return $acfArray;
    }
}
