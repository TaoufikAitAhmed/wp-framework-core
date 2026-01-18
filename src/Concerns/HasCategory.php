<?php

namespace themes\Wordpress\Framework\Core\Concerns;

use Timber\Post;
use Timber\Term;
use WP_Post;

trait HasCategory
{
     /**
     * Get the selected category.
     *
     * @return Term|null
     */
    protected function getSelectedCategory(): ?Term
    {
        $queriedObject = get_queried_object();

        if ($queriedObject instanceof WP_Post) {
            return (new Post($queriedObject->ID))->category();
        }

        return Term::from(get_queried_object()->term_id, 'category');
    }

    /**
     * Get the top category for a category.
     *
     * @param Term $category
     *
     * @return Term
     */
    protected function getTopCategory(Term $category): Term
    {
        $ancestorsCategoryId = get_ancestors($category->ID, 'category');

        if (empty($ancestorsCategoryId)) {
            return $category;
        }

        return Term::from($ancestorsCategoryId[count($ancestorsCategoryId) - 1], 'category');
    }
}
