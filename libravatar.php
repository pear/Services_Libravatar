<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP support for the Libravatar.org service.
 *
 * PHP version 5
 *
 * The MIT License
 *
 * Copyright (c) 2011 HTML_Libravatar committers.
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
 * @category  HTML
 * @package   HTML_Libravatar
 * @author    Melissa Draper <melissa@meldraweb.com>
 * @copyright 2011 HTML_Libravatar committers.
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version   SVN: <package_version>
 * @link      http://pear.php.net/package/HTML_Libravatar
 * @since     File available since Release 0.1
 */

/**
 * PHP support for the Libravatar.org service.
 *
 * @category  HTML
 * @package   HTML_Libravatar
 * @author    Melissa Draper <melissa@meldraweb.com>
 * @copyright 2011 HTML_Libravatar committers.
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version   Release: <package_version>
 * @link      http://pear.php.net/package/HTML_Libravatar
 * @since     Class available since Release 0.1
 */
class Libravatar
{

    /**
     *  Composes a URL for the identifier and options passed in
     *
     *  Compose a full URL as specified by the Libravatar API, based on the
     *  email address or openid URL passed in, and the options specified.
     *
     *  @param string $identifier a string of either an email address 
     *                              or an openid url
     *  @param array  $options    an array of (bool) https, (string) algorithm
     *                              (string) s or size, (string) d or default
     *
     *  @return  string  A string of a full URL for an avatar image
     *
     *  @access public
     *  @static
     *  @since Method available since Release 0.1
     */
    public function url($identifier, $options = array())
    {

        // If no identifier has been passed, set it to a null.
        // This way, there'll always be something returned.
        if (!$identifier) {
            $identifier = null;
        }

        // If the algorithm has been passed in $options, send it on.
        // This will only affect email functionality.
        if (isset($options['algorithm']) && $options['algorithm'] === true) {
            $identiferHash = $this::identiferHash($identifier,
            $options['algorithm']);
        } else {
            $identiferHash = $this::identiferHash($identifier);
        }

        // Get the domain so we can determine the SRV stuff for federation
        $domain = $this::domainGet($identifier);

        // If https has been specified in $options, make sure we make the
        // correct SRV lookup
        if (isset($options['https']) && $options['https'] === true) {
            $service  = $this::srvGet($domain, true);
            $protocol = 'https';
        } else {
            $service  = $this::srvGet($domain);
            $protocol = 'http';
        }

        // We no longer need these, and they will pollute our query string
        unset($options['algorithm']);
        unset($options['https']);

        // If there are any $options left, we want to make those into a query
        $params = null;
        if (count($options) > 0) {
            $params = '?' . http_build_query($options);
        }

        // Compose the URL from the pieces we generated
        $url = $protocol . '://' . $service . '/avatar/' . $identiferHash . $params;

        // Return the URL string
        return $url;

    }

    /**
     *  Create a hash of the identifier.
     *  
     *  Create a hash of the email address or openid passed in. Algorithm
     *  used for email address ONLY can be varied. Either md5 or sha256
     *  are supported by the Libravatar API. Will be ignored for openid.
     *
     *  @param string $identifier A string of the email address or openid URL
     *  @param string $hash       A string of the hash algorithm type to make 
     *
     *  @return string  A string hash of the identifier.
     *
     *  @access protected
     *  @since Method available since Release 0.1
     */
    protected function identiferHash($identifier, $hash = 'md5')
    {

        // Is this an email address or an OpenID account
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // If email, we can select our algorithm. Default to md5 for 
            // gravatar fallback.
            return hash($hash, $identifier);
        } else if (filter_var($identifier, 
            FILTER_VALIDATE_URL, 
            FILTER_FLAG_PATH_REQUIRED)
        ) {
            // If this is an OpenID, split the string and make sure the 
            // formatting is correct. See the Libravatar API for more info.
            // http://wiki.libravatar.org/api/
            $url     = parse_url($identifier);
            $hashurl =  strtolower($url['scheme']) . '://' .
                        strtolower($url['host']) .
                        $url['path'];
            return hash('sha256', $hashurl);
        }

    }

    /**
     *  Grab the domain from the identifier.
     * 
     *  Extract the domain from the Email or OpenID.
     *
     *  @param string $identifier A string of the email address or openid URL
     *
     *  @return string  A string of the domain to use
     *
     *  @access protected
     *  @since Method available since Release 0.1
     */
    protected function domainGet($identifier)
    {

        // What are we, email or openid? Split ourself up and get the
        // important bit out.
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $email = explode('@', $identifier);
            return $email[1];
        } else if (filter_var($identifier, 
            FILTER_VALIDATE_URL, 
            FILTER_FLAG_PATH_REQUIRED)
        ) {
            $url = parse_url($identifier);
            return $url['host'];
        }

    }

    /**
     *  Get the target to use.
     *  
     *  Get the SRV record, filtered by priority and weight. If our domain
     *  has no SRV records, fall back to Libravatar.org
     *
     *  @param string  $domain A string of the domain we extracted from the
     *                         provided identifer with domainGet()
     *  @param boolean $https  Whether or not to look for https records
     *
     *  @return string  The target URL.
     *
     *  @access protected
     *  @since Method available since Release 0.1
     */
    protected function srvGet($domain, $https = false)
    {

        // Are we going secure? Set up a fallback too.
        if (isset($https) && $https === true) {
            $subdomain = '_avatars-sec._tcp.';
            $fallback  = 'seccdn.';
        } else {
            $subdomain = '_avatars._tcp.';
            $fallback  = 'cdn.';
        }

        // Lets try get us some records based on the choice of subdomain
        // and the domain we had passed in.
        $srv = dns_get_record($subdomain . $domain, DNS_SRV);

        // Did we get anything? No?
        if (count($srv) == 0) {
            // Then let's try Libravatar.org.
            return $fallback . 'libravatar.org';
        }

        // Sort by the priority. We must get the lowest.
        usort($srv, 'comparePriority');

        $top = $srv[0];

        foreach ($srv as $s) {
            if ($s['pri'] == $top['pri']) {
                $pri[] = $s;
            }
        }

        // If we have a choice, get the lowest weighted.
        usort($pri, 'compareWeight');        

        // Grab the topmost record.
        $record = array_shift($pri);

        return $pri['target'];

    }

    /**
     *  Sorting function for record weights.
     *
     *  @param mixed $a A mixed value passed by usort()
     *  @param mixed $b A mixed value passed by usort()
     *
     *  @return mixed  The result of the comparison
     *
     *  @access protected
     *  @since Method available since Release 0.1
     */
    protected function compareWeight($a, $b)
    {
        return $a['weight'] - $b['weight'];
    }

    /**
     *  Sorting function for record priorities.
     *
     *  @param mixed $a A mixed value passed by usort()
     *  @param mixed $b A mixed value passed by usort()
     *
     *  @return mixed  The result of the comparison
     *
     *  @access protected
     *  @since Method available since Release 0.1
     */
    protected function comparePriority($a, $b)
    {
        return $a['pri'] - $b['pri'];
    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>

