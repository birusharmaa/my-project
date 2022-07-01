<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Roboto, -apple-system, 'Helvetica Neue', 'Segoe UI', Arial, sans-serif;
            background: #3b4465;
        }

        .forms-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .section-title {
            font-size: 32px;
            letter-spacing: 1px;
            color: #fff;
        }

        .forms {
            display: flex;
            align-items: flex-start;
            margin-top: 30px;
        }

        .form-wrapper {
            animation: hideLayer .3s ease-out forwards;
        }

        .form-wrapper.is-active {
            animation: showLayer .3s ease-in forwards;
        }

        @keyframes showLayer {
            50% {
                z-index: 1;
            }

            100% {
                z-index: 1;
            }
        }

        @keyframes hideLayer {
            0% {
                z-index: 1;
            }

            49.999% {
                z-index: 1;
            }
        }

        .switcher {
            position: relative;
            cursor: pointer;
            display: block;
            margin-right: auto;
            margin-left: auto;
            padding: 0;
            text-transform: uppercase;
            font-family: inherit;
            font-size: 16px;
            letter-spacing: .5px;
            color: #999;
            background-color: transparent;
            border: none;
            outline: none;
            transform: translateX(0);
            transition: all .3s ease-out;
        }

        .form-wrapper.is-active .switcher-login {
            color: #fff;
            transform: translateX(90px);
        }

        .form-wrapper.is-active .switcher-signup {
            color: #fff;
            transform: translateX(-90px);
        }

        .underline {
            position: absolute;
            bottom: -5px;
            left: 0;
            overflow: hidden;
            pointer-events: none;
            width: 100%;
            height: 2px;
        }

        .underline::before {
            content: '';
            position: absolute;
            top: 0;
            left: inherit;
            display: block;
            width: inherit;
            height: inherit;
            background-color: currentColor;
            transition: transform .2s ease-out;
        }

        .switcher-login .underline::before {
            transform: translateX(101%);
        }

        .switcher-signup .underline::before {
            transform: translateX(-101%);
        }

        .form-wrapper.is-active .underline::before {
            transform: translateX(0);
        }

        .form {
            overflow: hidden;
            min-width: 260px;
            margin-top: 50px;
            padding: 30px 25px;
            border-radius: 5px;
            transform-origin: top;
        }

        .form-login {
            animation: hideLogin .3s ease-out forwards;
        }

        .form-wrapper.is-active .form-login {
            animation: showLogin .3s ease-in forwards;
        }

        @keyframes showLogin {
            0% {
                background: #d7e7f1;
                transform: translate(40%, 10px);
            }

            50% {
                transform: translate(0, 0);
            }

            100% {
                background-color: #fff;
                transform: translate(35%, -20px);
            }
        }

        @keyframes hideLogin {
            0% {
                background-color: #fff;
                transform: translate(35%, -20px);
            }

            50% {
                transform: translate(0, 0);
            }

            100% {
                background: #d7e7f1;
                transform: translate(40%, 10px);
            }
        }

        .form-signup {
            animation: hideSignup .3s ease-out forwards;
        }

        .form-wrapper.is-active .form-signup {
            animation: showSignup .3s ease-in forwards;
        }

        @keyframes showSignup {
            0% {
                background: #d7e7f1;
                transform: translate(-40%, 10px) scaleY(.8);
            }

            50% {
                transform: translate(0, 0) scaleY(.8);
            }

            100% {
                background-color: #fff;
                transform: translate(-35%, -20px) scaleY(1);
            }
        }

        @keyframes hideSignup {
            0% {
                background-color: #fff;
                transform: translate(-35%, -20px) scaleY(1);
            }

            50% {
                transform: translate(0, 0) scaleY(.8);
            }

            100% {
                background: #d7e7f1;
                transform: translate(-40%, 10px) scaleY(.8);
            }
        }

        .form fieldset {
            position: relative;
            opacity: 0;
            margin: 0;
            padding: 0;
            border: 0;
            transition: all .3s ease-out;
        }

        .form-login fieldset {
            transform: translateX(-50%);
        }

        .form-signup fieldset {
            transform: translateX(50%);
        }

        .form-wrapper.is-active fieldset {
            opacity: 1;
            transform: translateX(0);
            transition: opacity .4s ease-in, transform .35s ease-in;
        }

        .form legend {
            position: absolute;
            overflow: hidden;
            width: 1px;
            height: 1px;
            clip: rect(0 0 0 0);
        }

        .input-block {
            margin-bottom: 20px;
        }

        .input-block label {
            font-size: 14px;
            color: #a1b4b4;
        }

        .input-block input {
            display: block;
            width: 100%;
            margin-top: 8px;
            padding-right: 15px;
            padding-left: 15px;
            font-size: 16px;
            line-height: 40px;
            color: #3b4465;
            background: #eef9fe;
            border: 1px solid #cddbef;
            border-radius: 2px;
        }

        .form [type='submit'] {
            opacity: 0;
            display: block;
            min-width: 120px;
            margin: 30px auto 10px;
            font-size: 18px;
            line-height: 40px;
            border-radius: 25px;
            border: none;
            transition: all .3s ease-out;
        }

        .form-wrapper.is-active .form [type='submit'] {
            opacity: 1;
            transform: translateX(0);
            transition: all .4s ease-in;
        }

        .btn-login {
            color: #fbfdff;
            background: #a7e245;
            transform: translateX(-30%);
        }

        .btn-signup {
            color: #a7e245;
            background: #fbfdff;
            box-shadow: inset 0 0 0 2px #a7e245;
            transform: translateX(30%);
        }
    </style>
</head>

<body>
    <section class="forms-section">
        <h1 class="section-title">Login & Signup Forms</h1>
        <div class="forms">
            <div class="form-wrapper is-active">
                <button type="button" class="switcher switcher-login">
                    Login
                    <span class="underline"></span>
                </button>
                <form class="form form-login" action="loginUser" method="post">
                    @csrf
                    <fieldset>
                        <legend>Please, enter your email and password for login.</legend>
                        <div class="input-block">
                            <label for="login-email">E-mail</label>
                            <input id="login-email" type="email" name="email" value="{{ old('email') }}">
                            @error('email')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="input-block">
                            <label for="login-password">Password</label>
                            <input id="login-password" type="password" name="password">
                            @error('password')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </fieldset>
                    <button type="submit" class="btn-login">Login</button>
                </form>
            </div>
            <div class="form-wrapper">
                <button type="button" class="switcher switcher-signup">
                    Sign Up
                    <span class="underline"></span>
                </button>
                <form class="form form-signup" action="registerUser" method="post" return="false">
                    @if(Session::get('register_status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{Session::get('register_status')}}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    @endif
                    <fieldset>
                        <legend>Please, enter your email, password and password confirmation for sign up.</legend>
                        <div class="input-block">
                            <input id="signup-email" type="text" placeholder="Enter Name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            @csrf
                        </div>
                        <div class="input-block">
                            <input id="signup-email" placeholder="Enter email" type="email" required name="email" value="{{ old('email') }}">
                            @error('email')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="input-block">
                            <input id="signup-password" placeholder="Enter password" type="password" required name="password" value="{{ old('password') }}">
                            @error('password')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="input-block">
                            <input id="signup-password" placeholder="Enter confirm password" type="password" required name="confirm_password" value="{{ old('confirm_password') }}">
                            @error('confirm_password')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="input-block">
                            <input id="signup-password" type="text" maxlength="10" placeholder="Enter Mobile" required name="mobile" value="{{ old('mobile') }}" class="form-control">
                            @error('password')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </fieldset>
                    <button type="submit" class="btn-signup">Sign Up</button>
                </form>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
        const switchers = [...document.querySelectorAll('.switcher')]
        switchers.forEach(item => {
            item.addEventListener('click', function() {
                switchers.forEach(item => item.parentElement.classList.remove('is-active'))
                this.parentElement.classList.add('is-active')
            })
        })
    </script>
</body>

</html>