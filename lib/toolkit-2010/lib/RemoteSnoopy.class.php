<?php

    require_once('Snoopy.class.php');
    require_once('IXR_Library.inc.php');

    class RemoteSnoopy extends Snoopy
    {
        var $m_Server;
        
        var $m_Secret;
        
    /**
    * @var IXR_Client
    */
        var $m_Client;
        
        function RemoteSnoopy($url, $secret)
        {
            $this->m_Server = $url;
            $this->m_Client = new IXR_Client($url);
            $this->m_Secret = md5($secret);
        }
        
        
        function getVars()
        {
            $result = get_object_vars($this);
            unset($result['temp_dir'], $result['curl_path'], $result['m_Server'], $result['m_Client']);
            
            return $result;
        }
        
        function setVars($incoming)
        {
            $this_vars = $this->getVars();
            
            foreach ($incoming as $var => $val)
            {
                if (isset($this_vars[$var]))
                {
                    $this->$var = $val;
                }
            }
        }
        
        
        function fetch($url)
        {
            $res = $this->m_Client->query('fetch', $this->getVars(), $url);
            
            if (!$res)
            {
                return false;
            }
            
            $result = $this->m_Client->getResponse();
//            echo "<pre>Result\n";
//            print_r($result);
//            echo "\n\n</pre>";
            $this->setVars($result);
            return $result['api_result'];
        }
        
        
        function fetchtext($url)
        {
            $res = $this->m_Client->query('fetchtext', $this->getVars(), $url);
            
            if (!$res)
            {
                return false;
            }
            
            $result = $this->m_Client->getResponse();
            $this->setVars($result);
            return $result['api_result'];
        }
        
        
        function fetchform($url)
        {
            $res = $this->m_Client->query('fetchform', $this->getVars(), $url);
            
            if (!$res)
            {
                return false;
            }
            
            $result = $this->m_Client->getResponse();
            $this->setVars($result);
            return $result['api_result'];
        }

        
        function fetchlinks($url)
        {
            $res = $this->m_Client->query('fetchlinks', $this->getVars(), $url);
            
            if (!$res)
            {
                return false;
            }
            
            $result = $this->m_Client->getResponse();
            $this->setVars($result);
            return $result['api_result'];
        }
        
        
        function submit($url, $postvars = '', $postfiles = '')
        {
            $res = $this->m_Client->query('submit', $this->getVars(), $url, $postvars, $postfiles);
            
            if (!$res)
            {
                return false;
            }
            
            $result = $this->m_Client->getResponse();
            $this->setVars($result);
            return $result['api_result'];
        }

        
        function submittext($url, $postvars = '', $postfiles = '')
        {
            $res = $this->m_Client->query('submittext', $this->getVars(), $url, $postvars, $postfiles);
            
            if (!$res)
            {
                return false;
            }
            
            $result = $this->m_Client->getResponse();
            $this->setVars($result);
            return $result['api_result'];
        }

        
        function submitlinks($url, $postvars = '', $postfiles = '')
        {
            $res = $this->m_Client->query('submitlinks', $this->getVars(), $url, $postvars, $postfiles);
            
            if (!$res)
            {
                return false;
            }
            
            $result = $this->m_Client->getResponse();
            $this->setVars($result);
            return $result['api_result'];
        }
        
    }

?>