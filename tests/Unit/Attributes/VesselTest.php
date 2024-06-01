<?php

use Illuminate\View\ViewException;
use Livewire\Component;
use Vessel\Attributes\Vessel;
use Vessel\BaseVessel;
use Vessel\Exceptions\CannotCallVesselDirectlyException;

use function Pest\Livewire\livewire;

class TestVessel extends BaseVessel
{
    public string $filter = 'one';
}

class One extends Component
{
    #[Vessel]
    public function state(): string
    {
        return TestVessel::class;
    }

    public function render(): string
    {
        return '<div></div>';
    }
}

class Two extends Component
{
    #[Vessel]
    public function state(): string
    {
        return TestVessel::class;
    }

    public function render(): string
    {
        return '<div></div>';
    }
}

test('should get and set vessel properties', function (): void {
    $one = livewire(One::class);
    $two = livewire(Two::class);

    expect($one->get('state.filter'))->toBe($two->get('state.filter'));

    $one->state->filter = 'two';

    expect($one->state->filter)->toBe('two');
    expect($one->get('state.filter'))->toBe($two->get('state.filter'));

    $two->state->filter = 'one';

    expect($one->state->filter)->toBe('one');
    expect($one->get('state.filter'))->toBe($two->get('state.filter'));
});

test('should throw exception when calling vessel method directly', function (): void {
    livewire(One::class)->call('state');
})->throws(CannotCallVesselDirectlyException::class);

test('should throw exception when vessel method does not return vessel', function (): void {
    livewire((new class() extends Component
    {
        #[Vessel]
        public function state(): string
        {
            return 'invalid';
        }

        public function render(): string
        {
            return '<div></div>';
        }
    })::class)->get('state');
})->throws(ViewException::class);
