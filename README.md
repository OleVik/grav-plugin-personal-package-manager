# Personal Package Manager Plugin

The **Personal Package Manager** Plugin is for [Grav CMS](http://github.com/getgrav/grav). Required by PGPM. **Work in progress**.

## Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `personal-package-manager`. You can find these files on [GitHub](https://github.com/OleVik/grav-plugin-personal-package-manager).

You should now have all the plugin files under

    /your/site/grav/user/plugins/personal-package-manager

## Configuration

Before configuring this plugin, you should copy the `user/plugins/personal-package-manager/personal-package-manager.yaml` to `user/config/plugins/personal-package-manager.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
```

## Usage

Call `php bin/plugin personal-package-manager data` via CLI. Use the `-p` option to pretty-print the JSON, and the `-b` flag to reduce the data to just include names and versions of extensions.

MIT License 2017-2020 by Ole Vik.
