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

                <img src="{{ $message->embed(public_path('assets/Logo.png')) }}">

            </div>
            <h2>
                Hi {{ $name }},<br />
            </h2>
        </div>
        <div class="section-two">
            <p style="font-size:1.2rem">
                {{$content}}
            </p>
           


            <p style="font-size:1.2rem">If you have an questions, please email us at <a
                    href="mailto:rm.io@amalitech.com">RM.io</a></p>


            <p style="font-size:1.2rem">Thank you for using Resource Manager! RM.io</p>


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
