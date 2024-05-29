<?php

namespace Vessel\Attributes;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportAttributes\Attribute;
use Vessel\BaseVessel;
use Vessel\Exceptions\BadVesselMethodException;
use Vessel\Exceptions\CannotCallVesselDirectlyException;
use Vessel\VesselManager;

use function Livewire\invade;
use function Livewire\off;
use function Livewire\on;
use function Livewire\store;

#[\Attribute]
class Vessel extends Attribute
{
    protected object $requestCachedVessel;

    public function __construct()
    {
    }

    public function boot(): void
    {
        off('__get', $this->handleMagicGet(...));
        on('__get', $this->handleMagicGet(...));

        foreach ($this->getEvents() as $event => $action) {
            store($this->component)->push(
                'listenersFromAttributes',
                method_exists($this->component, $action) ? $action : '$refresh',
                $event,
            );
        }

        $contextHeader = VesselManager::CONTEXT_HEADER;
        $contextId = VesselManager::getContextId();

        store($this->component)->push('scripts', <<<HTML
            <script>
                document.addEventListener('livewire:initialized', () => {
                    Livewire.hook('request', ({
                        options
                    }) => {
                        options.headers['$contextHeader'] = '$contextId';
                    });
                });
            </script>
        HTML, $this->getVesselClass().'-'.$contextId);
    }

    /**
     * @return array<string, string>
     */
    protected function getEvents(): array
    {
        /** @var \ReflectionProperty[] $props */
        $props = $this->getVesselClass()::getPublicProperties();

        return collect($props)
            ->mapWithKeys(fn (\ReflectionProperty $prop): array => [
                $this->getPropertyUpdatedEventName($prop) => $this->getPropertyUpdatedActionName($prop),
            ])
            ->all();
    }

    protected function getPropertyUpdatedEventName(\ReflectionProperty $prop): string
    {
        return Str::of($prop->getName())
            ->prepend(
                'vessel-',
                VesselManager::getContextId(),
                '-',
            )
            ->append('-updated')
            ->toString();
    }

    protected function getPropertyUpdatedActionName(\ReflectionProperty $prop): string
    {
        return Str::of('updated')
            ->append(ucfirst($this->getName()), ucfirst($prop->getName()))
            ->toString();
    }

    public function call(): void
    {
        throw new CannotCallVesselDirectlyException(
            $this->component->getName(),
            $this->getName(),
        );
    }

    protected function handleMagicGet(Component $target, string $property, callable $returnValue): void
    {
        if ($target !== $this->component) {
            return;
        }
        if ($this->generatePropertyName($property) !== $this->getName()) {
            return;
        }

        $returnValue($this->requestCachedVessel ??= $this->getVessel());
    }

    protected function getVesselClass(): string
    {
        return invade($this->component)->{parent::getName()}();
    }

    protected function getVessel(): object
    {
        $class = $this->getVesselClass();

        if (! is_a($class, BaseVessel::class, true)) {
            throw new BadVesselMethodException(
                $this->component->getName(),
                $this->getName(),
            );
        }

        return new $class($this->component);
    }

    public function getName(): string
    {
        return $this->generatePropertyName(parent::getName());
    }

    private function generatePropertyName(string $value): string
    {
        return Str::of($value)->camel()->toString();
    }
}
