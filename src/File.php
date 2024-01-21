<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\FileAccessException;

class File
{
    private $mime_types = [
        'hqx' => 'application/mac-binhex40',
        'cpt' => 'application/mac-compactpro',
        'csv' => ['text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream'],
        'bin' => 'application/macbinary',
        'dms' => 'application/octet-stream',
        'lha' => 'application/octet-stream',
        'lzh' => 'application/octet-stream',
        'exe' => ['application/octet-stream', 'application/x-msdownload'],
        'class' => 'application/octet-stream',
        'psd' => 'application/x-photoshop',
        'so' => 'application/octet-stream',
        'sea' => 'application/octet-stream',
        'dll' => 'application/octet-stream',
        'oda' => 'application/oda',
        'pdf' => ['application/pdf', 'application/x-download'],
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'mif' => 'application/vnd.mif',
        'xls' => ['application/excel', 'application/vnd.ms-excel', 'application/msexcel'],
        'ppt' => ['application/powerpoint', 'application/vnd.ms-powerpoint'],
        'wbxml' => 'application/wbxml',
        'wmlc' => 'application/wmlc',
        'dcr' => 'application/x-director',
        'dir' => 'application/x-director',
        'dxr' => 'application/x-director',
        'dvi' => 'application/x-dvi',
        'gtar' => 'application/x-gtar',
        'gz' => 'application/x-gzip',
        'php' => ['application/x-httpd-php', 'text/x-php'],
        'php4' => 'application/x-httpd-php',
        'php3' => 'application/x-httpd-php',
        'phtml' => 'application/x-httpd-php',
        'phps' => 'application/x-httpd-php-source',
        'js' => 'application/x-javascript',
        'swf' => 'application/x-shockwave-flash',
        'sit' => 'application/x-stuffit',
        'tar' => 'application/x-tar',
        'tgz' => ['application/x-tar', 'application/x-gzip-compressed'],
        'xhtml' => 'application/xhtml+xml',
        'xht' => 'application/xhtml+xml',
        'zip' => ['application/x-zip', 'application/zip', 'application/x-zip-compressed'],
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mpga' => 'audio/mpeg',
        'mp2' => 'audio/mpeg',
        'mp3' => ['audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'],
        'aif' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'ra' => 'audio/x-realaudio',
        'rv' => 'video/vnd.rn-realvideo',
        'wav' => 'audio/x-wav',
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpe' => ['image/jpeg', 'image/pjpeg'],
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'shtml' => 'text/html',
        'txt' => 'text/plain',
        'text' => 'text/plain',
        'log' => ['text/plain', 'text/x-log'],
        'rtx' => 'text/richtext',
        'rtf' => 'text/rtf',
        'xml' => 'text/xml',
        'xsl' => 'text/xml',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo',
        'movie' => 'video/x-sgi-movie',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'word' => ['application/msword', 'application/octet-stream'],
        'xl' => 'application/excel',
        'eml' => 'message/rfc822',
        'json' => ['application/json', 'text/json'],
    ];

    /**
     * @return File
     */
    public static function forge(): File
    {
        return new self();
    }

    /**
     * Read a directory and return containing filenames as array.
     *
     * @param string $directory Name of directory
     * @return  array|bool
     */
    public function read_directory(string $directory)
    {
        if (empty($directory))
        {
            return false;
        }

        if (!file_exists($directory))
        {
            clearstatcache();

            return false;
        }

        $result = [];
        $scanned_directory = scandir($directory);

        foreach ($scanned_directory as $value)
        {
            if (!in_array($value, ['.', '..']))
            {
                if (is_dir($directory . DIRECTORY_SEPARATOR . $value))
                {
                    $result[$value] = $this->read_directory($directory . DIRECTORY_SEPARATOR . $value);
                }
                else
                {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Get size from a file.
     *
     * @param string $file
     * @param boolean $human_readable set true to return filesize as human readable string
     * @return  mixed
     */
    public function size(string $file, $human_readable = false)
    {
        if (!file_exists($file))
        {
            return false;
        }

        if (true === $human_readable)
        {
            return $this->format_bytes(filesize($file));
        }

        return filesize($file);
    }

    /**
     * rename directory or file
     *
     * @param string $current_filename path to file or directory to rename
     * @param string $new_filename new path or filename
     * @return  boolean
     */
    public function change_name(string $current_filename, string $new_filename): bool
    {
        return rename($current_filename, $new_filename);
    }

    /**
     * Alias for rename()
     * @param string $current_directory
     * @param string $new_directory
     * @return boolean
     */
    public function rename_directory(string $current_directory, string $new_directory): bool
    {
        return $this->change_name($current_directory, $new_directory);
    }

    /**
     * Copy file
     *
     * @param string $source_file
     * @param string $new_directory
     * @param boolean $overwrite
     * @return  boolean
     */
    public function copy(string $source_file, string $new_directory, $overwrite = true): bool
    {
        if (!is_file($source_file))
        {
            return false;
        }

        $source_path_infos = pathinfo($source_file);
        $full_target = $new_directory . DIRECTORY_SEPARATOR . $source_path_infos['basename'];

        if (!$overwrite && file_exists($full_target))
        {
            return false;
        }

        return copy($source_file, $full_target);
    }

    /**
     * Delete file
     *
     * @param string $file path to file to delete
     * @return  boolean
     */
    public function delete(string $file): bool
    {
        if (!is_file($file) && !is_link($file))
        {
            return false;
        }

        return unlink($file);
    }

    /**
     * Create directory
     *
     * @param string $directory
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public function create_directory(string $directory, $mode = 0755, $recursive = true): bool
    {
        return mkdir($directory, $mode, $recursive);
    }

    /**
     * Check if
     * @param string $directory
     * @return bool
     */
    public function directory_exists(string $directory): bool
    {
        return is_dir($directory);
    }

    /**
     * Delete directory
     * If the directory is not empty, false will returned; exept if you set the second parameter to true.
     *
     * @param string $directory
     * @param boolean $delete_files
     * @return  bool
     */
    public function delete_directory(string $directory, $delete_files = false): bool
    {
        try
        {
            if (!is_dir($directory) || is_link($directory))
            {
                throw new FileAccessException('"' . $directory . '" is not a directory!');
            }

            if ($delete_files === false && count($this->read_directory($directory)) > 0)
            {
                throw new FileAccessException('Cannot delete non empty folder!');
            }

            if (true === $delete_files)
            {
                $files_in_folder = $this->read_directory($directory);

                if (false !== $files_in_folder && count($files_in_folder) > 0)
                {
                    foreach ($files_in_folder as $folder => $file)
                    {
                        if (is_array($file))
                        {
                            foreach ($file as $delete_subfile)
                            {
                                $this->delete($directory . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $delete_subfile);
                            }

                            $this->delete_directory($directory . DIRECTORY_SEPARATOR . $folder);
                        }
                        else
                        {
                            if (is_file($directory . $file))
                            {
                                $this->delete($directory . $file);
                            }

                            if (is_dir($directory . $file))
                            {
                                $this->delete_directory($directory . $file);
                            }
                        }
                    }
                }
            }

            return rmdir($directory);
        }
        catch (FileAccessException $e)
        {
            Debug::backtrace($e);
        }

        return false;
    }

    /**
     * Converts a number of bytes to a human readable number by taking the
     * number of that unit that the bytes will go into it. Supports TB value.
     *
     *
     * @param int $bytes
     * @param int $decimals
     * @return  boolean|string
     */
    private function format_bytes(int $bytes = 0, int $decimals = 0)
    {
        $size_assignment = [
            'TiB' => 1099511627776,
            'GiB' => 1073741824,
            'MiB' => 1048576,
            'KiB' => 1024,
            'Byte ' => 1,
        ];

        foreach ($size_assignment as $unit => $value)
        {
            if ((float)$bytes >= $value)
            {
                return sprintf('%01.' . $decimals . 'f', ($bytes / $value)) . ' ' . $unit;
            }
        }

        return false;
    }

    /**
     * Compare directory
     * Compares two directories and return the differences
     *
     * @param string $folder1
     * @param string $folder2
     * @param boolean $show_sources
     * @return  mixed
     */
    public function compare_directory(string $folder1, string $folder2, $show_sources = false)
    {
        try
        {
            if ((!is_dir($folder1) || is_link($folder1)) && (!is_dir($folder2) || is_link($folder2)))
            {
                return true;
            }

            $content_difference['count'] = 0;

            $folder1_content = $this->read_directory($folder1);
            $folder2_content = $this->read_directory($folder2);

            if (false === $folder1_content || false === $folder2_content)
            {
                throw new FileAccessException('Cannot compare empty folders!');
            }

            if ((false !== $folder1_content && count($folder1_content) > 0) && (false !== $folder2_content && count($folder2_content) > 0))
            {
                $_difference = array_diff($folder1_content, $folder2_content);
                $_total_differences = count($_difference);

                if ($_total_differences > 0)
                {
                    $content_difference['count'] = $_total_differences;
                    $content_difference['difference'] = $_difference;
                }
            }

            if (true === $show_sources)
            {
                $content_difference['sources'][$folder1] = $folder1_content;
                $content_difference['sources'][$folder2] = $folder2_content;
            }

            return (object)$content_difference;
        }
        catch (FileAccessException $e)
        {
            Debug::backtrace();

            return false;
        }
    }

    /**
     * Get last modified of given file
     *
     * @param string $file
     * @param string $date_format
     * @param boolean $timestamp
     * @return  false|int|string
     */
    public function get_last_modified(string $file, $date_format = 'Y-m-d', $timestamp = false)
    {
        if (!file_exists($file))
        {
            return false;
        }

        if ($timestamp)
        {
            return @filemtime($file);
        }

        return date($date_format, @filemtime($file));
    }

    /**
     * Write data into file
     *
     * @param string $file
     * @param string $content
     * @return  boolean
     */
    public function write(string $file, string $content): bool
    {
        $file_hander = @fopen($file, 'cb');

        if (@fwrite($file_hander, $content))
        {
            @fclose($file_hander);

            return true;
        }

        @fclose($file_hander);

        return false;
    }

    /**
     * Read data from file
     *
     * @param string $file
     * @return  false|resource
     */

    /**
     * @param string $file
     * @return false|string
     */
    public function read(string $file)
    {
        $file_hander = @fopen($file, 'rb');

        $content = @fread($file_hander, @filesize($file));
        if (false !== $content)
        {
            @fclose($file_hander);

            return $content;
        }

        @fclose($file_hander);

        return false;
    }

    /**
     * @param string $file
     * @param string $options
     * @return false|resource
     */
    public function open(string $file, string $options = 'rb')
    {
        return @fopen($file, $options);
    }

    /**
     * @param $stream
     * @param int|null $length
     * @return false|string
     */
    public function gets($stream, ?int $length = null): false|string
    {
        return fgets($stream, $length);
    }

    public function isReadable(string $file): bool
    {
        return is_readable($file);
    }

    /**
     * @param string $extension
     * @param bool $first
     * @return bool|mixed
     */
    public function get_mime_type(string $extension, $first = true)
    {
        if (array_key_exists($extension, $this->get_all_mime_types()))
        {
            if (is_array($this->mime_types[$extension]) && $first)
            {
                return $this->mime_types[$extension][0];
            }

            return $this->mime_types[$extension];
        }

        return false;
    }

    /**
     * @return array
     */
    public function get_all_mime_types(): array
    {
        return $this->mime_types;
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function file_exists(string $filename): bool
    {
        return file_exists($filename);
    }

    /**
     * @param string $filename
     * @return false|int
     */
    public function readfile(string $filename)
    {
        return @readfile($filename);
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function is_file(string $filename): bool
    {
        return is_file($filename);
    }

    /**
     * @param string $filename
     * @return string
     */
    public function file_get_contents(string $filename): string
    {
        return file_get_contents($filename);
    }

    /**
     * @param string $filename
     * @return mixed
     */
    public function load_file(string $filename)
    {
        /** @noinspection PhpIncludeInspection */
        return include $filename;
    }

    public function fileExtensionFromMimeType(string $mimeType): string
    {
        $list = $this->get_all_mime_types();
        foreach ($list as $ext => $value)
        {
            if (is_array($value))
            {
                if (in_array($mimeType, $value, true))
                {
                    return $ext;
                }
            }
            elseif ($value === $mimeType)
            {
                return $ext;
            }
        }

        return '';
    }
}