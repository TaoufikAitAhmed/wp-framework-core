<?php

namespace themes\Wordpress\Framework\Core\Acf;

use themes\Wordpress\Framework\Core\Acf\Concerns\InteractsWithPartial;
use themes\Wordpress\Framework\Core\Acf\Contracts\Field as FieldInterface;
use themes\Wordpress\Framework\Core\Admin\Notices\Notice;
use themes\Wordpress\Framework\Core\Admin\Notices\Types\Error;
use themes\Wordpress\Framework\Core\Config;
use Illuminate\Support\Str;
use Rareloop\Lumberjack\Application;
use StoutLogic\AcfBuilder\FieldsBuilder;

abstract class Composer implements FieldInterface
{
    use InteractsWithPartial;

    /**
     * The field keys.
     *
     * @var array
     */
    protected array $keys = ['fields', 'sub_fields', 'layouts'];

    /**
     * The field groups.
     *
     * @var FieldsBuilder|array
     */
    protected $fields;

    /**
     * The default field settings.
     *
     * @var \Illuminate\Support\Collection|array
     */
    protected $defaults = [];

    /**
     * Container of the application.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new Composer instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->defaults = collect($this->app->get(Config::class)->get('acf.defaults'))
            ->merge($this->defaults)
            ->mapWithKeys(static fn ($value, $key) => [Str::snake($key) => $value]);

        // phpcs:disable SlevomatCodingStandard.ControlStructures.AssignmentInCondition.AssignmentInCondition
        // phpcs:disable Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
        if (is_a($this->fields = $this->fields(), FieldsBuilder::class)) {
            $this->fields = $this->fields->build();
        }

        // phpcs:enable

        if (!$this->defaults->has('field_group')) {
            return;
        }

        $this->fields = array_merge($this->fields, $this->defaults->get('field_group'));
    }

    /**
     * @inheritDoc
     */
    public function fields()
    {
        return [];
    }

    /**
     * Register the field group with Advanced Custom Fields.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    protected function register(?callable $callback = null): void
    {
        if (!$this->isAcfLoaded()) {
            $acfProNotLoaded = new Notice('Please install Advanced Custom Fields, it is required to add Fields.', Error::class);
            $acfProNotLoaded->render();
        } else {
            if (empty($this->fields)) {
                return;
            }

            add_filter(
                'init',
                function () use ($callback): void {
                    if ($callback) {
                        $callback();
                    }

                    acf_add_local_field_group($this->build($this->fields));
                },
                20,
            );
        } // end if
    }

    /**
     * Build the field group with the default field type settings.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function build(array $fields = []): array
    {
        return collect($fields)
            ->map(function ($value, $key) {
                if (!Str::contains($key, $this->keys) || (Str::is($key, 'type') && !$this->defaults->has($value))) {
                    return $value;
                }

                return array_map(function ($field) {
                    foreach ($field as $key => $value) {
                        if (Str::contains($key, $this->keys)) {
                            return $this->build($field);
                        }

                        if (!Str::is($key, 'type') || !$this->defaults->has($value)) {
                            continue;
                        }

                        $field = array_merge($this->defaults->get($field['type'], []), $field);
                    }

                    return $field;
                }, $value);
            })
            ->all();
    }

    /**
     * Is ACF plugin loaded
     *
     * @return bool
     */
    private function isAcfLoaded(): bool
    {
        return function_exists('acf_add_local_field_group');
    }
}
