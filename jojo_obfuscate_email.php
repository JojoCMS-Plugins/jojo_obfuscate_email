<?php

/**
 *
 * Copyright 2009 Thomas Puppe <code@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Thomas Puppe <code@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

class JOJO_Plugin_jojo_obfuscate_email extends JOJO_Plugin
{
    public static function obfuscateEmail($content)
    {
        $obfuscationMethod = Jojo::getOption('obfuscate_email_method', false);
        if (!$obfuscationMethod || strpos($content, "@")===false) return $content;
    
        /* check for an Email Address in an <a> link */
        $apattern = '~<a[^>]*mailto:([a-z0-9_.+-]+@[a-z0-9.]+[a-z0-9]{2,4})(.*)[^"]*>([^<]*?)</a>~i';
        $pattern = "~[a-z0-9_.+-]+@[a-z0-9.]+[a-z0-9]{2,4}~i";
        $matches = array();
        preg_match_all($apattern, $content, $matches);

        if ($obfuscationMethod=="javascript") {
            $contacturl = '';
            $contacttitle = '';
            // work out the contact page to use when javascript isn't available
            if (class_exists('Jojo_Plugin_Jojo_contact')) {
                global $page;
                $contactpages = Jojo::selectQuery("SELECT pageid, pg_title, pg_url FROM {page} WHERE pg_link=?", array('jojo_plugin_jojo_contact'));
                if (count($contactpages)==1) {
                    $contactpage = $contactpages[0];
                    $contacturl = Jojo::getPageUrlPrefix($contactpage['pageid']) . (!empty($contactpage['pg_url']) ? $contactpage['pg_url'] : $contactpage['pageid'] . '/' . Jojo::cleanURL($contactpage['pg_title'])) . '/';
                     $contacttitle = htmlspecialchars($contactpage['pg_title'], ENT_COMPAT, 'UTF-8', false);
                } elseif ($contactpages) {
                    $thispageroot = Jojo::getSectionRoot($page->page['pageid']);
                    foreach ($contactpages as $c) {
                        if (Jojo::getSectionRoot($c['pageid'])==$thispageroot) {
                            $contacturl = Jojo::getPageUrlPrefix($c['pageid']) . (!empty($c['pg_url']) ? $c['pg_url'] : $c['pageid'] . '/' . Jojo::cleanURL($c['pg_title'])) . '/';
                            $contacttitle = htmlspecialchars($c['pg_title'], ENT_COMPAT, 'UTF-8', false);
                            break;
                        }
                    }
                }
           }
            $script = '<script type="text/javascript" language="javascript">' . "\n";
            $script .= '$(document).ready(function(){' . "\n";
            foreach($matches[0] as $k=>$match)
            {
                $pos = strpos($content, $match);
                $mailto = self::string2unicode('mailto');
                $email = explode('@', $matches[1][$k]);
                $emailextra = isset($matches[2][$k]) ? "+'" . rtrim($matches[2][$k], '"') . "'" : '';
                $person = $email[0];
                $domain = explode('.', $email[1]);
                $domainname = strrev(array_shift($domain));
                $domainextension = implode('.', $domain);
                $obfusc = "'" . $domainextension . "','" . $person. "','" . $domainname . "'";
 
                $newtag = '<a href="' . $contacturl . '" onmouseover="this.href=xyz(' . $obfusc . ')' . ($emailextra ?  $emailextra : '') . ';" id ="obscuredadd' . $k . '">' . $contacttitle . '</a>' ;
                $script .= "$('#obscuredadd" . $k . "').html(xyz(" . $obfusc . ", false));" . "\n";

                $content =  substr_replace($content, $newtag, $pos, strlen($match));
            }
            $script .= '});' . "\n" . '</script>' . "\n";
            $endpos = strpos($content, '</body>');
            $content =  substr_replace($content, $script, $endpos, 0);

        } elseif($obfuscationMethod=="unicode") {
            foreach($matches[0] as $k=>$match)
            {
                $pos = strpos($content, $match);
                $newtag = str_replace('mailto', self::string2unicode('mailto'), $match); //unicode mailto
                $newtag = str_replace($matches[1][$k], self::string2unicode($matches[1][$k]), $newtag); // unicode linked email address
                $newtag = strpos($newtag, '@')===false ?  str_replace($matches[2][$k], self::string2unicode($matches[2][$k]), $newtag) : $newtag; // unicode link text if it contains an email address 
                $content =  substr_replace($content, $newtag, $pos, strlen($match)); // replace original <a> tag with unicoded version
            }
        } else {
            return $content;
        }
        return $content;
    }

    public function string2unicode($input)
    {
        $output = "";
        $string = strval($input);
        for ($i = 0; $i < strlen($input); $i++)
        {
            $unicodevalue = ord($string[$i]);
            $output.= "&#".$unicodevalue.";";
        }
        return $output;
    }

}