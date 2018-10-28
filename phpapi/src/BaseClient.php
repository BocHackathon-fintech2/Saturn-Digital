<?php

namespace BankOfCyprus;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class BaseClient
{
    private $clientId;
    private $clientSecret;
    private $accessToken;

    private $appId;
    private $originUserId;
    private $journeyId;
    private $ttpId;

    protected $stack;
    protected $client;
    protected $logger;

    /**
     * BaseClient constructor.
     */
    public function __construct()
    {
        $this->stack = HandlerStack::create();
        $this->logger = new Logger('Logger');
        $this->logger->pushHandler(new StreamHandler('log.log', Logger::DEBUG));

        $this->stack->push(
            Middleware::log(
                $this->logger,
                new MessageFormatter('>>>>>>>>\n{request}\n\n<<<<<<<<\n{response}\n--------\n\n{error}')
            )
        );
    }


    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param mixed $clientId
     */
    public function setClientId( $clientId )
    {
        $this->clientId = $clientId;
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param mixed $clientSecret
     */
    public function setClientSecret( $clientSecret )
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     */
    public function setAccessToken( $accessToken )
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param mixed $appId
     */
    public function setAppId( $appId )
    {
        $this->appId = $appId;
    }

    /**
     * @return mixed
     */
    public function getOriginUserId()
    {
        return $this->originUserId;
    }

    /**
     * @param mixed $originUserId
     */
    public function setOriginUserId( $originUserId )
    {
        $this->originUserId = $originUserId;
    }

    /**
     * @return mixed
     */
    public function getJourneyId()
    {
        return $this->journeyId;
    }

    /**
     * @param mixed $journeyId
     */
    public function setJourneyId( $journeyId )
    {
        $this->journeyId = $journeyId;
    }

    /**
     * @return mixed
     */
    public function getTtpId()
    {
        return $this->ttpId;
    }

    /**
     * @param mixed $ttpId
     */
    public function setTtpId( $ttpId )
    {
        $this->ttpId = $ttpId;
    }


}