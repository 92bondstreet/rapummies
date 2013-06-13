<?php
/**
 * Rapummies
 *
 * Rap lines for dummies. PHP plugin for Rapgenius.
 * Get rap lines and annotation/interpretation
 *
 * Copyright (c) 2013 - 92 Bond Street, Yassine Azzout
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 *	The above copyright notice and this permission notice shall be included in
 *	all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package Rapummies
 * @version 1.0
 * @copyright 2013 - 92 Bond Street, Yassine Azzout
 * @author Yassine Azzout
 * @link http://www.92bondstreet.com Rapummies
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
 
require_once('rapummies.php');

// Init constructor with false value: no dump log file
$RapGenius = new Rapummies();

// Get all lines and annotation/interpretation from song url
$results = $RapGenius->raplines_song("http://rapgenius.com/Kanye-west-no-church-in-the-wild-lyrics");
print_r($results);

// Get all lines and annotation/interpretation of artist/singer/producer
$results = $RapGenius->raplines_artist("Capone N Noreaga");

// Save results in Database
$save = $RapGenius->save($results, "raplines");
var_dump($save);

?>