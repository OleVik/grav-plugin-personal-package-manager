# Personal Package Manager Plugin

The **Personal Package Manager** Plugin is for [Grav CMS](http://github.com/getgrav/grav). Required by PGPM. **Work in progress**.

## Installation

Installing the Personal Package Manager plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install personal-package-manager

This will install the Personal Package Manager plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/personal-package-manager`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `personal-package-manager`. You can find these files on [GitHub](https://github.com/OleVik/grav-plugin-personal-package-manager) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/personal-package-manager

## Configuration

Before configuring this plugin, you should copy the `user/plugins/personal-package-manager/personal-package-manager.yaml` to `user/config/plugins/personal-package-manager.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
```

## Usage

Call `php bin/plugin personal-package-manager data` via CLI.

MIT License 2017 by Ole Vik.
