<?php
/**
 * Personal Package Manager, CLI
 *
 * PHP version 7
 *
 * @category API
 * @package  Grav\Plugin\Console
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/OleVik/grav-plugin-personal-package-manager
 */

namespace Grav\Plugin\Console;

use Grav\Common\Grav;
use Grav\Common\GravTrait;
use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Class IteratePackagesCommand
 *
 * Command line utility for iterating Grav-extensions.
 *
 * @category API
 * @package  Grav\Plugin\Console\IteratePackagesCommand
 * @author   Ole Vik <git@olevik.net>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @link     https://github.com/OleVik/grav-plugin-personal-package-manager
 */
class IteratePackagesCommand extends ConsoleCommand
{
    /**
     * Command definitions
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('data')
            ->setAliases(['iteratepackages'])
            ->setDescription('Iterates Packages')
            ->setHelp('The <info>data</info> command returns the local ecosystem of packages and their versions.')
            ->addOption(
                'pretty',
                'p',
                InputOption::VALUE_NONE,
                'JSON_PRETTY_PRINT in json_encode() options'
            )
            ->addOption(
                'basic',
                'b',
                InputOption::VALUE_NONE,
                'Only include names and versions'
            );
    }

    /**
     * Execute command
     *
     * @return void
     */
    protected function serve()
    {
        $config = $this->config();
        $pretty = $this->input->getOption('pretty') ?? false;
        $basic = $this->input->getOption('basic') ?? false;
        $this->iteratePackages($config, $pretty, $basic);
    }

    /**
     * Get configuration
     *
     * @return array
     */
    public function config(): array
    {
        $config = array();
        $config['locator'] = Grav::instance()['locator'];
        $config['pluginsPath'] = $config['locator']->findResource('plugins://');
        $config['themesPath'] = $config['locator']->findResource('themes://');
        return $config;
    }

    /**
     * Iterate packages
     *
     * @param array   $config Configuration.
     * @param boolean $pretty Pretty-print JSON.
     * @param boolean $basic  Reduce data to name: version.
     *
     * @return void
     */
    public function iteratePackages(
        array $config,
        bool $pretty = false,
        bool $basic = false
    ) {
        $plugins = preg_grep('/^([^.])/', scandir($config['pluginsPath']));
        $themes = preg_grep('/^([^.])/', scandir($config['themesPath']));
        $packages = array_merge($plugins, $themes);
        $data = ['grav' => $this->getPackageData($config)];
        foreach ($packages as $package) {
            $data[$package] = $this->getPackageData($config, $package);
        }
        if ($basic) {
            $data = $this->getBasicData($data);
        }
        if ($pretty === true) {
            $this->output->writeln(json_encode($data, JSON_PRETTY_PRINT));
        } else {
            $this->output->writeln(json_encode($data));
        }
    }

    /**
     * Get package data
     *
     * @param array  $config  Configuration.
     * @param string $package Package-slug.
     *
     * @return array
     */
    public function getPackageData(array$config, $package = 'grav'): array
    {
        if ($package == 'grav') {
            return [
                'version' => GRAV_VERSION,
                'url' => 'https://github.com/getgrav/grav',
                'dependencies' => ''
            ];
        }
        $blueprints = $config['locator']->findResource(
            'plugins://' . $package . DS . 'blueprints.yaml'
        );
        if (!file_exists($blueprints)) {
            $blueprints = $config['locator']->findResource(
                'themes://' . $package . DS . 'blueprints.yaml'
            );
        }
        $yaml = file_get_contents($blueprints);
        $version = $this->parseYAML($yaml, 'version');
        $homepage = $this->parseYAML($yaml, 'homepage');
        $dependencies = $this->parseDependencies($yaml);
        return [
            'version' => $version,
            'url' => $homepage,
            'dependencies' => $dependencies
        ];
    }

    /**
     * Reduce data to name: version
     *
     * @param array $data Data to reduce.
     *
     * @return array
     */
    public function getBasicData(array $data): array
    {
        $return = array();
        foreach ($data as $key => $values) {
            $return[$key] = $values['version'];
        }
        return $return;
    }

    /**
     * Parse YAML-string
     *
     * @param string $yaml Data to parse.
     * @param string $mode Key to look for.
     *
     * @return string Parsed YAML
     */
    public static function parseYAML(string $yaml, string $mode): string
    {
        if (preg_match('/' . $mode . ':(.*?)\n/isx', $yaml, $display) === 1) {
            $return = trim($display[1]);
            $return = Yaml::parse($return);
            return $return;
        }
        return '';
    }
    
    /**
     * Parse dependencies from YAML
     *
     * @param string $yaml Data to parse.
     *
     * @return array Dependencies
     */
    public static function parseDependencies(string $yaml): array
    {
        if (preg_match('/dependencies:(.*?)form:/s', $yaml, $display) === 1) {
            $dependencies = "dependencies:" . $display[1];
            $dependencies = Yaml::parse($dependencies);
            foreach ($dependencies as $items) {
                foreach ($items as $item) {
                    if (is_array($item)) {
                        if (substr($item[key($item)], -4) == '.git') {
                            $return[$item[key($item)]] = 'dev-master';
                        } elseif (isset($item['name'])) {
                            $return[$item['name']] = $item['version'];
                        } else {
                            $return[key($item)] = $item[key($item)];
                        }
                    } else {
                        $return[$item] = '*';
                    }
                }
            }
        } else {
            $return = [];
        }
        return $return;
    }
}
