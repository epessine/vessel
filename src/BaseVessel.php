<?php

namespace Vessel;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use ReflectionClass;
use ReflectionProperty;
use Vessel\Support\PropertyHelper;
use Vessel\Support\VesselHelper;

/**
 * @method void init()
 */
abstract class BaseVessel
{
    private readonly string $cacheKey;

    private int $lifetime;

    public function __construct(private readonly Component $component)
    {
        $this->cacheKey = VesselHelper::cacheKey($this);

        /** @var int $lifetime */
        $lifetime = config('session.lifetime');

        Cache::remember(
            $this->cacheKey,
            $this->lifetime ??= $lifetime,
            function (): array {
                if (method_exists($this, 'init')) {
                    $this->init();
                }

                return $this->toArray();
            },
        );

        foreach (static::getPublicProperties() as $prop) {
            unset($this->{$prop->getName()});
        }
    }

    public function &__get(string $name): mixed
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        $values = $this->getCache();

        return $values[$name];
    }

    public function __set(string $name, mixed $value): void
    {
        Cache::lock($this->cacheKey, 5)->get(function () use ($name, $value): void {
            $values = $this->getCache();

            $values[$name] = $value;

            Cache::set($this->cacheKey, $values, $this->lifetime);

            $this->component->dispatch(PropertyHelper::updatedEventName($name), $value);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function getCache(): array
    {
        /** @var array<string, mixed> $values */
        $values = Cache::get($this->cacheKey);

        return $values;
    }

    /**
     * @return \ReflectionProperty[]
     */
    public static function getPublicProperties(): array
    {
        return (new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC);
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(): array
    {
        return collect(static::getPublicProperties())
            ->mapWithKeys(fn (ReflectionProperty $prop): array => [
                $prop->getName() => $prop->getValue($this),
            ])
            ->all();
    }
}
