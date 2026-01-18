<?php

declare(strict_types=1);

namespace themes\Wordpress\Framework\Core\Acf;

use themes\Wordpress\Framework\Core\Admin\Notices\Notice;
use themes\Wordpress\Framework\Core\Admin\Notices\Types\Error;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Rareloop\Lumberjack\Post;
use ReflectionClass;

abstract class Options extends Composer
{
    /**
     * The option page menu name.
     *
     * @var string
     */
    public string $name = '';

    /**
     * The option page menu slug.
     *
     * @var string
     */
    public string $slug = '';

    /**
     * The option page document title.
     *
     * @var string
     */
    public string $title = '';

    /**
     * The option page permission capability.
     *
     * @var string
     */
    public string $capability = 'edit_theme_options';

    /**
     * The option page menu position.
     *
     * @var int|string
     */
    public $position = '';

    /**
     * The slug of another admin page to be used as a parent.
     *
     * @var string|null
     */
    public ?string $parent = null;

    /**
     * The option page menu icon.
     *
     * @var string|null
     */
    public ?string $icon = null;

    /**
     * Redirect to the first child page if one exists.
     *
     * @var bool
     */
    public bool $redirect = true;

    /**
     * The post ID to save and load values from.
     *
     * @var string|int
     */
    public $post = 'options';

    /**
     * The option page autoload setting.
     *
     * @var bool
     */
    public bool $autoload = true;

    /**
     * Compose and register the defined ACF field groups.
     *
     * @return Options|void
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function compose()
    {
        if (!$this->isAcfProLoaded()) {
            $acfProNotLoaded = new Notice('Please install Advanced Custom Fields Pro, it is required to add options pages.', Error::class);
            $acfProNotLoaded->render();
        } else {
            if (empty($this->name)) {
                return;
            }

            if (empty($this->slug)) {
                $this->slug = Str::slug($this->name);
            }

            if (empty($this->title)) {
                $this->title = $this->name;
            }

            if ($this->parent && class_exists($this->parent) && !(new ReflectionClass($this->parent))->isAbstract()) {
                if (!is_subclass_of($this->parent, self::class) && !is_subclass_of($this->parent, Post::class)) {
                    throw new InvalidArgumentException("The parent page {$this->parent} need to extends Options class.");
                }

                $parent = $this->app->has($this->parent) ? $this->app->get($this->parent) : $this->app->make($this->parent);

                if (is_subclass_of($parent, Post::class)) {
                    $this->parent = "edit.php?post_type={$parent::getPostType()}";
                } else {
                    if ($parent->parent) {
                        $primaryOptionPage = get_class($this);

                        throw new InvalidArgumentException("The {$primaryOptionPage} option page cannot have a parent who has a parent.");
                    }

                    $this->parent = $parent->slug ?: Str::slug($parent->name);

                    if (!$this->parent || empty(trim($this->parent))) {
                        throw new InvalidArgumentException('You need to set a correct slug for the parent page.');
                    }
                }
            } // end if

            if (!Arr::has($this->fields, 'location.0.0')) {
                Arr::set($this->fields, 'location.0.0', [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => $this->slug,
                ]);
            }

            $this->register(function (): void {
                acf_add_options_page([
                    'menu_title'      => $this->name,
                    'menu_slug'       => $this->slug,
                    'page_title'      => $this->title,
                    'capability'      => $this->capability,
                    'position'        => $this->position,
                    'parent_slug'     => $this->parent,
                    'icon_url'        => $this->icon,
                    'redirect'        => $this->redirect,
                    'post_id'         => $this->post,
                    'autoload'        => $this->autoload,
                    'update_button'   => $this->updateButton(),
                    'updated_message' => $this->updatedMessage(),
                ]);
            });

            return $this;
        } // end if
    }

    /**
     * Localized text displayed on the submit button.
     *
     * @return string
     */
    public function updateButton(): string
    {
        return __('Update', 'acf');
    }

    /**
     * Localized text displayed after form submission.
     *
     * @return string
     */
    public function updatedMessage(): string
    {
        return __('Options Updated', 'acf');
    }

    /**
     * Is ACF Pro loaded ?
     *
     * @return bool
     */
    private function isAcfProLoaded(): bool
    {
        return function_exists('acf_add_options_page');
    }
}
