<?php

declare(strict_types=1);

namespace themes\Wordpress\Framework\Core\Admin\Notices;

use themes\Wordpress\Framework\Core\Admin\Notices\Types\Interfaces\NoticeTypeInterface;
use InvalidArgumentException;
use ReflectionClass;

/**
 * Handle notice message on the admin page
 */
class Notice
{
    /**
     * The message you want to see in the back office
     *
     * @var string
     */
    private string $message;

    /**
     * The type of the notice, class should implements NoticeTypeInterface
     *
     * @var string|NoticeTypeInterface|class-string<NoticeTypeInterface>
     */
    private $noticeType;

    /**
     * Create an admin notice
     *
     * @param string $messageInEnglish The message you want to see in the back office
     * @param string $noticeType       The type of the notice, class should implements NoticeTypeInterface
     *
     * @throws \ReflectionException
     * @throws InvalidArgumentException
     */
    public function __construct(string $messageInEnglish, string $noticeType)
    {
        $this->message = $messageInEnglish;

        if (!class_exists($noticeType)) {
            throw new InvalidArgumentException("$noticeType needs to be a class.");
        }

        if (!(new ReflectionClass($noticeType))->implementsInterface(NoticeTypeInterface::class)) {
            throw new InvalidArgumentException("$noticeType should implements NoticeTypeInterface.");
        }

        $this->noticeType = $noticeType;
    }

    /**
     * Render the notice message in the back office
     *
     * @return void
     */
    public function render(): void
    {
        add_action('admin_notices', function (): void {
            echo $this->html();
        });
    }

    /**
     * Echo HTML of the notice
     *
     * @return string
     */
    public function html(): string
    {
        return "
			<div class='notice {$this->noticeType::getClassName()}'>
			    <p>{$this->getMessage()}</p>
			</div>
			";
    }

    /**
     * Get the message
     *
     * @return string
     */
    private function getMessage(): string
    {
        return __($this->message, 'wp-framework-core');
    }
}
