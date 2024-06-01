<?php

use Livewire\Component;
use Vessel\BaseVessel;

use function Livewire\invade;

beforeEach(function (): void {
    $component = new class() extends Component
    {
        public function render(): string
        {
            return '<div></div>';
        }
    };

    $this->vessel = new class(app('livewire')->new($component::class)) extends BaseVessel
    {
        public string $property;

        public function init(): void
        {
            $this->property = 'value';
        }
    };
});

test('should get public properties', function (): void {
    $props = $this->vessel->getPublicProperties();

    expect(count($props))->toBe(1);
    expect($props[0]->getName())->toBe('property');
});

test('should transform to array', function (): void {
    $props = invade($this->vessel)->toArray();

    expect($props)->toBe(['property' => 'value']);
});

test('should get lifetime from session by default', function (): void {
    $lifetime = invade($this->vessel)->lifetime;

    expect($lifetime)->toBe(120);
});
