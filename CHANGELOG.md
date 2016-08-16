# CHANGELOG
All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning 2](http://semver.org/).

## [Unreleased]


## [0.3.1] - 2016-08-16

### Fixed
* Output log level verbosity of `FilePermissionTask`. (#212)


## [0.3.0] - 2016-07-17

### Added
* More verbose logging / reporting, including failure reporting. (#140)
* Verbosity to deployment strategy when failures occur. (#189)
* `FilePermissionTask` to change the permissions of files and directories. (#186)
* Init command to create an `accompli.json` file. (#169)
* Support for a document root subdirectory in `MaintenanceModeTask`. (#163, #164)
* Debug verbosity to `RepositoryCheckoutTask`. (#157)
* Better documentation of the available connection adapters. (#191)

### Changed
* Composer binary installation to be more secure. (#145)
* Upgraded to version 0.3 of Chrono. (#157)

### Removed
* PHP 5.4 support (#157, #175)

### Fixed
* `ComposerInstallTask` to be non-interactive. (#168)
* `RepositoryCheckoutTask` to be non-interactive. (#157)


## [0.2.1] - 2016-04-13

### Fixed
* Current release detection in `DeployReleaseTask`. (#127)


## [0.2.0] - 2016-04-12
This release adds Symfony application deployment support to Accompli.

### Added
* Symfony deployment recipes and documentation. (#124)
* Documentation for `ComposerInstallTask`. (#101)
* `ExecuteCommandTask` to execute commands. (#108)
* `SSHAgentTask` initialize and manage an SSH agent. (#114)
* `YamlConfigurationTask` to create and update YAML configuration files. (#115)
* `ConnectionAdapterInterface::readLink` to read targets of symbolic links. (#120)
* Authentication functionality to `ComposerInstallTask`. (#112)
* Stream wrappers to easily access Accompli recipes. (#107, #111)
* Compatibility with Symfony 3 components. (#110)

### Changed
* Console output verbosities of tasks to provide better debug logging. (#117)
* `SSHConnectionAdapterInterface::executeCommand` to persist state. (#113)
* `ConnectionAdapterInterface::executeCommand` interface for providing command arguments. (#105)

### Fixed
* Current user detection in `SSHConnectionAdapter`. (#106)
* `DeployReleaseTask` listener method name. (#116)
* `DeployReleaseTask` to correctly read and handle targets of symbolic links. (#120)
* Misspellings in documentation. (#102)


## 0.1.0 - 2016-01-10

Initial Accompli release with basic tasks for installing and deploying a project on a local or remote location through SSH.


[Unreleased]: https://github.com/accompli/accompli/compare/0.3.1...HEAD
[0.3.1]: https://github.com/accompli/accompli/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/accompli/accompli/compare/0.2.1...0.3.0
[0.2.1]: https://github.com/accompli/accompli/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/accompli/accompli/compare/0.1.0...0.2.0
