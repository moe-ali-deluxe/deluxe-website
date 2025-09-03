<?php

namespace App\Http\Controllers;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use App\Mail\NewsletterWelcomeMail;
use Illuminate\Support\Facades\Mail;
class NewsletterController extends Controller
{
     public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email'
        ]);

        NewsletterSubscriber::create([
            'email' => $request->email
        ]);

        return back()->with('success', 'Thank you for subscribing!');
    }
    public function subscribe(Request $request)
{
    $request->validate([
        'email' => 'required|email|unique:newsletter_subscribers,email',
    ]);

    NewsletterSubscriber::create([
        'email' => $request->email,
    ]);

    // Send welcome email
    Mail::to($request->email)->send(new NewsletterWelcomeMail($request->email));

    return back()->with('success', 'Thank you for subscribing! Please check your inbox.');
}
}
