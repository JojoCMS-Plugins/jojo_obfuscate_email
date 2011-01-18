<?php
/**
 * Jojo CMS - Obfuscate Email
 *
 * Copyright 2007-2008 Jojo CMS
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Thomas Puppe <thomas@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 * @package jojo_obfuscate_email
 */

 
/* Add filter */
Jojo::addFilter('output', 'obfuscateEmail', 'jojo_obfuscate_email');


/* Add Options */
$_options[] = array(
    'id'          => 'obfuscate_email_method',
    'category'    => 'Config',
    'label'       => 'Obfuscation method',
    'description' => 'Method for obfuscating Email addresses',
    'type'        => 'radio',
    'default'     => 'javascript',
    'options'     => 'unicode,javascript'
);