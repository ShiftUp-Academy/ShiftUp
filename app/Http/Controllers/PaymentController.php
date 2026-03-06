<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    private function getMvolaToken()
    {
        $consumerKey = env('MVOLA_CONSUMER_KEY');
        $consumerSecret = env('MVOLA_CONSUMER_SECRET');
        
        $tokenUrl = 'https://developer.mvola.mg/oauth2/token';

        $response = Http::asForm()
            ->withBasicAuth($consumerKey, $consumerSecret)
            ->withHeaders(['Cache-Control' => 'no-cache'])
            ->post($tokenUrl, [
                'grant_type' => 'client_credentials',   
                'scope' => 'EXT_INT_MVOLA_SCOPE'
            ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        Log::error("Echec Token MVola", ['body' => $response->body()]);
        return null;
    }

    public function initMvolaPayment(Request $request)
    {
        try {
            $request->validate(['amount' => 'required', 'phone' => 'required']);

            $amount = intval($request->amount);
            $customerPhone = $request->phone;
            $orderId = (string)time();
            $token = $this->getMvolaToken();

            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Erreur d\'authentification']);
            }

            $merchantNumber = env('MVOLA_MERCHANT_NUMBER');
            $isProduction = env('MVOLA_MODE') === 'production';
            
            $baseUrl = $isProduction ? 'https://api.mvola.mg' : 'https://devapi.mvola.mg';

            $formatMsisdn = function($number) {
                $number = preg_replace('/\D/', '', $number);
                if (str_starts_with($number, '261')) return $number;
                if (str_starts_with($number, '0')) return '261' . substr($number, 1);
                return '261' . $number;
            };

            $customerMsisdn = $formatMsisdn($customerPhone);
            $merchantMsisdn = $formatMsisdn($merchantNumber);
            
            // On tente le format international dans le header aussi (souvent requis en prod)
            $merchantIdentifier = 'msisdn;' . $merchantMsisdn; 

            $requestDate = gmdate('Y-m-d\TH:i:s.v\Z');

            $paymentData = [
                "amount" => (string)$amount,
                "currency" => "Ar",
                "descriptionText" => "Achat epikbrand " . $orderId,
                "requestingOrganisationTransactionReference" => $orderId,
                "requestDate" => $requestDate,
                "originalTransactionReference" => $orderId,
                "debitParty" => [["key" => "msisdn", "value" => $customerMsisdn]],
                "creditParty" => [["key" => "msisdn", "value" => $merchantMsisdn]],
                "metadata" => [
                    ["key" => "partnerName", "value" => "epikbrand"],
                    ["key" => "fc", "value" => "Ar"],
                    ["key" => "amountFc", "value" => (string)$amount]
                ]
            ];

            $correlationId = \Illuminate\Support\Str::uuid()->toString();

            Log::info("MVOLA DEBUG - PAYLOAD", $paymentData);

            $response = Http::withToken($token)
                ->withHeaders([
                    'Version'                => '1.0',
                    'X-CorrelationID'        => $correlationId,
                    'UserLanguage'           => 'mg', 
                    'UserAccountIdentifier'  => $merchantIdentifier,
                    'partnerName'            => 'epikbrand',
                    'Cache-Control'          => 'no-cache',
                    'Content-Type'           => 'application/json',
                    'Accept'                 => 'application/json',
                    'X-Callback-URL'         => url('/api/payment/mvola/callback'), // Ajout d'une URL de callback
                ])
                ->post($baseUrl . '/mvola/mm/transactions/type/merchantpay/1.0.0/', $paymentData);

            Log::info("MVOLA DEBUG - FULL RESPONSE", [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful() || $response->status() === 202) {
                return response()->json([
                    'status' => 'success',
                    'order_id' => $orderId,
                    'simulated_redirect_url' => url('/payment/mock-success?provider=mvola&order_id=' . $orderId)
                ]);
            }

            $errorMsg = $response->json()['errorDescription'] ?? ($response->json()['message'] ?? 'Erreur inconnue');
            return response()->json([
                'status' => 'error', 
                'message' => 'MVOLA: ' . $errorMsg, 
                'details' => $response->json()
            ]);

        } catch (\Exception $e) {
            Log::error("EXCEPTION MVOLA", ['msg' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function initOrangeMoneyPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric'
        ]);

        $amount = $request->amount;
        $orderId = uniqid('OM_');

        $clientId = env('ORANGE_MONEY_CLIENT_ID');
        $clientSecret = env('ORANGE_MONEY_CLIENT_SECRET');
        $merchantKey = env('ORANGE_MONEY_MERCHANT_KEY');

        /*
        // EXEMPLE D'INTÉGRATION RÉELLE ORANGE MONEY :
        // 1. Authentification (OAuth) pour obtenir un access_token
        $authResponse = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api.orange.com/oauth/v3/token', [
                'grant_type' => 'client_credentials'
            ]);
        $accessToken = $authResponse->json()['access_token'];

        // 2. Appel à Web Payment pour obtenir l'URL de redirection
        $paymentResponse = Http::withToken($accessToken)
            ->post('https://api.orange.com/orange-money-webpay/dev/v1/webpayment', [
                'merchant_key' => $merchantKey,
                'currency' => 'OUV', // OU Madagascar Ar
                'order_id' => $orderId,
                'amount' => $amount,
                'return_url' => url('/payment/success'),
                'cancel_url' => url('/panier'),
                'notif_url' => url('/api/payment/webhook'), // URL notification (IPN)
                'lang' => 'fr'
            ]);
        
        if ($paymentResponse->successful()) {
            return response()->json([
                'status' => 'success',
                'simulated_redirect_url' => $paymentResponse->json()['payment_url']
            ]);
        }
        */

        Log::info("Initiation paiement Orange Money", ['amount' => $amount, 'orderId' => $orderId]);

        $mockResponse = [
            'status' => 'success',
            'provider' => 'Orange Money',
            'order_id' => $orderId,
            'message' => "Simulation Orange Money. Clés du .env : " . ($clientId ? 'Détectées' : 'Vides'),
            'simulated_redirect_url' => url('/payment/mock-success?provider=orange_money&order_id=' . $orderId)
        ];

        return response()->json($mockResponse);
    }

    public function mockSuccess(Request $request)
    {
        $provider = $request->get('provider');
        $orderId = $request->get('order_id');

        return view('payment_success_mock', [
            'provider' => ucfirst(str_replace('_', ' ', $provider)),
            'order_id' => $orderId
        ]);
    }
}
