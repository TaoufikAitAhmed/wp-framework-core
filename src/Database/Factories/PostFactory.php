<?php

namespace themes\Wordpress\Framework\Core\Database\Factories;

use themes\Wordpress\Framework\Core\Database\Concerns\CurrentDate;
use themes\Wordpress\Framework\Core\Database\Factories\Concerns\HasAcf;
use themes\Wordpress\Framework\Core\Database\Factories\Concerns\HasImage;
use Rareloop\Lumberjack\Post;
use WP_Error;
use WP_Term;

class PostFactory extends Factory
{
    use CurrentDate;
    use HasAcf;
    use HasImage;

    /**
     * Raw attributes.
     *
     * @var array|null
     */
    protected ?array $properties = null;

    /**
     * The post.
     *
     * @var int|WP_Error|null
     */
    protected $post = null;

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
            'post_type'         => 'post',
            'post_status'       => 'publish',
            'post_name'         => $slug,
            'post_title'        => $title,
            'post_date'         => $this->now(),
            'post_date_gmt'     => $this->now(),
            'post_modified'     => $this->now(),
            'post_modified_gmt' => $this->now(),
            'post_content'      => $this->faker->realText(2000),
            'post_excerpt'      => $this->faker->realText(),
            'post_thumbnail'    => 'https://unsplash.it/1140/768/?random',
        ];
    }

    /**
     * Create a factory with a random category.
     *
     * @return PostFactory
     */
    public function forRandomCategory(): self
    {
        return $this->state(function () {
            return [
                'post_category' => [
                    collect(get_categories(['hide_empty' => false]))
                        ->map(fn (WP_Term $term) => $term->term_id)
                        ->values()
                        ->random(),
                ],
            ];
        });
    }

    /**
     * Generates entry of post.
     *
     * @return Post
     */
    public function generate(): Post
    {
        $this->properties = $this->getRawAttributes();
        $this->post = wp_insert_post($this->properties);

        add_post_meta($this->post, '_wp-framework-core-fake', true);

        if (isset($this->properties['post_meta']) && is_array($this->properties['post_meta'])) {
            foreach ($this->properties['post_meta'] as $key => $value) {
                add_post_meta($this->post, $key, $value);
            }
        }

        $this->addThumbnail();

        // Save ACF fields
        $this->saveAcfFields($this->post, $this->properties);

        return new Post($this->post);
    }

    /**
     * Add thumbnail image to the post.
     */
    protected function addThumbnail()
    {
        if (isset($this->properties['post_thumbnail'])) {
            $image = $this->generateImage($this->properties['post_thumbnail'], $this->post);

            if ($image) {
                set_post_thumbnail($this->post, $image);
            }
        }
    }
}
