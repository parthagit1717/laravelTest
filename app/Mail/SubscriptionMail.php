<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $request;
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->view('view.name');

        $data['data'] =  $this->request;
        // dd($data);

        return $this->view('mail.subscription_mail', $data)
                    ->to($this->request['email']) 
                    ->subject('OPConnect Subscription Mail')
                    ->from('onepatchoffice@gmail.com',env('APP_NAME'));
    }
}
