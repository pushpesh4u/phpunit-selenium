<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2010-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit_Selenium
 * @author     Giorgio Sironi <info@giorgiosironi.com>
 * @copyright  2010-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.2.13
 */

/**
 * Sends a file to a RC
 *
 * @package    PHPUnit_Selenium
 * @author     Kevin Ran  <heilong24@gmail.com>
 * @copyright  2010-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.2.13
 */
class PHPUnit_Extensions_Selenium2TestCase_SessionCommand_File
    extends PHPUnit_Extensions_Selenium2TestCase_Command
{

    /**
     * @var
     */
    private static $_zipArchive;

    public function __construct($argument, PHPUnit_Extensions_Selenium2TestCase_URL $url)
    {
        if ( !is_file( $argument ) ) {

          throw new BadMethodCallException( "No such file: {$argument}" );

        } // if !is_file

        $zipfile_path = $this->zipArchiveFile( $file_path );
        $contents     = @file_get_contents( $zipfile_path );

        if( $file === false ) {

          throw new Exception( "Unable to read generated zip file: {$zipfile_path}" );

        } // if !file

        $file = base64_encode( $file );

        parent::__construct( array( 'file' => $file ), $url );

        unlink( $zipfile_path );
    }

    public function httpMethod()
    {
        return 'POST';
    }

    /**
     * Creates a temporary file from the given file
     * Adds it to a zip archive
     *
     * @param   string $file_path   FQ path to file
     * @return  string              Generated zip file
     */
    protected function zipArchiveFile( $file_path ) {

      // file MUST be readable
      $file_data = @file_get_contents($file_path);

      if( $file_data === false ) {

        throw new Exception( "Unable to get contents of {$file_path}" );

      } // if !file_data

      $filename_hash  = sha1( time().$file_path );

      // create zip archive file
      $zip            = $this->_getZipArchiver();
      $tmp_dir        = $this->_getTmpDir();
      $zip_filename   = "{$tmp_dir}{$filename_hash}.zip";

      if( $zip->open( $zip_filename, ZIPARCHIVE::CREATE ) === false ) {

        throw new Exception( "Unable to create zip archive: {$zip_filename}" );

      } // if !open

      // generate a temporary file to use for the archive
      $ext      = pathinfo( $file_path, PATHINFO_EXTENSION );
      $tmp_file = "{$tmp_dir}{$filename_hash}.{$ext}";

      if( @file_put_contents( $tmp_file, $file_data ) === false ) {

        throw new Exception( "Unable to create temporary file for xfer: {$tmp_file}" );

      } // if !file_put_contents

      // add tmp file into zip archive
      $zip->addFile( $tmp_file );
      $zip->close();

      unlink( $tmp_file );

      return $zip_filename;

    } // zipArchiveFile

    /**
     * Returns a runtime instance of a ZipArchive
     *
     * @return ZipArchive
     */
    protected function _getZipArchiver() {

      // create ZipArchive if necessary
      if ( !static::$_zipArchive ) {

        static::$_zipArchive = new ZipArchive();

      } // if !zipArchive

      return static::$_zipArchive;

    } // _getZipArchiver

    /**
     * Calls sys_get_temp_dir and ensures that it has a trailing slash
     * ( behavior varies across systems )
     *
     * @return string
     */
    protected function _getTmpDir() {

      return rtrim( sys_get_temp_dir(), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

    } // _getTmpDir

}
