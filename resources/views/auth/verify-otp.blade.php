<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - GetBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .otp-container {
            max-width: 450px;
            margin: 0 auto;
            padding: 40px 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
            text-align: center;
        }

        .otp-header h2 {
            color: #3d5af1;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .otp-header p {
            color: #6c757d;
            margin-bottom: 30px;
        }

        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .otp-inputs input {
            width: 50px;
            height: 55px;
            text-align: center;
            font-size: 24px;
            font-weight: 500;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }

        .otp-inputs input:focus {
            border-color: #3d5af1;
            box-shadow: 0 0 0 0.25rem rgba(61, 90, 241, 0.25);
            outline: none;
        }

        .btn-verify {
            background-color: #3d5af1;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 30px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-verify:hover {
            background-color: #2a3eb1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(61, 90, 241, 0.3);
        }

        .resend-link {
            margin-top: 25px;
        }

        .timer {
            font-weight: 500;
            color: #3d5af1;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="otp-container">
            <div class="otp-header">
                <h2>Verify Your Login</h2>
                <p>We've sent a verification code to your mobile number ending with {{ $mobile_last_digits }}</p>
            </div>

            <form id="otpForm" method="POST" action="{{ route('verify.otp') }}">
                @csrf
                <div class="otp-inputs">
                    <input type="text" maxlength="1" class="form-control" name="otp[]" autofocus>
                    <input type="text" maxlength="1" class="form-control" name="otp[]">
                    <input type="text" maxlength="1" class="form-control" name="otp[]">
                    <input type="text" maxlength="1" class="form-control" name="otp[]">
                    <input type="text" maxlength="1" class="form-control" name="otp[]">
                    <input type="text" maxlength="1" class="form-control" name="otp[]">
                </div>

                <div class="timer">
                    <span id="countdown">01:30</span> remaining
                </div>

                <button type="submit" class="btn btn-verify">Verify & Login</button>

                <div class="resend-link">
                    <p>Didn't receive code? <a href="#" id="resendBtn" class="text-decoration-none">Resend OTP</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // OTP input handling
            const inputs = document.querySelectorAll('input[name="otp[]"]');

            inputs.forEach((input, index) => {
                input.addEventListener('keyup', function(e) {
                    if (e.key >= 0 && e.key <= 9) {
                        // Move to next input
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    } else if (e.key === 'Backspace') {
                        // Clear current field
                        input.value = '';

                        // Move to previous input if current is empty
                        if (index > 0 && input.value === '') {
                            inputs[index - 1].focus();
                        }
                    }
                });
            });

            // Countdown timer
            let timeLeft = 90; // 1:30 in seconds
            const countdownEl = document.getElementById('countdown');
            const resendBtn = document.getElementById('resendBtn');

            resendBtn.style.pointerEvents = 'none';
            resendBtn.style.opacity = '0.5';

            const countdown = setInterval(function() {
                const minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                seconds = seconds < 10 ? '0' + seconds : seconds;

                countdownEl.innerHTML = `${minutes}:${seconds}`;
                timeLeft--;

                if (timeLeft < 0) {
                    clearInterval(countdown);
                    countdownEl.innerHTML = "0:00";
                    resendBtn.style.pointerEvents = 'auto';
                    resendBtn.style.opacity = '1';
                }
            }, 1000);

            // Form submission
            const form = document.getElementById('otpForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validate all inputs are filled
                let isValid = true;
                let otpValue = '';

                inputs.forEach(input => {
                    if (input.value.trim() === '') {
                        isValid = false;
                    }
                    otpValue += input.value;
                });

                if (isValid) {
                    // Add hidden field with combined OTP
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'otp';
                    hiddenInput.value = otpValue;
                    form.appendChild(hiddenInput);

                    // Submit the form
                    form.submit();
                } else {
                    alert('Please enter the complete OTP');
                }
            });

            // Resend OTP functionality
            resendBtn.addEventListener('click', function(e) {
                e.preventDefault();

                if (timeLeft <= 0) {
                    fetch('{{ route("resend.otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reset timer
                            timeLeft = 90;
                            resendBtn.style.pointerEvents = 'none';
                            resendBtn.style.opacity = '0.5';

                            // Restart countdown
                            clearInterval(countdown);
                            countdown = setInterval(function() {
                                const minutes = Math.floor(timeLeft / 60);
                                let seconds = timeLeft % 60;
                                seconds = seconds < 10 ? '0' + seconds : seconds;

                                countdownEl.innerHTML = `${minutes}:${seconds}`;
                                timeLeft--;

                                if (timeLeft < 0) {
                                    clearInterval(countdown);
                                    countdownEl.innerHTML = "0:00";
                                    resendBtn.style.pointerEvents = 'auto';
                                    resendBtn.style.opacity = '1';
                                }
                            }, 1000);

                            // Clear inputs
                            inputs.forEach(input => {
                                input.value = '';
                            });
                            inputs[0].focus();

                            alert('OTP has been resent to your mobile number');
                        } else {
                            alert(data.message || 'Could not resend OTP. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Something went wrong! Please try again later.');
                    });
                }
            });
        });
    </script>
</body>

</html>
