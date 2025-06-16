<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;

/**
 * Clean Command
 * 
 * Cleans the application by removing Composer's vendor directory,
 * generated assets, and cache files.
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class CleanCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string The command name
     */
    public function getName(): string
    {
        return 'clean';
    }
    
    /**
     * Get the command description
     * 
     * @return string The command description
     */
    public function getDescription(): string
    {
        return 'Clean the application';
    }
    
    /**
     * Get the command help text
     * 
     * @return string The command help text
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: logbie clean [options]

Clean the application by removing Composer's vendor directory,
generated assets, and cache files.

Options:
  --vendor          Remove vendor directory
  --assets          Remove generated assets
  --cache           Remove cache files
  --all             Remove all (vendor, assets, cache) (default)
  --help, -h        Display this help message
HELP;
    }
    
    /**
     * Execute the command
     * 
     * @param array $args Command arguments
     * @return int Exit code (0 for success, non-zero for failure)
     */
    public function execute(array $args = []): int
    {
        // Parse options
        [$options, $remainingArgs] = $this->parseOptions($args);
        
        // If no specific options are provided, clean everything
        $cleanAll = !isset($options['vendor']) && !isset($options['assets']) && !isset($options['cache']);
        
        if ($cleanAll || isset($options['all'])) {
            $options['vendor'] = true;
            $options['assets'] = true;
            $options['cache'] = true;
        }
        
        $this->logger->info("Cleaning Logbie application...");
        
        // Clean vendor directory
        if (isset($options['vendor'])) {
            $this->cleanVendorDirectory();
        }
        
        // Clean generated assets
        if (isset($options['assets'])) {
            $this->cleanGeneratedAssets();
        }
        
        // Clean cache files
        if (isset($options['cache'])) {
            $this->cleanCacheFiles();
        }
        
        $this->logger->success("Clean completed successfully");
        return 0;
    }
    
    /**
     * Clean vendor directory
     * 
     * @return void
     */
    private function cleanVendorDirectory(): void
    {
        $vendorDir = $this->getProjectRoot() . '/vendor';
        $composerLock = $this->getProjectRoot() . '/composer.lock';
        
        $this->logger->info("Cleaning vendor directory...");
        
        if (is_dir($vendorDir)) {
            if ($this->removeDirectory($vendorDir)) {
                $this->logger->success("Vendor directory removed successfully");
            } else {
                $this->logger->error("Failed to remove vendor directory");
            }
        } else {
            $this->logger->info("Vendor directory not found, skipping");
        }
        
        if (file_exists($composerLock)) {
            if (unlink($composerLock)) {
                $this->logger->success("composer.lock removed successfully");
            } else {
                $this->logger->error("Failed to remove composer.lock");
            }
        } else {
            $this->logger->info("composer.lock not found, skipping");
        }
    }
    
    /**
     * Clean generated assets
     * 
     * @return void
     */
    private function cleanGeneratedAssets(): void
    {
        $assetDirs = [
            $this->getProjectRoot() . '/public/assets',
            $this->getProjectRoot() . '/public/build',
            $this->getProjectRoot() . '/public/dist',
        ];
        
        $this->logger->info("Cleaning generated assets...");
        
        $assetsFound = false;
        
        foreach ($assetDirs as $dir) {
            if (is_dir($dir)) {
                $assetsFound = true;
                
                if ($this->removeDirectory($dir)) {
                    $this->logger->success("Asset directory removed successfully: " . basename($dir));
                } else {
                    $this->logger->error("Failed to remove asset directory: " . basename($dir));
                }
            }
        }
        
        if (!$assetsFound) {
            $this->logger->info("No asset directories found, skipping");
        }
        
        // Clean node_modules if it exists
        $nodeModules = $this->getProjectRoot() . '/node_modules';
        
        if (is_dir($nodeModules)) {
            $this->logger->info("Cleaning node_modules directory...");
            
            if ($this->removeDirectory($nodeModules)) {
                $this->logger->success("node_modules directory removed successfully");
            } else {
                $this->logger->error("Failed to remove node_modules directory");
            }
        }
    }
    
    /**
     * Clean cache files
     * 
     * @return void
     */
    private function cleanCacheFiles(): void
    {
        $cacheDir = $this->getProjectRoot() . '/storage/cache';
        
        $this->logger->info("Cleaning cache files...");
        
        if (is_dir($cacheDir)) {
            if ($this->removeDirectory($cacheDir, false)) {
                $this->logger->success("Cache files removed successfully");
                
                // Recreate the cache directory
                if (!mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
                    $this->logger->error("Failed to recreate cache directory");
                }
            } else {
                $this->logger->error("Failed to remove cache files");
            }
        } else {
            $this->logger->info("Cache directory not found, skipping");
        }
    }
    
    /**
     * Remove a directory and its contents
     * 
     * @param string $dir The directory to remove
     * @param bool $removeRoot Whether to remove the root directory itself
     * @return bool True if successful, false otherwise
     */
    private function removeDirectory(string $dir, bool $removeRoot = true): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        return $removeRoot ? rmdir($dir) : true;
    }
    
    /**
     * Get the project root directory
     * 
     * @return string The project root directory
     */
    private function getProjectRoot(): string
    {
        return dirname(__DIR__, 3);
    }
}