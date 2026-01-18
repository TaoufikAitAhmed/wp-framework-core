<?php

namespace themes\Wordpress\Framework\Core\Database\Factories;

use Rareloop\Lumberjack\Page;

class PageFactory extends PostFactory
{
    /**
     * Define the post default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();
        $slug = sanitize_title($title);

        return [
            'post_author'       => 1,
            'post_type'         => 'page',
            'post_status'       => 'publish',
            'post_name'         => $slug,
            'post_title'        => $title,
            'post_date'         => $this->now(),
            'post_date_gmt'     => $this->now(),
            'post_modified'     => $this->now(),
            'post_modified_gmt' => $this->now(),
            'post_content'      => $this->faker->realText(2000),
            'page_template'     => null,
            'post_thumbnail'    => null,
        ];
    }

    /**
     * Set the title of the page.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        return $this->state(function () use ($title) {
            return [
                'post_name'  => sanitize_title($title),
                'post_title' => $title,
            ];
        });
    }

    /**
     * Set the page template.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPageTemplate(string $path): self
    {
        return $this->state(function () use ($path) {
            return [
                'page_template' => $path,
            ];
        });
    }

    /**
     * Generates entry of post.
     *
     * @return Page
     */
    public function generate(): Page
    {
        $this->properties = $this->getRawAttributes();
        $this->post = wp_insert_post($this->properties);

        add_post_meta($this->post, '_wp-framework-core-fake', true);

        if (isset($this->properties['page_meta']) && is_array($this->properties['page_meta'])) {
            foreach ($this->properties['page_meta'] as $key => $value) {
                add_post_meta($this->post, $key, $value);
            }
        }

        $this->addThumbnail();

        // Save ACF fields
        $this->saveAcfFields($this->post, $this->properties);

        return new Page($this->post);
    }
}
