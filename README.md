# Vessel - global state for Livewire

Vessel provides a global state for [Livewire](https://livewire.laravel.com), allowing multiple components to share state without a multitude of events to handle interaction.

Just create a Vessel class, set it's properties and initialization and use it on your components.

## Installation

```bash
composer install epessine/vessel
```

No configuration needed.

## Usage

Vessel works by creating 'vessel classes', where you define the properties that you global state will have:

```php
use Vessel\BaseVessel;

class FilterVessel extends BaseVessel
{
    public string $selectedUserType = 'admin';
}
```

You can also initialize your properties using the `init()` method:

```php
use Vessel\BaseVessel;

class FilterVessel extends BaseVessel
{
    public string $selectedUserType;

    public function init(): void
    {
        $this->selectedUserType = str('admin')->toString();
    }
}
```

Then use the Vessel on your components with the `Vessel\Attributes\Vessel` attribute. Take a look at the components below:

```php
use Livewire\Component;
use Vessel\Attributes\Vessel;

/**
 * @property-read FilterVessel globalFilters
 */
class Filter extends Component
{
    #[Vessel]
    public function globalFilters(): string
    {
        return FilterVessel::class; // vessel class declared above
    }

    public function selectUserFilter(string $type): void
    {
        // here we change the vessel property value
        $this->globalFilters->selectedUserType = $type;
    }

    // ...
}
```

```php
use Livewire\Component;
use Vessel\Attributes\Vessel;

/**
 * @property-read FilterVessel globalFilters
 */
class UserList extends Component
{
    #[Vessel]
    public function globalFilters(): string
    {
        return FilterVessel::class; // vessel class declared above
    }

    #[Computed]
    public function users(): array
    {
        // here we get the updated vessel property value
        User::query()
            ->where('type', $this->globalFilters->selectedUserType)
            ->get()
            ->all();
    }

    // ...
}
```
After calling `Filter::selectUserFilter()`, the `selectedUserType` will be updated on all components that use the same Vessel, and will reflect the change on the `UserList::users` computed property query, for example.

All of that without a single event dispatch/listener!

## Troubleshooting

There are some base rules when using Vessel:

 - Vessel properties _cannot_ be accessed/mutated on the front-end via `$wire`. You can create methods that can be called on the front-end to interact with the Vessel indirectly, though.
- Vessel properties are _'immutable'_ so any changes inside the property will _not_ reflect on other components. Always reassign the property after making changes.
- Vessel uses the application cache to operate and maintain state, so storing huge amounts of data on a Vessel can slow down your whole application.
