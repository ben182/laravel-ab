# Laravel A/B Testing

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ben182/laravel-ab.svg?style=flat-square)](https://packagist.org/packages/ben182/laravel-ab)
[![Build Status](https://img.shields.io/travis/ben182/laravel-ab/master.svg?style=flat-square)](https://travis-ci.org/ben182/laravel-ab)
[![Quality Score](https://img.shields.io/scrutinizer/g/ben182/laravel-ab.svg?style=flat-square)](https://scrutinizer-ci.com/g/ben182/laravel-ab)
[![Code Coverage](https://scrutinizer-ci.com/g/ben182/laravel-ab/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ben182/laravel-ab/?branch=master)

This package helps you to find out which content works on your site and which doesn't.

It allows you to create experiments and goals. The visitor will receive randomly the next experiment and you can customize your site to that experiment. The view and the goal conversion will be tracked and you can view the results in a report.

## Installation

This package can be used in Laravel 5.5 or higher.

You can install the package via composer:

```bash
composer require ben182/laravel-ab
```

## Config

After installation publish the config file:

```bash
php artisan vendor:publish --provider="Ben182\AbTesting\AbTestingServiceProvider"
```

You can define your experiments and goals in there.

Finally, run the newly added migration

```bash
php artisan migrate
```

Two new migrations should be added.

## Usage

### Experiments

```html
@if (AbTesting::isExperiment('logo-big'))

    <div class="logo-big"></div>

@elseif (AbTesting::isExperiment('logo-grayscale'))

    <div class="logo-greyscale"></div>

@elseif (AbTesting::isExperiment('brand-name'))

    <h1>Brand name</h1>

@endif
```

That's the most basic usage of the package. You don't have to initialize anything. The package handles everything for you if you call `isExperiment`

Alternatively you can use a custom blade if statement:

```html
@ab('logo-big')

    <div class="logo-big"></div>

@elseab('logo-grayscale')

    <div class="logo-greyscale"></div>

@elseab('brand-name')

    <h1>Brand name</h1>

@endab
```

This will work exactly the same way.

If you don't want to make any continual rendering you can call

```php
AbTesting::pageview()
```

directly and trigger a new page view with a random experiment. This function will also be called from `isExperiment`.

Under the hood a new session item will keep track of the current experiment. A session will only get one experiment and only trigger one page view.

You can grab the current experiment with:

```php
// get the underlying model
AbTesting::getExperiment()

// get the experiment name
AbTesting::getExperiment()->name

// get the visitor count
AbTesting::getExperiment()->visitors
```

Alternativly there is a request helper for you:

```php
public function index(Request $request) {
    // the same as 'AbTesting::getExperiment()'
    $request->abExperiment()
}
```

### Goals

To complete a goal simply call:

```php
AbTesting::completeGoal('signup')
```

The function will increment the conversion of the goal assigned to the active experiment. If there isn't an active experiment running for the session one will be created. You can only trigger a goal conversion once per session. This will be prevented with another session item. The function returns the underlying goal model.

To get all completed goals for the current session:

```php
AbTesting::getCompletedGoals()
```

### Report

To get a report of the pageviews, completed goals and conversion call the report command:

```bash
php artisan ab:report
```

This prints something like this:

```
+---------------+----------+-------------+
| Experiment    | Visitors | Goal signup |
+---------------+----------+-------------+
| big-logo      | 2        | 1 (50%)     |
| small-buttons | 1        | 0 (0%)      |
+---------------+----------+-------------+
```

### Reset

To reset all your visitors and goal completions call the reset command:

```bash
php artisan ab:reset
```

### Events

In addition you can hook into two events:

- `ExperimentNewVisitor` gets triggered once an experiment gets assigned to a new visitor. You can grab the experiment as a property of the event.
- `GoalCompleted` gets triggered once a goal is completed. You can grab the goal as a property of the event.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email moin@benjaminbortels.de instead of using the issue tracker.

## Credits

- [Benjamin Bortels](https://github.com/ben182)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
