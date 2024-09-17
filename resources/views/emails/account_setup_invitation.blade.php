<!DOCTYPE html>
<html lang="en">

<head>
    @include('emails.head')
</head>

<body style="
background: #f1f1f1;
font-family: 'Lexend', sans-serif;
">
    <div class="email-card"
        style="
            width: 440px;
            height: 39rem;
            padding: 10px 15px;
            background: #fff;
            margin: 20px auto;
            ">
        <div class="section-one">
            <div>

                <img src="{{  $message->embed(public_path('assets/Logo.png')) }}">

            </div>
            <h2>
                Hi {{ $receiver_email }},<br />

                {{--  Hi Paul,<br />  --}}
            </h2>
        </div>
        <div class="section-two">
            <p style="font-size:1.2rem">
                <a style="color: #000000;cursor: pointer;" href="mailto:{{ $sender_email }}">{{ $sender }},</a>
                has invited you to RM.io - a team
                scheduling tool.
                All you need to do is set up a password to get started.
            </p>
            <center>
                <a style="color: #fff;cursor: pointer;" href="{{ $actionUrl }}">
                    <button
                        style="
                cursor: pointer;
                appearance: button;
                background-color: #1F26A8;
                border: 1px solid #1F26A8;
                border-radius: 5px;
                box-sizing: border-box;
                color: #FFFFFF;
                cursor: pointer;
                padding: 9px 30px;
                border: none !important;
                margin: 20px 0 20px 0;"
                        role="button"> Get Started </button>
                </a>

            </center>


            <p style="font-size:1.2rem">If you have an questions, please email us at <a href="mailto:rm.io@amalitech.com">RM.io</a></p>


            <p style="font-size:1.2rem">Thank you for using Resource Manager! RM.io</p>


            <p style="font-size:1.2rem">

                If you are having trouble
                 clicking the get started button,
                  copy and paste the URL below into your web browser:<br>
                <a   href="{{ $actionUrl }}">{{ $actionUrl }}</a>
            </p>

            <div class="divider"
                style="
            width: 125px;
            height: 1px;
            background: rgba(0, 0, 0, 0.24);
            margin: 50px 10px 10px 0;
            font-family: "Lexendum"
                , "Helvetica Neue" , Helvetica, Arial; "
            ></div>


            {{--  footer  --}}
           @include('emails.footer')
        </div>
    </div>
</body>

</html>
