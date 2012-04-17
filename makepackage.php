<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP support for the Libravatar.org service.
 *
 * PHP version 5
 *
 * The MIT License
 *
 * Copyright (c) 2011 Services_Libravatar committers.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Services
 * @package   Services_Libravatar
 * @author    Melissa Draper <melissa@meldraweb.com>
 * @copyright 2011 Services_Libravatar committers.
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version   SVN: <package_version>
 * @link      http://pear.php.net/package/Services_Libravatar
 * @since     File available since Release 0.1.0
 */
/**
 * This is the only setup function needed
 */
require_once 'PEAR/PackageFileManager2.php';
// recommended - makes PEAR_Errors act like exceptions (kind of)
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$packagexml = new PEAR_PackageFileManager2();
$packagexml->setOptions(
    array(
        'filelistgenerator' => 'file',
        'packagedirectory' => dirname(__FILE__),
        'baseinstalldir' => '',
        'ignore' => array('build/', 'build.xml'),
        'include' => array('Services/', 'docs/', 'LICENCE'),
        'simpleoutput' => true,
    )
);
$packagexml->setPackageType('php');
$packagexml->addRelease();
$packagexml->setPackage('Services_Libravatar');
$packagexml->setChannel('pear.php.net');
$packagexml->setReleaseVersion('0.1.0');
$packagexml->setAPIVersion('0.1.0');
$packagexml->setReleaseStability('alpha');
$packagexml->setAPIStability('alpha');
$packagexml->setSummary('API interfacing class for libravatar.org');
$packagexml->setDescription('Allows php applications to implement libravatar.org');
$packagexml->setNotes('Initial release');
$packagexml->setPhpDep('5.3.0');
$packagexml->setPearinstallerDep('1.4.0a12');
$packagexml->addMaintainer(
    'lead',
    'elky',
    'Melissa Draper',
    'melissa@meldraweb.com'
);
$packagexml->setLicense(
    'MIT License',
    'http://www.opensource.org/licenses/mit-license.html'
);
$packagexml->addGlobalReplacement('package-info', '@PEAR-VER@', 'version');
$packagexml->generateContents();
if (isset($_GET['make']) 
    || (isset($_SERVER['argv']) 
    && @$_SERVER['argv'][1] == 'make')
) {
    $packagexml->writePackageFile();
} else {
    $packagexml->debugPackageFile();
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
