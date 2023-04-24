<x-guest-layout>
    <h1 class="text-4xl">Test</h1>

    <x-demo />
    <x-demo>
        <x-demo />
        <x-demo />
        <x-demo />

        <x-demo foo="bar">
            <x-demo array="['key' => 'value']" />
            <x-demo />
            <x-demo />

        </x-demo>
    </x-demo>

</x-guest-layout>
