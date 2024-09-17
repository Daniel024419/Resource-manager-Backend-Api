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

                {{--  Hi Paul,<br />  --}}

                Hi {{ $firstName }},<br />

                {{--  Deadline for the {{$projectName}} project is approaching {{$duration}}  --}}
               </p>


            </h2>
        </div>


        <div class="section-two">

            {{--  <p style="font-size:1.1rem">Projet : {{$projectName}}t</p>
            <p style="font-size:1.1rem">Start Date : {{$startDate}}</p>
            <p style="font-size:1.1rem">End Date : {{$endDate}}</p>  --}}

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
        If you encounter any challenges or foresee potential delays, please don't hesitate to communicate with the team as soon as possible. Collaborative problem-solving is crucial to overcoming obstacles and maintaining the project's momentum.
        </p>

        <center>
            <button style="display: flex;
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
                <span style="color: #FFF;
                             font-family: Lexend;
                             font-size: 16px;
                             font-style: normal;
                             font-weight: 500;
                             text-align: center;
                             line-height: 24px; /* 150% */">
                    View Project
                </span>
            </button>
        </center>
        <br>



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
