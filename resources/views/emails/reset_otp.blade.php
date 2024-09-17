
<!DOCTYPE html>
<html lang="en">

<head>
@include('emails.head')
</head>

<body style="
background: #f1f1f1;
font-family: 'Lexend', sans-serif;
">
    <div class="email-card"  style="
            width: 450px;
            height: 39rem;
            padding: 10px 20px;
            background: #fff;
            margin: 30px auto;
            ">


        <div class="section-one">
            <div>
                <img src="{{  $message->embed(public_path('assets/Logo.png')) }}">

                {{--  <img src="{{ asset('assets/Logo.png') }}">  --}}
            </div>
            <h2>
                Hi {{ $firstName }},<br />

                {{--  Hi Paul,<br />  --}}
            </h2>
        </div>



        <div class="section-two">

            <p>Enter this OTP code when prompted:</p>


            <p>If you have an questions, please email us at <a href="mailto:rm.io@amalitech.com">RM.io</a></p>

            <h1>{{ $OTP }}</h1>

            {{--  <h1>OTP</h1>  --}}

            <p>If you did not request this code, you can safely ignore this email. Someone else might have typed your
                email address by mistake.</p>




            <p>Thank you for using Resource Manager! RM.io</p>

            <div class="divider"
            style="
            width: 125px;
            height: 1px;
            background: rgba(0, 0, 0, 0.24);
            margin: 50px 10px 10px 0;
            "
            ></div>

            {{--  footer  --}}
           @include('emails.footer')
        </div>
    </div>
</body>

</html>
