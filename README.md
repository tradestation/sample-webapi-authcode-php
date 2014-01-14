# sample-webapi-authcode-php

This sample uses PHP to authenticate with the TradeStation WebAPI via an OAuth 2 Authorization Code Grant Type. The user will be directed to TradeStation's login page to capture credentials. After a successful login, an auth code is returned and is then exchanged for an access token which will be used for subsequent WebAPI calls.

## Configuration

Modify the following fields in gettoken.php with your appropriate values:

    $apikey = ""; // set this to your API Key
    $apisecret = ""; // set this to your API Secret
    $baseurl = "https://sim.api.tradestation.com/v2/"; // change this to LIVE when you're ready: https://api.tradestation.com/v2/

## Troubleshooting

If there are any problems, open an [issue](https://github.com/tradestation/sample-webapi-authcode-php/issues?page=1&state=open) and we'll take a look! You can also give us feedback by e-mailing webapi@tradestation.com.