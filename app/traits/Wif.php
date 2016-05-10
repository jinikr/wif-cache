<?php

namespace App\Traits;

trait Wif
{
    private $baseurl = 'http://wif.wooripension.com/';
    private $redisCache = null;
    private $useCache = false;
    private $cacheLifetime = 0;
    private $uid = '';

    private function _getRedisConnection()
    {
        return $this->cache;
    }

    private function _getCache($key)
    {
        if (true === $this->useCache) {
            return $this->_getRedisConnection()->get($key);
        }
        return false;
    }

    private function _setCache($key, $data)
    {
        if (true === $this->useCache && $this->cacheLifetime) {
            $this->_getRedisConnection()->save(
                $key,
                $data,
                $this->cacheLifetime
            );
        }
    }

    private function callWif()
    {
        $request = $this->request;
        // request uri log
        $this->logger->debug($this->uid . ' ' . $request->getMethod().': '.$request->getURI());

        $uri = explode('?', $request->getURI());
        $queryString = explode('&', $uri[1]);
        foreach ($queryString as $key => $value) {
            if (strtolower(explode('=',$value)[0]) === 'api_key') {
                unset($queryString[$key]);
            }
        }
        ksort($queryString);
        $cacheKey = implode('&', $queryString);
        // cache key log
        $this->logger->debug($this->uid . ' useCache flag: ' . $this->useCache);
        $this->logger->debug($this->uid . ' cache key: ' . $cacheKey);
        if ($result = $this->_getCache($cacheKey)) {
            // cache data return log
            $this->logger->info($this->uid . ' find cache: ' . $cacheKey);
            $this->logger->debug($this->uid . ' get cache: ' . $result);
            return $result;
        }
        $this->logger->debug($this->uid . ' cache not found: ' . $cacheKey);

        $httpHeaders = [];
        $httpHeaders['x-forwarded-for'] = $request->getClientAddress();

        $options = [CURLOPT_RETURNTRANSFER => TRUE];
        $options[CURLOPT_URL] = $this->baseurl.'?'.$uri[1];
        $options[CURLOPT_HTTPHEADER] = [];

        foreach ($httpHeaders as $key => $value) {
            $options[CURLOPT_HTTPHEADER][] = "$key: $value";
        }

        // curl options log
        $this->logger->info($this->uid . ' curl url: ' . $options[CURLOPT_URL]);
        $this->logger->debug($this->uid . ' curl options: ' . json_encode($options, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $result = curl_exec($curl);

        $curlError = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // return http code log
        $this->logger->info($this->uid . ' curl http code: ' . $httpCode);
        if ($curlError) {
            $this->logger->error($this->uid . ' curl error: ' . $curlError);
            throw new \Exception($curlError);
        }

        $result = iconv("EUC-KR", "UTF-8", $result);
        $result = preg_replace(
            '/^\<\?xml .*\?\>/',
            '<?xml version="1.0" encoding="UTF-8"?>',
            $result
        );

        curl_close($curl);

        $xml = new \XMLReader();
        if (!$xml->xml($result, NULL, LIBXML_DTDVALID)) {
          throw new \Exception('no result');
        }

        $this->logger->debug($this->uid . ' set cache flag: ' . $this->useCache);
        $this->logger->debug($this->uid . ' set cache lifetime: ' . $this->cacheLifetime);
        $this->_setCache($cacheKey, $result);

        return $result;
    }

    private function getPropertyInformation()
    {
        $this->useCache = true;
        $this->cacheLifetime = 600;
        return $this->callWif();
    }

    private function getRoomInfomation()
    {
        $this->useCache = true;
        $this->cacheLifetime = 600;
        return $this->callWif();
    }

    private function getPullAvailability()
    {
        $this->useCache = true;
        $this->cacheLifetime = 10;
        return $this->callWif();
    }

    private function getPropertyImages()
    {
        $this->useCache = true;
        $this->cacheLifetime = 600;
        return $this->callWif();
    }

    private function getRoomImages()
    {
        $this->useCache = true;
        $this->cacheLifetime = 600;
        return $this->callWif();
    }

    private function getProvinceCode()
    {
        $this->useCache = true;
        $this->cacheLifetime = 86400;
        return $this->callWif();
    }

    private function getCityCode()
    {
        $this->useCache = true;
        $this->cacheLifetime = 86400;
        return $this->callWif();
    }

    private function getExtraServiceCode()
    {
        $this->useCache = true;
        $this->cacheLifetime = 86400;
        return $this->callWif();
    }

    private function getThemeCode()
    {
        $this->useCache = true;
        $this->cacheLifetime = 86400;
        return $this->callWif();
    }

    /**
     * 예약관련 method
     * cache 없음.
     */
    private function getReservationHolding()
    {
        return $this->callWif();
    }

    private function getReservationConfirm()
    {
        return $this->callWif();
    }

    private function getReservationCancellation()
    {
        return $this->callWif();
    }

    private function getReservationCancelCharge()
    {
        return $this->callWif();
    }

    private function getReservationInfo()
    {
        return $this->callWif();
    }

}
