<footer>
    <div>

        <a href="#"><img src="{{ $message->embed(public_path('assets/tw.png')) }}" alt="Twitter"></a>
        <a href="#"><img src="{{ $message->embed(public_path('assets/fb.png')) }}" alt="Facebook"></a>
        <a href="#"><img src="{{ $message->embed(public_path('assets/in.png')) }}" alt="LinkedIn"></a>
        </div>
    <div class="logo-container">
        <img src="{{ $message->embed(public_path('assets/Logo.png')) }}">
    </div>
    <div>



        <p>
            Copyright © 2023-{{ Date('Y') }} RM.io Rewards & Recognition.
            <br />
            A better company begins with a personalized employee experience.
        </p>
    </div>
</footer>
