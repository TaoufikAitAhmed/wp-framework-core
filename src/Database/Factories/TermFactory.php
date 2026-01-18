<?php

namespace themes\Wordpress\Framework\Core\Database\Factories;

use themes\Wordpress\Framework\Core\Term;
use WP_Error;

class TermFactory extends Factory
{
    /**
     * Raw attributes.
     *
     * @var array|null
     */
    protected ?array $properties = null;

    /**
     * The term.
     *
     * @var int|WP_Error|null
     */
    protected $term = null;

    /**
     * Define the term default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();
        $slug = sanitize_title($title);

        return [
            'name'        => $title,
            'slug'        => $slug,
            'taxonomy'    => 'category',
            'description' => $this->faker->realText(),
            'parent'      => null,
        ];
    }

    /**
     * Generates entry of term.
     *
     * @return Term
     */
    public function generate(): Term
    {
        $this->properties = $this->getRawAttributes();
        $this->term = wp_insert_term($this->properties['name'], $this->properties['taxonomy'], [
            'description' => $this->properties['description'],
            'parent'      => $this->properties['parent'],
            'slug'        => $this->properties['slug'],
        ]);

        update_term_meta($this->term['term_id'], '_wp-framework-core-fake', true);

        if (isset($this->properties['term_meta']) && is_array($this->properties['term_meta'])) {
            foreach ($this->properties['term_meta'] as $key => $value) {
                update_term_meta($this->term['term_id'], $key, $value);
            }
        }

        return new Term($this->term['term_id']);
    }
}
