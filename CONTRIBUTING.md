# Contributing

Contributions are always **welcome**.

We accept contributions through Pull Requests on [Github](https://github.com/accompli/accompli).


## Issues

- Please [create an issue](https://github.com/accompli/accompli/issues/new) before submitting a Pull Request. This way we can discuss the new feature or problem and come to the best solution before 'wasting time' coding.


## Pull Requests

- **[Symfony Coding Standards](http://symfony.com/doc/current/contributing/code/standards.html)** - See [Coding standards and naming conventions](#coding-standards-and-naming-conventions) for more information.

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We follow [Semantic Versioning 2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

- **Create feature branches** - Don't ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.


## Coding standards and naming conventions

Accompli follows the [Symfony code standards](http://symfony.com/doc/current/contributing/code/standards.html) with one exception:

- No [Yoda conditions](https://en.wikipedia.org/wiki/Yoda_conditions). We're more a Han Solo fan, you see.

Code style standards are best fixed with the [PHP Coding Standards Fixer](http://cs.sensiolabs.org/).
Please check your code before creating a commit:

``` bash
$ php php-cs-fixer.phar fix src/ --level=symfony
$ php php-cs-fixer.phar fix tests/ --level=symfony
```


## Running Tests

``` bash
$ vendor/bin/phpunit
```
