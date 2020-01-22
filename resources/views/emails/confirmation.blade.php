<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Verify Your Email Address</h2>

        <div>Hi {{$name}},
            <p>Thanks for creating an account g-Order.
            Please follow the link below to verify your email address
            {{route('confirmation',$api_token)}}.</p><br/>

        </div>

    </body>
</html>
