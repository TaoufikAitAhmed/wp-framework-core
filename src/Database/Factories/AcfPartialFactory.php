<?php

namespace themes\Wordpress\Framework\Core\Database\Factories;

use themes\Wordpress\Framework\Core\Database\Factories\Concerns\HasImage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use RuntimeException;

abstract class AcfPartialFactory extends Factory
{
    use HasImage;

    /**
     * The ACF Partial linked to this Factory.
     *
     * @var \themes\Wordpress\Framework\Core\Acf\Partial|class-string<\themes\Wordpress\Framework\Core\Acf\Partial>|null
     */
    protected $acfPartial = null;

    /**
     * Raw attributes of the acf partial factory.
     *
     * @var array|null
     */
    protected ?array $properties = null;

    /**
     * Is the acf partial factory inside a flexible content ?
     *
     * @var bool
     */
    protected bool $isInsideFlexibleContent = false;

    public function __construct(?int $count = null, ?Collection $states = null)
    {
        if ($this->acfPartial === null) {
            throw new RuntimeException(sprintf('Property [$acfPartial] is missing from %s.', get_class($this)));
        }
        parent::__construct($count, $states);
    }

    /**
     * Is the TextFactory inside a flexible content ?
     *
     * @return $this
     */
    public function insideFlexibleContent(): self
    {
        $this->isInsideFlexibleContent = true;

        return $this;
    }

    /**
     * Generates entry of text.
     */
    public function generate()
    {
        $properties = $this->getRawAttributes();

        if (!$this->isInsideFlexibleContent) {
            return $properties;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($properties));
        foreach ($iterator as $key => $value) {
            if (!Str::contains($value, '(image)') || !$value) {
                continue;
            }

            // If the value have the '(image)' string, we will generate an image and replace the URL with the ID of the attachment.
            $image = $this->generateImage(trim(Str::replaceLast('(image)', '', $value)));

            // Get the current depth and traverse back up the tree, saving the modifications
            $currentDepth = $iterator->getDepth();
            for ($subDepth = $currentDepth; $subDepth >= 0; $subDepth--) {
                // Get the current level iterator
                $subIterator = $iterator->getSubIterator($subDepth);

                // If we are on the level we want to change, use the replacements ($value) other wise set the key to the parent iterators value
                $subIterator->offsetSet(
                    $subIterator->key(),
                    ($subDepth === $currentDepth ? $image : $iterator->getSubIterator(($subDepth + 1))->getArrayCopy())
                );
            }
        }
        $properties = $iterator->getArrayCopy();

        return array_merge(
            [
                'acf_fc_layout' => app($this->acfPartial)->fields()->getName(),
            ],
            $properties
        );
    }
}
