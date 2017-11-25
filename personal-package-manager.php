<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use Symfony\Component\Yaml\Yaml;

/**
 * Personal Package Manger Plugin
 *
 * Class PersonalPackageManagerPlugin.
 * 
 * @package Grav\Plugin
 * @return  JSON-formatted Grav-environment data
 * @license MIT License by Ole Vik
 */
class PersonalPackageManagerPlugin extends Plugin
{
    protected $route = 'personalpackagemanagerendpoint';
    
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            return;
        }

        $this->enable([
            'onPagesInitialized' => ['pluginEndpoint', 0],
        ]);
    }
    
    public function pluginEndpoint(Event $e)
    {
        $uri = $this->grav['uri'];
        
        if (strpos($uri->path(), $this->config->get('plugins.personal.package-manager.route') . '/' . $this->route) === false) {
            return;
        }
        
        $config = $this->config();
        $data = $this->iteratePackages($config);
        
        print_r(json_encode($data));

        /* if (!$this->isAuthorized()) {
            header('HTTP/1.1 401 Unauthorized');
            exit();
        } */

        exit();
    }

    public function config()
    {
        $config = array();
        $config['locator'] = $this->grav['locator'];
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
        return $return;
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
