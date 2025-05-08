<?php namespace Vankosoft\IssueTrackingBundle\Component;

interface ProjectApiClientInterface
{
    /**
     * Login to VankoSoft API
     *
     * @throws VankosoftApiException
     * @return string Vankosoft API Token
     */
    public function login(): string;
}