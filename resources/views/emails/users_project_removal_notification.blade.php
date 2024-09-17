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
            width: 450px;
            height: 100%;
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
               <p style="font-size:1.1rem">You have been removed from {{ $projectName }} project</p>

                {{--  Hi Paul,<br />  --}}
            </h2>
        </div>


        <div class="section-two">

            <p style="font-size:1.1rem">Below is the project details</p>
            <p style="font-size:0.8rem">Desc : <b>{{ $details }}</b> </p>
            <p style="font-size:0.8rem">Removed By : {{ $sender }}</p>

            <br>
            <p
            style="
color: #3D4A5C;
font-family: Inter;
font-size: 16px;
font-style: normal;
font-weight: 400;
line-height: 28px; /* 175% */
letter-spacing: -0.3px;
            font-family: Inter;
            font-size: 16px;
            font-style: normal;
            font-weight: 400;
            line-height: 28px; /* 175% */
            letter-spacing: -0.3px;
        ">
            Feel free to reach out if you have any questions or need further information. Welcome to the team, and
            let's make this project a success together!
        </p>


            <p style="font-size:1rem">Thank you for using Resource Manager! RM.io</p>

            <div class="divider"
                style="
            width: 125px;
            height: 1px;
            background: rgba(0, 0, 0, 0.24);
            margin: 50px 10px 10px 0;
            ">
            </div>

            {{--  footer  --}}
            @include('emails.footer')
        </div>
    </div>
</body>

</html>
