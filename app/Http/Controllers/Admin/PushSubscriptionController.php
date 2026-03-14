<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    public function update(Request $request)
    {
        $this->validate($request, [
            'endpoint'    => 'required',
            'keys.p256dh' => 'required',
            'keys.auth'   => 'required',
        ]);

        $endpoint = $request->endpoint;
        $token = $request->input('keys.auth');
        $key = $request->input('keys.p256dh');

        Auth::user()->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true], 200);
    }

    public function destroy(Request $request)
    {
        $this->validate($request, ['endpoint' => 'required']);

        Auth::user()->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true], 200);
    }
}