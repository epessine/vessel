<?php

namespace Vessel;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;
use ReflectionClass;
use ReflectionProperty;

/**
 * @method void init()
 */
abstract class BaseVessel
{
    private readonly string $id;

    private int $lifetime;

    public function __construct(private readonly Component $component)
    {
        $this->id = VesselManager::getContextId();

        /** @var int $lifetime */
        $lifetime = config('session.lifetime');

        Cache::remember(
            $this->generateKey(),
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

    public function __get(string $name): mixed
    {
        /** @var array<string, mixed> $values */
        $values = Cache::get($this->generateKey());

        return $values[$name];
    }

    public function __set(string $name, mixed $value): void
    {
        /** @var array<string, mixed> $values */
        $values = Cache::get($this->generateKey());

        $values[$name] = $value;

        Cache::set($this->generateKey(), $values, $this->lifetime);

        $this->component->dispatch($this->getPropertyUpdatedEventName($name), $value);
    }

    private function getPropertyUpdatedEventName(string $name): string
    {
        return Str::of($name)
            ->prepend(
                'vessel-',
                VesselManager::getContextId(),
                '-',
            )
            ->append('-updated')
            ->toString();
    }

    private function generateKey(): string
    {
        return Str::of('vessel_')
            ->append(static::class, '_', $this->id)
            ->toString();
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
        return collect((new ReflectionClass($this))
            ->getProperties(ReflectionProperty::IS_PUBLIC))
            ->mapWithKeys(fn (ReflectionProperty $prop): array => [
                $prop->getName() => $prop->getValue($this),
            ])
            ->all();
    }
}
