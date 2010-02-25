<?php

/**
 * Blip! (http://blip.pl) communication library.
 *
 * @author Marcin Sztolcman <marcin /at/ urzenia /dot/ net>
 * @version 0.02.6
 * @version $Id: blipapi.php 7 2008-05-25 13:30:53Z urzenia $
 * @copyright Copyright (c) 2007, Marcin Sztolcman
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License v.2
 * @package blipapi
 */

/**
 * Blip! (http://blip.pl) communication library.
 *
 * @author Marcin Sztolcman <marcin /at/ urzenia /dot/ net>
 * @version 0.02.6
 * @version $Id: blipapi.php 7 2008-05-25 13:30:53Z urzenia $
 * @copyright Copyright (c) 2007, Marcin Sztolcman
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License v.2
 * @package blipapi
 */
class BlipApi {
    /**
     * CURL handler
     *
     * @access protected
     * @var resource
     */
    protected $_ch;

    /**
     * Login to Blip!
     *
     * @access protected
     * @var string
     */
    protected $_login;

    /**
     * Password to Blip!
     *
     * @access protected
     * @var string
     */
    protected $_password;

    /**
     * Useragent
     *
     * @access protected
     * @var string
     */
    protected $_uagent      = 'BlipApi/0.02.6 (http://blipapi.googlecode.com)';

    /**
     *
     *
     * @access protected
     * @var string
     */
    protected $_referer     = 'http://urzenia.net';

    /**
     * URI to API host
     *
     * @access protected
     * @var string
     */
    protected $_root        = 'http://api.blip.pl';

    /**
     * Mime type for "Accept" header in request
     *
     * @access protected
     * @var string
     */
    protected $_format      = 'application/json';

    /**
     *
     *
     * @access protected
     * @var string
     */
    protected $_timeout     = 10;

    /**
     * Debug mode flag
     *
     * @access protected
     * @var bool
     */
    protected $_debug       = false;

    /**
     * Headers to be sent
     *
     * @access protected
     * @var array
     */
    protected $_headers     = array ();

    /**
     * Available parsers for reply.
     *
     * As key need have to be mime type of reply, and for value - function name to call, or 2
     * values array: 0 - object, 1 - method to call.
     *
     * @access protected
     * @var array
     */
    protected $_parsers     = array (
        'application/xml'       => 'simplexml_load_string',
        'application/json'      => 'json_decode',
    );

    /**
     * BlipApi constructor
     *
     * Initialize CURL handler ({@link $_ch}). Throws RuntimeException exception if no CURL extension found.
     *
     * @param string $login
     * @param string $passwd
     */
    public function __construct ($login=null, $passwd=null) {
        if (!function_exists ('curl_init')) {
            throw new RuntimeException ('CURL missing!', -1);
        }

        $this->_login       = $login;
        $this->_password    = $passwd;

        # inicjalizujemy handler curla
        $this->_ch = @curl_init ($this->_root);
        if (!$this->_ch) {
            throw new RuntimeException ('CURL initialize error: '. curl_error ($this->_ch), curl_errno ($this->_ch));
        }

        # ustawiamy domyślne nagłówki
        $this->_headers = array (
            'Accept'        => $this->format,
            'X-Blip-API'    => '0.02',
        );
    }

    /**
     * BlipApi destructor
     *
     * Close CURL handler, if active
     */
    public function __destruct () {
        if (is_resource ($this->_ch)) {
            @curl_close ($this->_ch);
        }
    }

    /**
     * Magic method to execute commands as their names, without 'execute'.
     *
     * Of course, execute is used by __call internally.
     *
     * @param string $fn name of command
     * @param array $args arguments
     * @access public
     * @return return of {@link execute}
     */
    public function __call ($fn, $args) {
        if (!method_exists ($this, '_cmd__'.$fn)) {
            throw new BadMethodCallException (sprintf ('Unknown method: "%s".', $fn), -1);
        }

        array_unshift ($args, $fn);
        return call_user_func_array (array ($this, 'execute'), $args);
    }

    /**
     * Setter for some options
     *
     * For specified keys, call proper __set_* method. Throws InvalidArgumentException exception when incorrect key was
     * specified.
     *
     * @param string $key name of property to set
     * @param mixed $value value of property
     * @access public
     */
    public function __set ($key, $value) {
        if (!in_array ($key, array ('debug', 'format', 'uagent', 'referer', 'timeout', 'headers'))) {
            throw new InvalidArgumentException (sprintf ('Unknown param: "%s".', $key), -1);
        }

        return call_user_func (array ($this, '__set_'.$key), $value);
    }

    /**
     * Getter for some options
     *
     * For specified keys, return them. Throws InvalidArgumentException exception when incorrect key was specified.
     *
     * @param string $key name of property to return
     * @return mixed
     * @access public
     */
    public function __get ($key) {
        if (!in_array ($key, array ('debug', 'format', 'uagent', 'referer', 'timeout', 'headers'))) {
            throw new InvalidArgumentException (sprintf ('Unknown param: "%s".', $key), -1);
        }

        $key = '_'.$key;
        return $this->$key;
    }

    /**
     * Setter for {@link $_debug} property
     *
     * @param bool $enable
     * @access protected
     */
    protected function __set_debug ($enable = null) {
        $this->_debug = $enable ? true : false;

        curl_setopt($this->_ch, CURLOPT_VERBOSE, $this->_debug);
    }

    /**
     * Setter for {@link $_format} property
     *
     * Format have to be string in mime type format. In other case, there will be prepended 'application/' prefix.
     *
     * @param string $format
     * @access protected
     */
    protected function __set_format ($format) {
        # jeśli nie jest to pełen typ mime, to doklejamy na początek 'application/'
        if ($format && strpos ($format, '/') === false) {
            $format = 'application/'. $format;
        }
        $this->_format = $format;
    }

    /**
     * Setter for {@link $_uagent} property
     *
     * @param string $uagent
     * @access protected
     */
    protected function __set_uagent ($uagent) {
        $this->_uagent = (string) $uagent;
        curl_setopt ($this->_ch, CURLOPT_USERAGENT, $this->_uagent);
    }

    /**
     * Setter for {@link $_referer} property
     *
     * @param string $referer
     * @access protected
     */
    protected function __set_referer ($referer) {
        $this->_referer = (string) $referer;
        curl_setopt ($this->_ch, CURLOPT_REFERER, $referer);
    }

    /**
     * Setter for {@link $_timeout} property
     *
     * @param string $timeout
     * @access protected
     */
    protected function __set_timeout ($timeout) {
        $this->_timeout = (int) $timeout;
        curl_setopt ($this->_ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
    }

    /**
     * Setter for {@link $_headers} property
     *
     * @param array|string $headers headers in format specified at {@link _parse_headers}
     * @access protected
     */
    protected function __set_headers ($headers) {
        $this->_headers = $this->_parse_headers ($headers);
    }

    /**
     * Parsing headers parameter to correct format
     *
     * Param $headers have to be an array, where key is header name, and value - header value, or string in
     * 'Header-Name: Value'.
     * Throws UnexpectedValueException of incorect type of $headers is given
     *
     * @param array|string $headers
     * @access protected
     */
    protected function _parse_headers ($headers) {
        if (!$headers) {
            $headers = array ();
        }
        else if (is_string ($headers) && preg_match ('/^(\w+):\s(.*)/', $headers, $match)) {
            $headers = array ( $match[1] => $match[2] );
        }
        else if (!is_array ($headers)) {
            throw new UnexpectedValueException (sprintf ('%s::$headers have to be an array or string, but %s given.',
                __CLASS__,
                gettype ($headers)), -1
            );
        }

        return $headers;
    }

    /**
     * Add or replace headers to be sent to remote server
     *
     * @param array|string $headers headers in format specified at {@link _parse_headers}
     * @access public
     * @return bool false if empty array specified
     */
    public function headers_set ($headers) {
        $headers = $this->_parse_headers ($headers);
        if (!$headers) {
            return false;
        }

        foreach ($headers as $k=>$v) {
            $this->_headers[$k] = $v;
        }
        return true;
    }

    /**
     * Remove specified header
     *
     * @param array|string $headers headers in format specified at {@link _parse_headers}
     * @access public
     * @return bool false if empty array specified
     */
    public function headers_remove ($headers) {
        $headers = $this->_parse_headers ($headers);
        if (!$headers) {
            return false;
        }

        foreach ($headers as $k=>$v) {
            if (isset ($this->_headers[$k])) {
                unset ($this->_headers[$k]);
            }
        }
        return true;
    }

    /**
     * Get headers set to sent
     *
     * $headers have to be:
     *  * array - with names of headers values to return
     *  * string - with single header name
     *  * null - if all headers have to be returned
     *
     * @param mixed $headers
     * @access public
     * @return array
     */
    public function headers_get ($headers=null) {
        if (is_null ($headers)) {
            return $this->_headers;
        }
        else if (is_string ($headers)) {
            $headers = array ($headers);
        }
        else if (!is_array ($headers)) {
            throw new UnexpectedValueException ('Incorrect value specified.', -1);
        }

        $ret = array ();
        foreach ($headers as $header) {
            $ret[$header] = (isset ($this->_headers[$header])) ? $this->_headers[$header] : null;
        }
        return $ret;
    }

    /**
     * Create connection with CURL, setts some CURL options etc
     *
     * Throws RuntimeException exception when CURL initialization has failed
     *
     * @param string $login as in {@link __construct}
     * @param string $passwd as in {@link __construct}
     * @access public
     * @return bool always true
     */
    public function connect ($login=null, $passwd=null) {
        if (!is_null ($login)) {
            $this->_login       = (string) $login;
        }
        if (!is_null ($passwd)) {
            $this->_password    = (string) $passwd;
        }

        # standardowe opcje curla
        $curlopts = array (
            CURLOPT_USERAGENT       => $this->uagent,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_HEADER          => true,
            CURLOPT_HTTP200ALIASES  => array (201, 204),
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_0,
            CURLOPT_CONNECTTIMEOUT  => 10,
        );
         
        # jeśli podane login i hasło, to logujemy się
        if ($this->_login && !is_null ($this->_password)) {
            $curlopts[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            $curlopts[CURLOPT_USERPWD]  = sprintf ('%s:%s', $this->_login, $this->_password);
        }

        # ustawiamy opcje
        curl_setopt_array ($this->_ch, $curlopts);     

        return true;
    }

    /**
     * Execute command and parse reply
     *
     * Throws InvalidArgumentException exception when specified command does not exists, or RuntimeException
     * when exists some CURL error or returned status code is greater or equal 400.
     *
     * @param string $command command to execute
     * @param mixed $options,... options passed to proper command method (prefixed with _cmd__)
     * @access public
     * @return array like {@link __query}
     */
    public function execute () {
        if (!func_num_args ()) {
            throw new InvalidArgumentException ('Command missing.', -1);
        }

        $args   = func_get_args ();
        $cmd    = array_shift ($args);
        if (!method_exists ($this, '_cmd__'.$cmd)) {
            throw new InvalidArgumentException ('Command not found.', -1);
        }
        $this->_debug ('CMD: '. $cmd);

        # wywołujemy wybraną metodę - metody komend zawsze zwracają wynik curl_exec
        $reply = call_user_func_array (array ($this, '_cmd__'.$cmd), $args);
        if (!$reply) {
            throw new RuntimeException ('CURL Error: '. curl_error ($this->_ch), curl_errno ($this->_ch));
        }

        $this->_debug (print_r ($reply, 1));
        $reply = $this->__parse_reply ($reply);

        if ($reply['status_code'] >= 400) {
            throw new RuntimeException ($reply['status_body'], $reply['status_code']);
        }

        return $reply;
    }

    /**
     * Print debug mesage if debug mode is enabled
     *
     * @param string $msg,... messages to print to stdout
     * @access protected
     * @return bool
     */
    protected function _debug () {
        if (!$this->_debug) {
            return;
        }

        $args = func_get_args ();
        printf ("<pre style='border: 1px solid black; padding: 4px;'><b>DEBUG MSG:</b>\n%s</pre>",
            join ("<br />\n", $args)
        );
        return 1;
    }

    /**
     * Return array with CURLOPT_* constants values replaced by these names. For debugging purposes only.
     *
     * @param array $opts array with CURLOPTS_* as keys
     * @return array the same as $opts, but keys are replaced by names of constants
     * @access protected
     */
    protected function _debug_curlopts ($opts) {
        $copts = array ();
        foreach (get_defined_constants () as $k => $v) {
            if (strlen ($k) > 8 && substr ($k, 0, 8) == 'CURLOPT_') {
                $copts[$v] = $k;
            }
        }

        $ret = array ();
        foreach ($opts as $k => $v) {
            if (isset ($copts, $k)) {
                $ret[$copts[$k]] = $v;
            }
            else {
                $ret[$k] = $v;
            }
        }

        return $ret;
    }

    /**
     * Converts specified array of params to query string
     *
     * @param array $arr
     * @return string
     * @access protected
     */
    protected function __arr2qstr ($arr) {
        $ret = array ();
        foreach ($arr as $k => $v) {
            $ret[] = sprintf ('%s=%s', $k, $v);
        }
        return implode ('&', $ret);
    }

    /**
     * Helper for {@link __query} - set connection params for POST HTTP method.
     *
     * Recognised options:
     *  * multipart - (bool) if true, data is send as multipart/form-data (this is used for sending file)
     *
     * @param array $data
     * @param array $opts additional options
     * @return array CURL options
     * @access protected
     */
    protected function __query__post ($data, $opts=array ()) {
        if (!isset ($opts['multipart']) || !$opts['multipart']) {
            $data = $this->__arr2qstr ($data);
        }

        $curlopts = array (
            CURLOPT_POST        => true,
            CURLOPT_POSTFIELDS  => $data,
        );

        return $curlopts;
    }

    /**
     * Helper for {@link __query} - set connection params for GET HTTP method.
     *
     * @param array $data
     * @param array $opts additional options
     * @return array CURL options
     * @access protected
     */
    protected function __query__get ($opts=array ()) {
        return array ( CURLOPT_HTTPGET => true );
    }

    /**
     * Helper for {@link __query} - set connection params for PUT HTTP method.
     *
     * @param array $data
     * @param array $opts additional options
     * @return array CURL options
     * @access protected
     */
    protected function __query__put ($data, $opts=array ()) {
        $curlopts = array ( CURLOPT_PUT => true,);
        if (!$data) {
            $curlopts[CURLOPT_HTTPHEADER] = array ('Content-Length' => 0);
        }
        return $curlopts;
    }

    /**
     * Helper for {@link __query} - set connection params for DELETE HTTP method.
     *
     * @param array $data
     * @param array $opts additional options
     * @return array CURL options
     * @access protected
     */
    protected function __query__delete ($opts=array ()) {
        return array ( CURLOPT_CUSTOMREQUEST => 'DELETE' );
    }

    /**
     * Prepare connection params and execute it
     *
     * Uses methods {@link __query__get}, {@link __query__post}, {@link __query__put} and {@link __query__delete}
     * to prepare data and connection params. Throws UnexpectedValueException exception for unknown method, or
     * RuntimeException when CURL error exists.
     *
     * @param string $url address to apppend to {@link $_root}
     * @param string $method HTTP method, one of: get, post, put, delete
     * @param mixed $data
     * @param array $opts additional options
     * @return string result of curl_exec ()
     * @access protected
     */
    protected function __query ($url, $method='get', $data=null, $opts=array ()) {
        $method = strtolower ($method);
        if (!method_exists ($this, '__query__'.$method)) {
            throw new UnexpectedValueException ('Unknown HTTP method.', -1);
        }
        $this->_debug ('METHOD: '. strtoupper ($method));

        # pobieramy ustawienia specyficzne dla każdej z wybranych metod
        $curlopts = call_user_func (array ($this, '__query__'.$method), $data, $opts);
        $curlopts[CURLOPT_URL] = $this->_root . $url;

        $headers_single = array ();
        # jesli trzeba to dodajemy jednorazowe nagłówki które mamy wysłać
        if (isset ($curlopts[CURLOPT_HTTPHEADER])) {
            $this->headers_set ($curlopts[CURLOPT_HTTPHEADER]);
            $headers_names = array_keys ($curlopts[CURLOPT_HTTPHEADER]);
        }

        # nagłówki do wysłania
        if ($this->_headers) {
            $headers = array ();
            foreach ($this->_headers as $k=>$v) {
                $headers[]          = sprintf ('%s: %s', $k, $v);
            }
            $curlopts[CURLOPT_HTTPHEADER] = $headers;
        }
        $this->_debug ('post2', print_r ($this->_headers, 1), print_r ($headers_names, 1));

        $this->_debug ('DATA: '. print_r ($data, 1), 'CURLOPTS: '.print_r ($this->_debug_curlopts ($curlopts), 1));

        if (!curl_setopt_array ($this->_ch, $curlopts)) {
            throw new RuntimeException (curl_error ($this->_ch), curl_errno ($this->_ch));
        }

        //curl_setopt($this->_ch, CURLOPT_PROXY, '85.21.58.206:3128');
        //curl_setopt($this->_ch, CURLOPT_PROXYPORT, 0);
        //curl_setopt($this->_ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        
        
        # wykonujemy zapytanie
        $ret = curl_exec ($this->_ch);
        
        //echo '<pre>'.print_r($ret,true).'</pre>';           

        # jeśli któraś z metod __query__* zwróciła dodatkowe nagłówki do wysłania, to miało to byc jednorazowe,
        # czyli teraz usuwamy je z zestawu
        if (isset($headers_names)) {
            $this->headers_remove ($headers_names);
        }
        $this->_debug ('post3', print_r ($this->_headers, 1));

        return $ret;
    }

    /**
     * Parse reply
     *
     * Throws BadFunctionCallException exception when specified parser was not found.
     * Return array with keys
     *  * headers - (array) array of headers (keys are lowercased)
     *  * body - (mixed) body of response. If reply's mime type is found in {@link $_parsers}, then contains reply of specified parser, in other case contains raw string reply.
     *  * body_parse - (bool) if true, content was successfully parsed by specified parser
     *  * status_code - (int) status code from server
     *  * status_body - (string) content of status
     *
     * @param string $reply
     * @return array
     * @access protected
     */
    protected function __parse_reply ($reply) {
        # rozdzielamy nagłówki od treści
        $reply          = preg_split ("/\r?\n\r?\n/mu", $reply, 2);
        $headers        = $reply[0];
        $body           = isset ($reply[1]) ? $reply[1] : '';

        # parsujemy nagłówki
        $headers        = explode ("\n", $headers);
        # usuwamy typ protokołu
        $header_http    = array_shift ($headers);
        $headers_parsed = array ();
        foreach ($headers as $header) {
            $header = preg_split ('/\s*:\s*/u', trim ($header), 2);
            $headers_parsed[strtolower ($header[0])] = $header[1];
        }
        $headers = &$headers_parsed;

        # określamy kod statusu
        if (
            (isset ($headers['status']) && preg_match ('/(\d+)\s+(.*)/u', $headers['status'], $match))
            ||
            (preg_match ('!HTTP/1\.[01]\s+(\d+)\s+([\w ]+)!', $header_http, $match))
        ) {
            $status = array ( $match[1], $match[2] );
        }
        else {
            $status = array (0, '');
        }

        # parsujemy treść odpowiedzi, jeśli mamy odpowiedni parser
        $body_parsed    = false;
        if (isset ($this->_parsers[$this->_format])) {
            $formatter = $this->_parsers[$this->_format]; # shortcut
            if ($formatter &&
                (
                    (is_array ($formatter) && isset ($formatter[1]) && is_object ($formatter[0]) &&
                        method_exists ($formatter[0], $formatter[1]))
                    ||
                    function_exists ($formatter)
                )
            ) {                                           
                $body           = call_user_func ($formatter, $body);   
                
                $body_parsed    = true;
            }
            else {
                throw new BadFunctionCallException ('Specified parser not found: '. $formatter .'.');
            }
        }

        return array (
            'headers'       => $headers,
            'body'          => $body,
            'body_parsed'   => $body_parsed,
            'status_code'   => $status[0],
            'status_body'   => $status[1],
        );
    }



    /**
     * Creating update
     *
     * @param string $body body of status
     * @param sting $user recipient of message
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__update_create ($body, $user=null) {
        if (!$body) {
            throw new UnexpectedValueException ('Update body is missing.', -1);
        }

        if ($user) {
            $body = sprintf ('>%s: %s', $user, $body);
        }

        return $this->__query ('/updates', 'post', array ('update[body]' => $body));
    }

    /**
     * Reading update
     *
     * It's hard to explain what are doing specified parameters. Please consult with offcial API
     * documentation: {@link http://www.blip.pl/api-0.02.html}.
     *
     * Differences with official API: if you want messages from all users, specify $user == __all__.
     *
     * @param int $id Update ID
     * @param string $user
     * @param array $include array of resources to include (more info in official API documentation: {@link http://www.blip.pl/api-0.02.html}.
     * @param bool $since
     * @param int $limit
     * @param int $offset
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__update_read ($id=null, $user=null, $include=array(), $since=false, $limit=10, $offset=0) {
        # normalnie pobieramy updatey z tego zasobu
        $url = '/updates';

        # w odróżnieniu od samego API, chcemy ujednolicić pobieranie danych. podanie jako usera '__all__'
        # powoduje pobranie update'ów od wszystkich userów. Układ RESTowych urli blipa jest co najmniej... dziwny... i
        # mało konsekwentny.
        if ($user) {
            # ten user nie istnieje, wprowadzamy go dla wygody użytkownika biblioteka.
            if (strtolower ($user) == '__all__') {
                if ($id) {
                    $url    .= '/'. $id;
                    $id     = null;
                }
                $url        .= '/all';
                if ($since) {
                    $url    .= '_since';
                    $since  = null;
                }
            }
            # jeśli pobieramy konkretnego usera, to wszystko jest prostsze
            else {
                $url = sprintf ('/users/%s/updates', $user);
            }
        }

        # dla pojedynczego usera, innego niż __all__, dodajemy id wpisu
        if (!is_null ($id) && $id) {
            $url .= '/'. $id;
        }

        if ($since) {
            $url .= '/since';
        }

        $limit = (int)$limit;
        if ($limit) {
            $url .= '?limit='.$limit;
        }

        $offset = (int)$offset;
        if ($offset) {
            $url .= ($limit ? '&' : '?') . 'offset=' . $offset;
        }

        if ($include) {
            $url .= (($limit || $offset) ? '&' : '?'). 'include=' . implode (',', $include);
        }

        return $this->__query ($url, 'get');
    }

    /**
     * Deleting update
     *
     * Throws UnexpectedValueException when update ID is missing.
     *
     * @param int $id update ID
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__update_delete ($id) {
        if (!$id) {
            throw new UnexpectedValueException ('Update ID is missing.', -1);
        }
        return $this->__query ('/updates/'. $id, 'delete');
    }


    /**
     * Create direct message
     *
     * Throws UnexpectedValueException if some of parametr is missing.
     *
     * @param string $body Body of sent message
     * @param int|string $user username or user id
     * @param string @picture Absolute path to a picture assigned to a message
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__dirmsg_create ($body, $user, $picture = null) {
        if (!$body || !$user) {
            throw new UnexpectedValueException ('Directed_message body or recipient is missing.', -1);
        }
        $opts = array();
        $data = array('directed_message[body]' => $body, 'directed_message[recipient]' => $user);
        if ($picture !== null) {
            $data['directed_message[picture]'] = $picture;
            $opts['multipart'] = true;
        }
        return $this->__query ('/directed_messages', 'post', $data, $opts);
    }

    /**
     * Read direct message
     *
     * Meaning of params: {@link http://www.blip.pl/api-0.02.html}
     *
     * @param int $id message ID
     * @param string $user username
     * @param array $include array of resources to include (more info in official API documentation: {@link http://www.blip.pl/api-0.02.html}.
     * @param bool $since
     * @param int $limit default to 10
     * @param int $offset default to 0
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__dirmsg_read ($id=null, $user=null, $include=array (), $since=false, $limit=10, $offset=0) {
        # normalnie pobieramy mesgi z tego zasobu
        $url = '/directed_messages';

        if (!is_null ($user) && $user) {
            # ten user nie istnieje, wprowadzamy go dla wygody użytkownika biblioteki.
            if (strtolower ($user) == '__all__') {
                if ($id) {
                    $url    .= '/'. $id;
                    $id     = null;
                }
                $url        .= '/all';
                if ($since) {
                    $url    .= '_since';
                    $since  = null;
                }
            }
            # jeśli pobieramy konkretnego usera, to wszystko jest prostsze
            else {
                $url = sprintf ('/users/%s/directed_messages', $user);
            }
        }

        # dla pojedynczego usera, innego niż __all__, dodajemy id wpisu
        if (!is_null ($id) && $id) {
            $url .= '/'. $id;
        }

        if ($since) {
            $url .= '/since';
        }

        $limit = (int)$limit;
        if ($limit) {
            $url .= '?limit='.$limit;
        }

        $offset = (int)$offset;
        if ($offset) {
            $url .= ($limit ? '&' : '?') . 'offset=' . $offset;
        }

        if ($include) {
            $url .= (($limit || $offset) ? '&' : '?'). 'include=' . implode (',', $include);
        }

        return $this->__query ($url, 'get');
    }


    /**
     * Delete direct message
     *
     * Throws UnexpectedValueException when directed message ID is missing
     *
     * @param int $id message ID
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__dirmsg_delete ($id) {
        if (!$id) {
            throw new UnexpectedValueException ('Directed_message ID is missing.', -1);
        }
        return $this->__query ('/directed_messages/'. $id, 'delete');
    }


    /**
     * Create status
     *
     * Throws UnexpectedValueException when status body is missing
     *
     * @param string $body Body of setted message
     * @param string @picture Absolute path to a picture assigned to a status
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__status_create ($body, $picture = null) {
        if (!$body) {
            throw new UnexpectedValueException ('Status body is missing.', -1);
        }
        $opts = array();
        $data = array('status[body]' => $body);
        if ($picture !== null) {
            $data['status[picture]'] = $picture;
            $opts['multipart'] = true;
        }
        return $this->__query ('/statuses', 'post', $data, $opts);
    }

    /**
     * Read status
     *
     * Meaning of params: {@link http://www.blip.pl/api-0.02.html}
     *
     * @param int $id status ID
     * @param string $user username
     * @param array $include array of resources to include (more info in official API documentation: {@link http://www.blip.pl/api-0.02.html}.
     * @param bool $since
     * @param int $limit default to 10
     * @param int $offset default to 0
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__status_read ($id=null, $user=null, $include=array (), $since=false, $limit=10, $offset=0) {
        # normalnie pobieramy statusy z tego zasobu
        $url = '/statuses';

        if ($user) {
            # ten user nie istnieje, wprowadzamy go dla wygody użytkownika biblioteki.
            if (strtolower ($user) == '__all__') {
                if ($id) {
                    $url    .= '/'. $id;
                    $id     = null;
                }
                $url        .= '/all';
                if ($since) {
                    $url    .= '_since';
                    $since  = null;
                }
            }
            # jeśli pobieramy konkretnego usera, to wszystko jest prostsze
            else {
                $url = sprintf ('/users/%s/statuses', $user);
            }
        }

        # dla pojedynczego usera, innego niż __all__, dodajemy id wpisu
        if (!is_null ($id) && $id) {
            $url .= '/'. $id;
        }

        if ($since) {
            $url .= '/since';
        }

        $limit = (int)$limit;
        if ($limit) {
            $url .= '?limit='.$limit;
        }

        $offset = (int)$offset;
        if ($offset) {
            $url .= ($limit ? '&' : '?') . 'offset=' . $offset;
        }

        if ($include) {
            $url .= (($limit || $offset) ? '&' : '?'). 'include=' . implode (',', $include);
        }

        return $this->__query ($url, 'get');
    }

    /**
     * Delete status
     *
     * Throws UnexpectedValueException when status ID is missing
     *
     * @param int $id status ID
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__status_delete ($id) {
        if (!$id) {
            throw new UnexpectedValueException ('Status ID is missing.', -1);
        }
        return $this->__query ('/statuses/'. $id, 'delete');
    }


    /**
     * Read recording attached to status/message/updateA
     *
     * Throws UnexpectedValueException when status ID is missing
     *
     * @param int $id status ID
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__recording_read ($id) {
        if (!$id) {
            throw new UnexpectedValueException ('Update ID is missing.', -1);
        }
        return $this->__query (sprintf ('/updates/%s/recording', $id), 'get');
    }


    /**
     * Read picture attached to status/message/update
     *
     * Throws UnexpectedValueException when update ID is missing
     *
     * @param int $id picture ID
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__picture_read ($id) {
        if (!$id) {
            throw new UnexpectedValueException ('Update ID is missing.', -1);
        }
        return $this->__query (sprintf ('/updates/%s/pictures', $id), 'get');
    }


    /**
     * Get shortlinks from Blip!'s rdir system
     *
     * @param int $since_id status ID - will return statuses with newest ID then it
     * @param int $limit
     * @param int $offset
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__shortlink_read ($since_id=null, $limit=10, $offset=0) {
        if ($since_id) {
            $url = sprintf ('/shortlinks/%d/all_since', $since_id);
        }
        else {
            $url = '/shortlinks/all';
        }

        $limit = (int)$limit;
        if ($limit) {
            $url .= '?limit='.$limit;
        }

        $offset = (int)$offset;
        if ($offset) {
            $url .= ($limit ? '&' : '?') . 'offset=' . $offset;
        }

        return $this->__query ($url, 'get');
    }


    /**
     * Return user current dashboard
     *
     * @param int $since_id status ID - will return statuses with newest ID then it
     * @param string $user
     * @param array $include array of resources to include (more info in official API documentation: {@link http://www.blip.pl/api-0.02.html}.
     * @param int $limit default to 10
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__dashboard_read ($since_id=null, $user=null, $include=array (), $limit=10) {
        if ($user) {
            $url = sprintf ('/users/%s/dashboard', $user);
        }
        else {
            $url = '/dashboard';
        }

        if (!is_null ($since_id) && $since_id) {
            $url .= sprintf ('/since/%s', $since_id);
        }

        $limit = (int)$limit;
        if ($limit) {
            $url .= '?limit='.$limit;
        }

        if ($include) {
            $url .= ($limit ? '&' : '?'). 'include=' . implode (',', $include);
        }

        return $this->__query ($url, 'get');
    }


    /**
     * Return current bliposhpere
     *
     * @param array $include array of resources to include (more info in official API documentation: {@link http://www.blip.pl/api-0.02.html}.
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__bliposphere_read ($include=array ()) {
        $url = '/bliposphere';

        if ($include) {
            $url .= '?include=' . implode (',', $include);
        }

        return $this->__query ($url, 'get');
    }


    /**
     * Return user current subscriptions
     *
     * Throws UnexpectedValueException when incorrect $direction is specified.
     *
     * @param string $user
     * @param array $include array of resources to include (more info in official API documentation: {@link http://www.blip.pl/api-0.02.html}.
     * @param string $direction subscription direction. Can be: both (default), from, to
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__subscription_read ($user=null, $include=array(), $direction = 'both') {
        $direction = strtolower ($direction);
        if (!in_array ($direction, array ('both', 'to', 'from'))) {
            throw new UnexpectedValueException (sprintf ('Incorrect param: "direction": "%s". Allowed values: both, from, to.',
                $direction), -1);
        }

        if ($direction == 'both') {
            $direction = '';
        }

        $url = '/subscriptions/' . $direction;
        if (!is_null ($user) && $user) {
            $url = '/users/'. $user . $url;
        }

        if ($include) {
            $url .= '?include=' . implode (',', $include);
        }

        return $this->__query ($url, 'get');
    }

    /**
     * Create or delete subscription of given user to current signed
     *
     * @param string $user subscribed user
     * @param bool $www
     * @param bool $im
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__subscription_update ($user, $www=null, $im=null) {
        $url = '/subscriptions';
        if (!is_null ($user) && $user) {
            $url .= '/'. $user;
        }

        $data = array (
            'subscription[www]' => $www ? 1 : 0,
            'subscription[im]'  => $im  ? 1 : 0,
        );
        return $this->__query ($url . '?' . $this->__arr2qstr ($data), 'put');
    }

    /**
     * Delete subscription
     *
     * @param string $user
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__subscription_delete ($user) {
        return $this->__query ('/subscriptions/'. $user, 'delete');
    }


    /**
     * Return users data
     *
     * @param string $user
     * @param array $include array of resources to include (more info in official API documentation: {@link http://www.blip.pl/api-0.02.html}.
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__user_read ($user=null, $include=array ()) {
        if (!$user) {
            $user = $this->_login;
        }

        $url = '/users/'. $user;
        if ($include) {
            $url .= '?include=' . implode (',', $include);
        }
        return $this->__query ($url, 'get');
    }


    /**
     * Get info about users avatar
     *
     * @param string $user
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__avatar_read ($user=null) {
        if (!$user) {
            $user = $this->_login;
        }
        return $this->__query (sprintf ('/users/%s/avatar', $user), 'get');
    }

    /**
     * Upload new avatar
     *
     * Throws UnexpectedValueException if avatar path is missing or file not found
     *
     * @param string $avatar new avatars path
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__avatar_update ($avatar) {
        if (!$avatar || !file_exists ($avatar)) {
            throw new UnexpectedValueException ('Avatar path missing or file not found.', -1);
        }
        if ($avatar[0] != '@') {
            $avatar = '@'.$avatar;
        }
        return $this->__query ('/avatar', 'post', array ( 'avatar[file]' => $avatar ), array ('multipart' => 1));
    }

    /**
     * Delete avatar
     *
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__avatar_delete () {
        return $this->__query ('/avatar', 'delete');
    }


    /**
     * Get info about users background
     *
     * @param string $user
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__background_read ($user=null) {
        if (is_null ($user)) {
            $user = $this->_login;
        }
        return $this->__query (sprintf ('/users/%s/background', $user), 'get');
    }

    /**
     * Upload new background
     *
     * Throws UnexpectedValueException if background path is missing, or file not found
     *
     * @param string $background new background path
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__background_update ($background) {
        if (!$background || !file_exists ($background)) {
            throw new UnexpectedValueException ('Background path is missing or file not found.', -1);
        }
        if ($background[0] != '@') {
            $background = '@'.$background;
        }
        return $this->__query ('/background', 'post', array ('background[file]' => $background), array ('multipart' => 1));
    }

    /**
     * Delete background
     *
     * @access protected
     * @return mixed return of {@link __query}
     */
    protected function _cmd__background_delete () {
        return $this->__query ('/background', 'delete');
    }

}

// vim: fdm=manual
?>
