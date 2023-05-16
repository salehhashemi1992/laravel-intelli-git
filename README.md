# Laravel IntelliGit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/salehhashemi/laravel-intelli-git.svg?style=flat-square)](https://packagist.org/packages/salehhashemi/laravel-intelli-git)
[![Total Downloads](https://img.shields.io/packagist/dt/salehhashemi/laravel-intelli-git.svg?style=flat-square)](https://packagist.org/packages/salehhashemi/laravel-intelli-git)
[![GitHub Actions](https://img.shields.io/github/actions/workflow/status/salehhashemi1992/laravel-intelli-git/run-tests.yml?branch=main&label=tests)](https://github.com/salehhashemi1992/laravel-intelli-git/actions/workflows/run-tests.yml)
[![StyleCI](https://github.styleci.io/repos/640366803/shield?branch=main)](https://github.styleci.io/repos/640366803?branch=main)

An intelligent Laravel package to generate git commit messages using OpenAI.

![Header Image](./assets/header.png)

## Features

The following commands are implemented in this package:

- `ai:commit` - Generate a commit message and description using AI

Stay tuned for future updates as we continue to expand the capabilities of the Laravel Intelli Git package.

## Installation

1. Install the package via composer:
    ```
    composer require salehhashemi/laravel-intelli-git
    ```

2. Publish the configuration file:
    ```
    php artisan vendor:publish --provider="Salehhashemi\LaravelIntelliGit\LaravelIntelliGitServiceProvider"
    ```

3. Add your OpenAI API key to the `.env` file:
    ```
    OPEN_AI_KEY=your_openai_key
    ```

4. Optionally, you can change the default model used by OpenAI in the `.env` file:
    ```
   OPEN_AI_MODEL=gpt-4
    ```

## Usage

### ai:commit

```
php artisan ai:commit
```

This command will automatically check for staged and unstaged changes in your git repository. If unstaged changes 
are found, it will ask you to stage them. Then, it will generate a commit message and description using OpenAI.

## Examples

### ai:commit

Here's an example of the command in action:

```
$ php artisan ai:commit
No staged changes found.
There are unstaged changes. Would you like to stage all changes? (yes/no) [no]:
> yes
All changes have been staged.
Generating commit message with AI, please wait...

Here are the AI-generated commit title and description:
Title
Refactor User model and improve validation
Description
This commit refactors the User model, specifically improving the validation logic by adding custom validation rules. It also includes updates to the relevant tests.

```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Saleh Hashemi](https://github.com/salehhashemi1992)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.