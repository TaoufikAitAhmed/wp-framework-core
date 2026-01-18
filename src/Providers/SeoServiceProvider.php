<?php

namespace themes\Wordpress\Framework\Core\Providers;

use themes\Wordpress\Framework\Core\Config;

class SeoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @param Config $config
     *
     * @return void
     */
    public function boot(Config $config)
    {
        $seo = $config->get('seo');

        if ($seo['canonical_urls']) {
            add_action('wp', [$this, 'canonicalUrl'], 10, 3);
        }

        if ($seo['remove_sub_category_from_post_permalink']) {
            add_filter('post_link', [$this, 'removeSubCategoryInPostPermalink'], 10, 3);
        }

        if ($seo['yoast']['fix_comment']) {
            add_filter('wpseo_remove_reply_to_com', '__return_false');
        }

        if ($seo['yoast']['fix_curl']) {
            add_filter('https_local_ssl_verify', '__return_true');
        }
    }

    /**
     * Remove sub category permalink
     *
     * @param $permalink
     * @param $post
     * @param $leavename
     *
     * @return string The permalink for post
     */
    public function removeSubCategoryInPostPermalink($permalink, $post, $leavename = null): string
    {
        if (!gettype($post) == 'post') {
            return $permalink;
        }

        if ($post->post_type === 'post') {
            $cats = get_the_category($post->ID);
            $subcats = [];
            foreach ($cats as $cat) {
                $cat = get_category($cat->term_id);
                if ($cat->parent) {
                    $subcats[] = sanitize_title($cat->name);
                }
            }
            if ($subcats) {
                $permalink = parse_url($permalink)['path'];

                $paths = explode('/', trim($permalink, '/'));
                $postPath = end($paths);
                array_pop($paths);

                if (!empty($paths)) {
                    $paths = [$paths[0], $postPath];

                    $permalink = str_replace('wp/', '', get_site_url(null, implode('/', $paths))) . '/';
                }
            }
        }

        return $permalink;
    }

    /**
     * Canonical URL on paged pages and sub categories
     *
     * @return void
     */
    public function canonicalUrl(): void
    {
        if (get_query_var('cat') && !$this->isSubCategory()) {
            return;
        }

        if ($this->isSubCategory()) {
            $parentCategory = get_queried_object();
            while ($parentCategory->parent) {
                $parentCategory = get_term($parentCategory->parent, 'category');
            }

            // Remove
            remove_action('wp_head', 'rel_canonical');
            add_filter('wpseo_canonical', '__return_false', 10, 1);

            $parentCategoryPermalink = get_category_link($parentCategory);

            add_action('wp_head', function () use ($parentCategoryPermalink) {
                echo sprintf('<link rel="canonical" href="%s" />', "$parentCategoryPermalink");
            });

            return;
        }

        if (is_paged()) {
            // Remove
            remove_action('wp_head', 'rel_canonical');
            add_filter('wpseo_canonical', '__return_false', 10, 1);

            $canonPage = get_pagenum_link();

            add_action('wp_head', function () use ($canonPage) {
                echo sprintf('<link rel="canonical" href="%s" />', "$canonPage");
            });
        }
    }

    /**
     * Is a sub category ?
     *
     * @return bool
     */
    private function isSubCategory(): bool
    {
        $cat = get_query_var('cat');
        if (!$cat) {
            return false;
        }
        $category = get_category($cat);

        return !($category->parent == '0');
    }
}
