<!DOCTYPE html>
<html lang="en">
    <head>
        <title>OPConnect|Subscription Payment</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://js.stripe.com/v3/"></script>
        <style type="text/css">
            /* Absolute Center Spinner */
            .loading {
            display: none;
            position: fixed;
            z-index: 9999;
            height: 2em;
            width: 2em;
            overflow: show;
            margin: auto;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            }
            /* Transparent Overlay */
            .loading:before {
            content: '';
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(rgba(20, 20, 20,.8), rgba(0, 0, 0, .8));
            background: -webkit-radial-gradient(rgba(20, 20, 20,.8), rgba(0, 0, 0,.8));
            }
            /* :not(:required) hides these rules from IE9 and below */
            .loading:not(:required) {
            /* hide "loading..." text */
            font: 0/0 a;
            color: transparent;
            text-shadow: none;
            background-color: transparent;
            border: 0;
            }
            .loading:not(:required):after {
            content: '';
            display: block;
            font-size: 10px;
            width: 1em;
            height: 1em;
            margin-top: -0.5em;
            -webkit-animation: spinner 150ms infinite linear;
            -moz-animation: spinner 150ms infinite linear;
            -ms-animation: spinner 150ms infinite linear;
            -o-animation: spinner 150ms infinite linear;
            animation: spinner 150ms infinite linear;
            border-radius: 0.5em;
            -webkit-box-shadow: rgba(255,255,255, 0.75) 1.5em 0 0 0, rgba(255,255,255, 0.75) 1.1em 1.1em 0 0, rgba(255,255,255, 0.75) 0 1.5em 0 0, rgba(255,255,255, 0.75) -1.1em 1.1em 0 0, rgba(255,255,255, 0.75) -1.5em 0 0 0, rgba(255,255,255, 0.75) -1.1em -1.1em 0 0, rgba(255,255,255, 0.75) 0 -1.5em 0 0, rgba(255,255,255, 0.75) 1.1em -1.1em 0 0;
            box-shadow: rgba(255,255,255, 0.75) 1.5em 0 0 0, rgba(255,255,255, 0.75) 1.1em 1.1em 0 0, rgba(255,255,255, 0.75) 0 1.5em 0 0, rgba(255,255,255, 0.75) -1.1em 1.1em 0 0, rgba(255,255,255, 0.75) -1.5em 0 0 0, rgba(255,255,255, 0.75) -1.1em -1.1em 0 0, rgba(255,255,255, 0.75) 0 -1.5em 0 0, rgba(255,255,255, 0.75) 1.1em -1.1em 0 0;
            }
            /* Animation */
            @-webkit-keyframes spinner {
            0% {
            -webkit-transform: rotate(0deg);
            -moz-transform: rotate(0deg);
            -ms-transform: rotate(0deg);
            -o-transform: rotate(0deg);
            transform: rotate(0deg);
            }
            100% {
            -webkit-transform: rotate(360deg);
            -moz-transform: rotate(360deg);
            -ms-transform: rotate(360deg);
            -o-transform: rotate(360deg);
            transform: rotate(360deg);
            }
            }
            @-moz-keyframes spinner {
            0% {
            -webkit-transform: rotate(0deg);
            -moz-transform: rotate(0deg);
            -ms-transform: rotate(0deg);
            -o-transform: rotate(0deg);
            transform: rotate(0deg);
            }
            100% {
            -webkit-transform: rotate(360deg);
            -moz-transform: rotate(360deg);
            -ms-transform: rotate(360deg);
            -o-transform: rotate(360deg);
            transform: rotate(360deg);
            }
            }
            @-o-keyframes spinner {
            0% {
            -webkit-transform: rotate(0deg);
            -moz-transform: rotate(0deg);
            -ms-transform: rotate(0deg);
            -o-transform: rotate(0deg);
            transform: rotate(0deg);
            }
            100% {
            -webkit-transform: rotate(360deg);
            -moz-transform: rotate(360deg);
            -ms-transform: rotate(360deg);
            -o-transform: rotate(360deg);
            transform: rotate(360deg);
            }
            }
            @keyframes spinner {
            0% {
            -webkit-transform: rotate(0deg);
            -moz-transform: rotate(0deg);
            -ms-transform: rotate(0deg);
            -o-transform: rotate(0deg);
            transform: rotate(0deg);
            }
            100% {
            -webkit-transform: rotate(360deg);
            -moz-transform: rotate(360deg);
            -ms-transform: rotate(360deg);
            -o-transform: rotate(360deg);
            transform: rotate(360deg);
            }
            }
            .payimg{
            float: right;
            width:100%;
            }
            .jumbotron {
            padding: 2rem;
            }.jumpadding{
            padding: 0px 150px;
            margin-top: 50px;
            }.mr-t{
            margin-top:100px;
            }
            @media(max-width:769px){
            .mr-t{
            margin-top:20px;
            }.payimg {
            float: none;
            width:100%;
            }
            }
            .card{
            box-shadow: 0 4px 48px 0 rgb(0 0 0 / 60%), 0 6px 20px 0 rgb(0 0 0 / 80%);
            }
            body{
            background-size: cover;
            background-repeat: no-repeat;  
            background-image:url({{asset('assets/images/users/payb6.jpg')}});
            }
            .CardField-number{
            border: 1px solid black;  
            }
        </style>
    </head>
    <body>
        <div class="loading">Loading&#8230;</div>
        <div class="container">
            <div class="row mr-t">
                <div class="col-md-4"></div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 style="font-size:15px;">Subscription Payment</h4>
                                </div>
                                <div class="col-md-6">
                                    <img src="{{asset('assets/images/users/cards.png')}}" class="payimg"/>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="payment-form">
                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <input type="text" value="{{ $data['name'] }}" readonly  class="form-control"  id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label>Customer Email</label>
                                    <input type="email" value="{{ $data['email'] }}" readonly class="form-control"  id="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label>Payable Amount</label>
                                    <input type="text" value="&#8377; {{ $data['amount'] }}" readonly  class="form-control"  id="amount" name="amount" required>
                                </div>
                                <div class="form-group pt-2">
                                    <div id="card-element">
                                        <!-- Elements will create input elements here -->
                                    </div>
                                    <!-- We'll put the error messages in this element -->
                                </div>
                                <div class="form-group pt-2">
                                    <button id="submit" class="btn btn-block btn-success paynow mt-3">Pay Now</button>
                                </div>
                                <div id="card-errors" role="alert" style="color: red;"></div>
                                <div id="card-thank" role="alert" style="color: green;"></div>
                                <div id="card-message" role="alert" style="color: green;"></div>
                                <div id="card-success" role="alert" style="color: green;font-weight:bolder"></div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>
        <script type="text/javascript">
            // Set your publishable key: remember to change this to your live publishable key in production
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            $('#card-success').text('');
            $('#card-errors').text('');
            var stripe = Stripe('pk_test_51JWap1SCy44BcbLFmzwV4nAxI83Wywka4N46FV80eFOlXX5AaU4F9D69C1nWwwo3S0DTFOHId5yZkDWzrQ7oJ8H000hsR2uLIW');
            var elements = stripe.elements();
            $('#submit').prop('disabled', true);
            // Set up Stripe.js and Elements to use in checkout form
            var style = {
              base: {
                color: "#32325d",
              }
            };
            
            var card = elements.create("card", { hidePostalCode: true,style: style });
            card.mount("#card-element");
            
            
            card.addEventListener('change', ({error}) => {
              const displayError = document.getElementById('card-errors');
              if (error) {
                displayError.textContent = error.message;
                $('#submit').prop('disabled', true);
            
              } else {
                displayError.textContent = '';
                $('#submit').prop('disabled', false);
            
              }
            });
            
            var form = document.getElementById('payment-form');
            
            form.addEventListener('submit', function(ev) {
                $('.loading').css('display','block');            
                ev.preventDefault();
                
                $.ajax({
                    url: '{{ url("create-payment-intent")}}',
                    data: {sub_id: '{{$data["subsid"]}}', user_id: '{{$data["userid"]}}','_token': '{{ csrf_token() }}'},
                    method: 'post',
                    dataType: 'json',
                    success: function(response){
                        if(response.status==200){
                        //cardnumber,exp-date,cvc
                          stripe.confirmCardPayment(response.client_secret, {
                            payment_method: {
                              card: card,
                              billing_details: {
                                "address": {
                                    "city": '{{$user->city ? $user->city : @$ipdetails->city}}',
                                    "country": '{{@$ipdetails->country_code}}', 
                                    "postal_code": '{{$user->zipcode ? $user->zipcode : @$ipdetails->postal}}',
                                    "state": '{{$user->state ? $user->state : @$ipdetails->region}}'
                                    },
                                name: '{{ $data["name"] }}',
                                email: '{{ $data["email"] }}'
                              }
                            },
                            setup_future_usage: 'off_session'
                          }).then(function(result) {
                            $('.loading').css('display','none');
                            // return false;
                            if (result.error) {
                                 
                              // Show error to your customer (e.g., insufficient funds)
                              $('#card-errors').text(result.error.message);
                              setTimeout(function(){ window.location.href = "{{url('/success?pid=')}}"+result.error.payment_intent.id+"&userid="+'{{$data["userid"]}}'+"&subsis="+'{{$data["subsid"]}}'; }, 2000);
                            } else {
                              // The payment has been processed!
                              if (result.paymentIntent.status === 'succeeded') {

                                // $('#card-success').text("Payment successfully done");
                                $('.loading').show();
                                setTimeout(function(){ window.location.href = "{{url('/success?pid=')}}"+result.paymentIntent.id+"&userid="+'{{$data["userid"]}}'+"&subsis="+'{{$data["subsid"]}}'; }, 2000);
                              }
                              return false;
                            }
                          });
                      }
                    }
                })
            });
        </script>
    </body>
</html>