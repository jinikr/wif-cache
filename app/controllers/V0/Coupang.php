<?php
namespace App\Controllers\V0;

class Coupang extends \Phalcon\Mvc\Controller
{
    use \App\Traits\Wif;

    public function index()
    {
        // request log
        $request = $this->request;
        $this->uid = uniqid();
        $this->logger->info($this->uid . ' request start');
        $this->logger->info($this->uid . ' ' . $request->getMethod().': '.$request->getURI());
        $this->logger->info($this->uid . ' Body: ' . json_encode($request->getJsonRawBody(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        $response = $this->response;
        $p = $request->get('p');

        $methodName = strtolower($request->getMethod()).$p;

        if ($p && true === method_exists($this, $methodName)) {
            $this->logger->info($this->uid . ' call method: ' . $methodName);
            // method call log
            $result = call_user_func([$this, $methodName]);
            $this->logger->debug($this->uid . ' result: ' . $result);
        } else {
            $this->logger->error($this->uid . ' invalid parameter');
            $this->logger->error($this->uid . ' p: ' . $p);
            $this->logger->error($this->uid . ' methodName: ' . $methodName);
            // exception log
            throw new \Exception('invalid parameter');
        }

        $response
            ->setHeader("Content-Type", "application/xml")
            ->setContent($result);

        // response log
        $this->logger->info($this->uid . ' response!');
        return $response;
    }
}
