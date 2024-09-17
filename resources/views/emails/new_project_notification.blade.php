<!DOCTYPE html>
<html lang="en">

<head>
    @include('emails.head')
</head>

<body style="
background: #f1f1f1;
font-family: 'Lexend', sans-serif;
font-family: 'Poppins', sans-serif;
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
                <img src="{{ $message->embed(public_path('assets/Logo.png')) }}" data-default="placeholder">

                {{--  <img src="{{ public_path('assets/Logo.png') }}">  --}}
            </div>
            <h2>
                Hi {{ $firstName }},<br />

                {{--  Hi Paul,<br>  --}}
                You have been assigned to {{ $projectName }} project
            </h2>
        </div>

        <div class="section-two" style="color: #1C222B;
        ">

            <p style="font-size:1.1rem">Here are few details</p>

            <ul style="font-style: bold">
                <li>
                    <b>
                        <p style="font-size:0.8rem">Desc : <b>{{ $details }}</b> </p>
                    </b>
                </li>
                <li>
                    <b>
                        <p style="font-size:0.8rem">Start Date : <b>{{ $startDate }}</b></p>
                    </b>
                </li>
                <li>
                    <b>
                        <p style="font-size:0.8rem">End Date : <b>{{ $endDate }}</b> </p>
                    </b>
                </li>

                <li>
                    <b>
                        <p style="font-size:0.8rem">Assigned By : {{ $sender }}</p>
                    </b>
                </li>
            </ul>
            <br>
            <p
                style="
color: #3D4A5C;
/* Paragraph: 4/Regular */
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
            <center>
                <a style="color: #fff;cursor: pointer;" href="{{ $actionUrl }}">
                    <button
                        style="
                               width: 448px;
                               padding: 10px 24px;
                               justify-content: center;
                               align-items: center;
                               text-align: center;
                               gap: 12px;
                               border-radius: 16px;
                               border: none;
                               background: #1F26A8;"
                        role="button">
                        <span
                            style="color: #FFF;
                                 font-family: Lexend;
                                 font-size: 16px;
                                 font-style: normal;
                                 font-weight: 500;
                                 text-align: center;
                                 line-height: 24px; /* 150% */">
                            View Project
                        </span>
                    </button>
                </a>
            </center>


            <br>
            <div class="divider"
                style="
            width: 125px;
            height: 1px;
            background: rgba(0, 0, 0, 0.24);
            margin: 50px 10px 10px 0;
            ">
            </div>
            <p style="font-size:1rem">If you have an questions, please email us at <a
                    href="mailto:rm.io@amalitech.com">RM.io</a></p>

            footer
            @include('emails.footer')
        </div>
    </div>
</body>

</html>
