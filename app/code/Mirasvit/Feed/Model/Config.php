<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Mirasvit\Feed\Helper\Io;

/**
 * Feed Configuration Class
 */
class Config
{
    const STATUS_COMPLETED  = 'completed';
    const STATUS_READY      = 'ready';
    const STATUS_PROCESSING = 'processing';
    const STATUS_ERROR      = 'error';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Mirasvit\Feed\Helper\Io
     */
    protected $io;

    /**
     * Config constructor.
     *
     * @param Filesystem $filesystem
     * @param Io         $io
     */
    public function __construct(
        Filesystem $filesystem,
        Io $io
    ) {
        $this->filesystem = $filesystem;
        $this->io         = $io;
    }

    /**
     * Remove all folder and files from base feed folder
     * @return bool
     */
    public function clearBasePath()
    {
        $this->io->rmdirRecursive($this->getBasePath());

        return true;
    }

    /**
     * /var/www/magento2/
     * @return string
     */
    public function getRootPath()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
    }

    /**
     * Base path to feed directory /var/www/magento2/pub/media/feed/
     * @return string
     */
    public function getBasePath()
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'feed';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path for store temporary files
     * @return string
     */
    public function getTmpPath()
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'tmp';

        if (!$this->io->dirExists($path)) {
            $this->io->mkdir($path);
        }

        return $path;
    }

    /**
     * Path to directory with templates
     * @return string
     */
    public function getTemplatePath()
    {
        $rel = 'Setup/data/template';
        $abs = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $rel;

        if (!$this->io->dirExists($abs)) {
            $this->io->mkdir($abs);
        }

        return $rel;
    }

    /**
     * Path to directory with rules
     * @return string
     */
    public function getRulePath()
    {
        $rel = 'rule';
        $abs = $this->getBasePath() . DIRECTORY_SEPARATOR . $rel;

        if (!$this->io->dirExists($abs)) {
            $this->io->mkdir($abs);
        }

        return $rel;
    }

    /**
     * Path to directory with Dynamic Attributes
     * @return string
     */
    public function getDynamicAttributePath()
    {
        $rel = 'dynamic/attribute';
        $abs = $this->getBasePath() . DIRECTORY_SEPARATOR . $rel;

        if (!$this->io->dirExists($abs)) {
            $this->io->mkdir($abs);
        }

        return $rel;
    }

    /**
     * Path to directory with Dynamic Categories
     * @return string
     */
    public function getDynamicCategoryPath()
    {
        $rel = 'dynamic/category';
        $abs = $this->getBasePath() . DIRECTORY_SEPARATOR . $rel;

        if (!$this->io->dirExists($abs)) {
            $this->io->mkdir($abs);
        }

        return $rel;
    }

    /**
     * Path to directory with Dynamic Variables
     * @return string
     */
    public function getDynamicVariablePath()
    {
        $rel = 'dynamic/variable';
        $abs = $this->getBasePath() . DIRECTORY_SEPARATOR . $rel;

        if (!$this->io->dirExists($abs)) {
            $this->io->mkdir($abs);
        }

        return $rel;
    }

    /**
     * Check and return maximum allowed script execution time
     * @return int
     */
    public function getMaxAllowedTime(): int
    {
        $time = (int)ini_get('max_execution_time');

        if ($time < 1 || $time > 30) {
            $time = 20;
        }

        return $time;
    }

    /**
     * Check and return maximum allowed memory
     * @return int
     */
    public function getMaxAllowedMemory(): int
    {
        return 220 * 1024 * 1024;
    }

    public function printPath(string $path): string
    {
        $rootPath = $this->getRootPath();

        $path = str_replace($rootPath, '[Magento root dir]/', $this->absolutePath($path));

        return $path;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function relativePath($path)
    {
        $rootPath = $this->getRootPath();

        $path = str_replace($rootPath, '', $path);

        return $path;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function absolutePath($path)
    {
        if (substr($path, 0, 5) === 'Setup') {
            return dirname(dirname(__FILE__)) . '/' . $path;
        }

        return $this->getBasePath() . '/' . $path;
    }

    /**
     * Facebook app ID
     *
     * @return string
     */
    public function getFacebookAppID()
    {
        $facebookAppId = 359775291691249;

        return $facebookAppId;
    }
}
