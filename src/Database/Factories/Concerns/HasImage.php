<?php

namespace themes\Wordpress\Framework\Core\Database\Factories\Concerns;

use Illuminate\Support\Str;

trait HasImage
{
    /**
     * Generate an image.
     *
     * @param string   $imageUrl
     * @param int|null $postId
     *
     * @return int|null
     */
    protected function generateImage(string $imageUrl, ?int $postId = null): ?int
    {
        $imageUrl = $this->normalizeImageUrl($imageUrl);

        $attributes = [
            'tmp_name' => download_url($imageUrl),
            'name'     => sprintf('%s.jpg', Str::uuid()),
            'type'     => 'image/jpg',
        ];

        if (is_wp_error($attributes['tmp_name'])) {
            return null;
        }

        $media = media_handle_sideload($attributes, $postId);

        if (!is_wp_error($media)) {
            update_post_meta($media, '_wp-framework-core-fake', true);

            return $media;
        }

        unlink($attributes['url']);

        return null;
    }

    /**
     * Ensure image url does not have (image) string,
     * as we need it when image is inside flexible content.
     *
     * @param string $imageUrl
     *
     * @return string
     */
    protected function normalizeImageUrl(string $imageUrl): string
    {
        return trim(Str::replaceLast('(image)', '', $imageUrl));
    }
}
