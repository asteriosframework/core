<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Support;

final readonly class CachePayload
{
    public function __construct(
        public mixed $value,
        public ?int $expiresAt = null,
        public array $tagVersions = [],
    ) {
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt <= time();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'expires_at' => $this->expiresAt,
            'tag_versions' => $this->tagVersions,
        ];
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'] ?? null,
            expiresAt: $data['expires_at'] ?? null,
            tagVersions: $data['tag_versions'] ?? [],
        );
    }
}
