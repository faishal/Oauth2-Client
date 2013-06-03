<?php
namespace rtOAuth2;

interface DataStore
{
    /**
     * @param \rtOAuth2\Token $token
     */
    function storeAccessToken(Token $token);

    /**
     * @return \rtOAuth2\Token
     */
    function retrieveAccessToken();
}
