<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class EarnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // RETURN ALL VALUES
        return view('earn')->with([
            // Coming Soon
        ]);
    }

    public function start(Request $request)
    {
        $randomnumber = rand(1000, 1500);
        $code = rand(20000, 30000);

        $base = "https://link-to.net/" . 'api';
        $base .= "/" . rand(100, 1000) . "." . rand(999, 10000000) . "/dynamic/?r=";
        $base .= base64_encode(env('APP_URL', 'https://ctrlpanel.freehorizon.host') . '/redeem?code=' . strval($code));

        // Set the start time in the session
        Session::put('start_time', now());

        return redirect()->away($base)->withCookie(cookie('earn_code', $code, 15));
    }

    public function redeem(Request $request)
    {
        // Get the start time from the session
        $startTime = Session::get('start_time');

        // Get the current time
        $currentTime = now();

        // Calculate the elapsed time in seconds
        $elapsedTime = $currentTime->diffInSeconds($startTime);

        // Check if the elapsed time is less than 60 seconds (1 minute)
        if ($elapsedTime < 60) {
            return redirect()->route('earn.index')->with('error', 'Please do not skip the advertising!');
        }

        $reward = 20;

        $code = $request->cookie('earn_code');

        $referer = request()->headers->get('referer');

        if (!str_contains($referer, 'linkvertise.com')) {
            // @Aronik if necessary add Discord logging again -> I can also implement a handler for it
            return redirect()->route('earn.index')->with('error', __("You bypassed linkvertise! Please don't use cheats."));
        }

        if ($request->query('code') !== $code) {
            // @Aronik if necessary add Discord logging again -> I can also implement a handler for it
            return redirect()->route('earn.index')->with('error', __("We cannot verify this being a legitimate request. Please try again later."));
        }

        Auth::user()->increment('credits', $reward);

        // Clear the start time from the session
        Session::forget('start_time');

        return redirect()->route('earn.index')->with('success', __("You successfully got Coins!"))->withoutCookie('earn_code');
    }

    // Rest of your methods...

}
