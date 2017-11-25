<?php
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
 * @package Grav\Plugin\Console
 * @return  JSON-formatted Grav-environment data.
 * @license MIT License by Ole Vik
 */
class IteratePackagesCommand extends ConsoleCommand
{
    protected function configure()
    {
        $this
            ->setName('data')
            ->setAliases(['iteratepackages'])
            ->setDescription('Iterates Packages')
            ->setHelp('The <info>data</info> command returns local ecosystem of packages and versions.');
    }

    protected function serve()
    {
        $config = $this->config();
        $this->iteratePackages($config);
    }

    private function config()
    {
        $config = array();
        $config['locator'] = Grav::instance()['locator'];
        $config['pluginsPath'] = $config['locator']->findResource('plugins://');
        $config['themesPath'] = $config['locator']->findResource('themes://');
        return $config;
    }

    private function iteratePackages($config)
    {
        $plugins = preg_grep('/^([^.])/', scandir($config['pluginsPath']));
        $themes = preg_grep('/^([^.])/', scandir($config['themesPath']));
        $packages = array_merge($plugins, $themes);
        $return = ['grav' => $this->getPackageData($config)];
        foreach ($packages as $package) {
            $return[$package] = $this->getPackageData($config, $package);
        }
        $this->output->writeln(json_encode($return));
    }


    private function getPackageData($config, $package = 'grav')
    {
        if ($package == 'grav') {
            return [
                'version' => GRAV_VERSION,
                'url' => 'https://github.com/getgrav/grav',
                'dependencies' => ''
            ];
        }
        $blueprints = $config['locator']->findResource('plugins://' . $package . DS . 'blueprints.yaml');
        if (!file_exists($blueprints)) {
            $blueprints = $config['locator']->findResource('themes://' . $package . DS . 'blueprints.yaml');
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

    public static function parseYAML($yaml, $mode)
    {
        if (preg_match('/' . $mode . ':(.*?)\n/isx', $yaml, $display) === 1) {
            $return = trim($display[1]);
            $return = Yaml::parse($return);
            return $return;
        }
    }
    
    public static function parseDependencies($yaml)
    {
        if (preg_match('/dependencies:(.*?)form:/s', $yaml, $display) === 1) {
            $dependencies = "dependencies:" . $display[1];
            $dependencies = Yaml::parse($dependencies);
            foreach ($dependencies as $items) {
                foreach ($items as $item) {
                    if (is_array($item)) {
                        if (substr($item[key($item)], -4) == '.git') {
                            $return[$item[key($item)]] = 'dev-master';
                        } elseif(isset($item['name'])) {
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
            $return = null;
        }
        return $return;
    }
}
