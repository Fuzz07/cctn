<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $services = Service::active()->where('price', '>', 0)->orderBy('price', 'asc')->get()->unique('service_name')->values();

        // Get troubleshooting service ID dynamically
        $troubleService = Service::where('service_name', 'like', '%Troubleshooting%')->first();
        $troubleId = $troubleService ? $troubleService->id : 16;

        return view('home', compact('services', 'troubleId'));
    }

    /**
     * Display Terms and Conditions for BCTVI clients.
     */
    public function terms()
    {
        return view('terms');
    }

    /**
     * The "I'm not a robot" check for the Android app, which opens this page in
     * a WebView and receives the token through its JavaScript bridge. The app
     * sends the token with its sign-in or registration request, where it is verified.
     */
    public function mobileRecaptcha(Request $request)
    {
        return view('mobile.recaptcha', [
            'required' => Recaptcha::isRequired(),
            'siteKey'  => config('services.recaptcha.site_key'),
            'dark'     => $request->query('theme') === 'dark',
        ]);
    }

    /**
 * Streams the mobile companion app APK with proper headers.
 */
public function downloadApk()
{
    // If running in production / on Vercel, redirect to the direct CDN path.
    // This is crucial to bypass Vercel's strict 4.5MB Serverless Function response payload limit.
    if (env('VERCEL') || str_contains(request()->getHost(), 'vercel.app')) {
        return redirect('/downloads/cctn-app.apk');
    }

    // For local development, stream the file directly.
    $path = resource_path('apk/cctn-app.apk');

    if (!file_exists($path)) {
        // Fallback to public folder if resource path does not exist in local development
        $path = public_path('downloads/cctn-app.apk');
    }

    if (!file_exists($path)) {
        abort(404, 'The requested APK file could not be found.');
    }

    return response()->download($path, 'cctn-app.apk', [
        'Content-Type' => 'application/vnd.android.package-archive',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ]);
}
}
