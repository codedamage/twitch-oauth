<?php
require 'twitch.php';

$provider = new TwitchProvider([
    'clientId'                => 'aykbgdlthyq4rjngjkl1l7d9w03q79',     // The client ID assigned when you created your application
    'clientSecret'            => 'kr974chb7uyyxtclks5qaegulemc8y', // The client secret assigned when you created your application
    'redirectUri'             => 'https://findstreamer.com/oauth.php',  // Your redirect URL you specified when you created your application
    'scopes'                  => ['analytics:read:extensions channel:read:subscriptions user:read:email']  // The scopes you would like to request
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Fetch the authorization URL from the provider, and store state in session
    $authorizationUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();

    // Display link to start auth flow
    echo "<html><a href=\"$authorizationUrl\">Click here to link your Twitch Account</a><html>";
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }

    exit('Invalid state');

} else {

    try {

        // Get an access token using authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // Using the access token, get user profile
        $resourceOwner = $provider->getResourceOwner($accessToken);
        $user = $resourceOwner->toArray();

        echo '<html><table>';
        echo '<tr><th>Access Token</th><td>' . htmlspecialchars($accessToken->getToken()) . '</td></tr>';
        echo '<tr><th>Refresh Token</th><td>' . htmlspecialchars($accessToken->getRefreshToken()) . '</td></tr>';
        echo '<tr><th>Username</th><td>' . htmlspecialchars($user['display_name']) . '</td></tr>';
        echo '<tr><th>Bio</th><td>' . htmlspecialchars($user['bio']) . '</td></tr>';
        echo '<tr><th>Image</th><td><img src="' . htmlspecialchars($user['logo']) . '"></td></tr>';
        echo '</table></html>';

        // You can now create authenticated API requests through the provider.
        //$request = $provider->getAuthenticatedRequest(
        //    'GET',
        //    'https://api.twitch.tv/kraken/user',
        //    $accessToken
        //);

    } catch (Exception $e) {
        exit('Caught exception: '.$e->getMessage());
    }
}