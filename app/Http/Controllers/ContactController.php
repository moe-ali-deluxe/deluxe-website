<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
class ContactController extends Controller
{
     public function show()
    {
        return view('contact');
    }

    public function submit(Request $request)
{
    $data = $request->validate([
        'name'    => 'required|string|max:255',
        'email'   => 'required|email|max:255',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    Mail::raw($data['message'], function ($message) use ($data) {
        $message->to('deluxeplusmohammad@gmail.com')
                ->subject('Contact Form: ' . $data['subject'])
                ->replyTo($data['email'], $data['name']);
    });

    return back()->with('success', 'Thanks for contacting us! We will get back to you soon.');
}
}
