<?php

namespace Awoo\OAuth;

use Awoo\OAuth\Flow\DiscordAuthCodeFlow;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Nette\Application\UI\Presenter;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;
use Wohali\OAuth2\Client\Provider\Exception\DiscordIdentityProviderException;

class Discord
{

    /** @var DiscordAuthCodeFlow */
    private $flow;

    public function __construct(DiscordAuthCodeFlow $flow)
    {
        $this->flow = $flow;
    }

    /**
     * @param Presenter $presenter
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function authenticate(Presenter $presenter): void
    {
        $this->flow->getProvider()->setRedirectUri($presenter->link("//:Auth:discordAuthorize"));
        $presenter->redirectUrl($this->flow->getAuthorizationUrl());
    }

    /**
     * @param array $param
     * @param Presenter $presenter
     * @return DiscordResourceOwner|string
     * @throws DiscordIdentityProviderException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function authorize(array $param, Presenter $presenter): DiscordResourceOwner
    {
        $this->flow->getProvider()->setRedirectUri($presenter->link("//:Auth:discordAuthorize"));

        try {
            $token = $this->flow->getAccessToken($param);
        } catch (IdentityProviderException $e) {
            $presenter->redirect("//:Auth:discordAuthenticate");
        }

        return $this->flow->getProvider()->getResourceOwner($token);
    }

}