# Blade Devtools

An attempt to build something like [Vue's Devtools](https://devtools.vuejs.org/) or the [React Developer Tools](https://react.dev/learn/react-developer-tools) for the Blade templating engine.

![Screenshot of a development version of Blade Devtools showing various elements used in Filament](https://github.com/NiclasvanEyk/blade-devtools/assets/10284694/97b7578b-58f8-4fa9-a618-adb058347efc)


## Installation

This is still in development, there is no package you could readily install.

## Development

There are two [`packages/`](./packages/) and one [example Laravel app](./apps/blade-devtools-test-app/) for testing their integration.
Right now the functionality is split into a composer package which swaps out the implementation of the Blade compiler with one that emits debug markers and a NPM package that consumes them.

To start developing just setup the example app like any other Laravel application.
The two packages are setup to symlink to their local versions during `composer install` and `npm install`, so any changes should be reflected immediately.
