<?php

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
 * @category   HTML
 * @package    HTML_Libravatar
 * @author     Melissa Draper <melissa@meldraweb.com>
 * @copyright  2011 HTML_Libravatar committers.
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @since      File available since Release 0.1
 */

/**
 * PHP support for the Libravatar.org service.
 *
 * @category   HTML
 * @package    HTML_Libravatar
 * @author     Melissa Draper <melissa@meldraweb.com>
 * @copyright  2011 HTML_Libravatar committers.
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    Release: 0.1
 * @since      Class available since Release 0.1
 */
class Libravatar {

    /**
     *  Compose a URL for the identifier and options passed in.
     *
     *  Compose a full URL as specified by the Libravatar API, based on the
     *  email address or openid URL passed in, and the options specified.
     *
     *  $identifier     string      Either an email address or an openid url.
     *  $options        array       https, algorithm, s/size, d/default.
     *
     *  @return         string      A full URL.
     */
    public function url($identifier, $options = array()) {

        // If no identifier has been passed, set it to a null.
        // This way, there'll always be something returned.
        if(!$identifier) {
            $identifier = null;
        }

        // If the algorithm has been passed in $options, send it on.
        // This will only affect email functionality.
        if(isset($options['algorithm']) && $options['algorithm'] === true) {
            $id_hash = $this::id_hash($identifier, $options['algorithm']);
        } else {
            $id_hash = $this::id_hash($identifier);
        }

        // Get the domain so we can determine the SRV stuff for federation.
        $domain = $this::domain_get($identifier);

        // If https has been specified in $options, make sure we make the
        // correct SRV lookup.
        if(isset($options['https']) && $options['https'] === true) {
            $service = $this::srv_get($domain, true);
            $protocol = 'https';
        } else {
            $service = $this::srv_get($domain);
            $protocol = 'http';
        }

        // We no longer need these, and they'll pollute our query string.
        unset($options['algorithm']);
        unset($options['https']);

        // If there are any $options left, we want to make those into a
        // query.
        $params = null;
        if(count($options) > 0) {
            $params = '?' . http_build_query($options);
        }

        // Time to compose our URL.
        $url = $protocol . '://' . $service . '/avatar/' . $id_hash . $params;

        // GIMME!
        return $url;

	}

    /**
     *  Create a hash of the identifier.
     *  
     *  Create a hash of the email address or openid passed in. Algorithm
     *  used for email address ONLY can be varied. Either md5 or sha256
     *  are supported by the Libravatar API. Will be ignored for openid.
     *
     *  $identifier     string      The email address or openid URL.
     *  $hash           string      The type of hash to make, by algo name.
     *
     *  @return         string      A hash of the identifier.
     */
    protected function id_hash($identifier, $hash = 'md5') {

        // What are we? Email or OpenID
        if(filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // If we're an email, we can select our algorithm.
            // However it defaults to md5 for gravatar's sake.
            return hash($hash, $identifier);
        } else if (filter_var($identifier, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            // If we're an OpenID we need to split ourself up a bit and
            // make sure formatting is correct. See API for more info.
            $url = parse_url($identifier);
            $hashurl =  strtolower($url['scheme']) . '://' .
                        strtolower($url['host']) .
                        $url['path';
            return hash('sha256', $hashurl);
        }

        // If we have been passed nothing, we give nothing back.
        return null;

    }

    /**
     *  Grab the domain from the identifier.
     * 
     *  Extract the domain from the Email or OpenID.
     *
     *  $identifier     string      The email or openid.
     *
     *  @return         string      The domain.
     */
    protected function domain_get($identifier) {

        // What are we, email or openid? Split ourself up and get the
        // important bit out.
        if(filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $email = explode('@', $identifier);
            return $email[1];
        }
        else if (filter_var($identifier, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $url = parse_url($identifier);
            return $url['host'];
        }

    }

    /**
     *  Get the target to use.
     *  
     *  Get the SRV record, filtered by priority and weight. If our domain
     *  has nothing, fall back to the Libravatar.org stuff.
     *
     *  $domain     string      The domain we extracted with domain_get().
     *  $https      boolean     Whether or not to go secure.
     *
     *  @return     string      The target URL.
     */
    protected function srv_get($domain, $https = false) {

        // Are we going secure? Set up a fallback too.
        if (isset($https) && $https === true) {
            $subdomain = '_avatars-sec._tcp.';
            $fallback = 'seccdn.';
        } else {
            $subdomain = '_avatars._tcp.';
            $fallback = 'cdn.';
        }

        // Lets try get us some records based on the choice of subdomain
        // and the domain we had passed in.
        $srv = dns_get_record($subdomain . $domain, DNS_SRV);

        // Did we get anything? No?
        if (count($srv) == 0) {
            // Then let's try Libravatar.org
            // ...this should work.
            return $fallback . 'libravatar.org';
        }

        // Sort by the priority. We must get the lowest.
        usort($srv, 'comparePriority');

        $top = $srv[0];

        foreach ($srv as $s) {
            if($s['pri'] == $top['pri']) {
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
     */
    protected function compareWeight($a, $b) {
      return $a['weight'] - $b['weight'];
    }

    /**
     *  Sorting function for record priorities.
     */
    protected function comparePriority($a, $b) {
      return $a['pri'] - $b['pri'];
    }

}
