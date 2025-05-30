<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth Forms</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: space-around;
            padding: 20px;
        }
        .form-container {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .response {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Register</h2>
        <form id="registerForm">
            <div class="form-group">
                <label for="registerName">Name:</label>
                <input type="text" id="registerName" name="name" required>
            </div>
            <div class="form-group">
                <label for="registerEmail">Email:</label>
                <input type="email" id="registerEmail" name="email" required>
            </div>
            <div class="form-group">
                <label for="registerPassword">Password:</label>
                <input type="password" id="registerPassword" name="password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <div id="registerResponse" class="response"></div>
    </div>

    <div class="form-container">
        <h2>Login</h2>
        <form id="loginForm">
            <div class="form-group">
                <label for="loginEmail">Email:</label>
                <input type="email" id="loginEmail" name="email" required>
            </div>
            <div class="form-group">
                <label for="loginPassword">Password:</label>
                <input type="password" id="loginPassword" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div id="loginResponse" class="response"></div>
    </div>
    
    <div class="form-container">
        <h2>Authenticated User</h2>
        <button id="getUserButton">Get Authenticated User</button>
        <div id="userResponse" class="response"></div>

    <script>
        const registerForm = document.getElementById('registerForm');
        const registerResponseDiv = document.getElementById('registerResponse');
        const loginForm = document.getElementById('loginForm');
        const loginResponseDiv = document.getElementById('loginResponse');

        const getUserButton = document.getElementById('getUserButton');
        const userResponseDiv = document.getElementById('userResponse');
        registerForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('/api/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();
                registerResponseDiv.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                registerResponseDiv.textContent = 'Error: ' + error.message;
            }
        });

        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();
                localStorage.setItem('access_token', result.access_token);
                loginResponseDiv.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                loginResponseDiv.textContent = 'Error: ' + error.message;
            }
        });

        getUserButton.addEventListener('click', async () => {
            const token = localStorage.getItem('access_token'); // Assuming you store the token in localStorage after login

            if (!token) {
                userResponseDiv.textContent = 'Error: No token available. Please log in first.';
                return;
            }

            try {
                const response = await fetch('/api/auth/me', {
                    method: 'POST', // Assuming your /auth/me is a POST based on your route definition
                    headers: {
                        'Authorization': `Bearer ${token}`,
                    },
                });

                const result = await response.json();
                userResponseDiv.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                userResponseDiv.textContent = 'Error: ' + error.message;
            }
        });
    </script>

</body>
</html>