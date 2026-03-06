<?php
$consumerKey    = 'SoVoRg56qw27VycfR3AogsdhpvQa';
$consumerSecret = '5Ur8Uz7gXnwdKFM3bsuEfZxxRlUa';
$merchantMsisdn = '261383454081';

echo "--- Tentative 1: Token simple sans scope ---\n";
$auth = base64_encode($consumerKey . ':' . $consumerSecret);
$cmd = "curl -k -X POST https://developer.mvola.mg/oauth2/token -d \"grant_type=client_credentials\" -H \"Authorization: Basic $auth\"";
exec($cmd, $out);
print_r($out);

// Si vous avez un Username/Password pour le "Password Grant", on pourrait tester ça aussi.
// Mais essayons d'abord de voir si le token sans scope change quelque chose.
